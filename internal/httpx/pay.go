package httpx

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strconv"
	"strings"

	"github.com/badAd/badad/internal/pay"
)

func (s *Server) payCfg() pay.Cfg {
	c := pay.Cfg{
		StripeSecret: s.cfg.StripeSecret, StripePub: s.cfg.StripePub,
		PaypalID: s.cfg.PaypalID, PaypalSecret: s.cfg.PaypalSecret,
		PaypalSandbox: s.cfg.PaypalSandbox, Origin: s.cfg.URL,
	}
	for t, coin := range s.cfg.Coins {
		if coin.Address != "" {
			c.Coins = append(c.Coins, pay.Coin{Ticker: strings.ToUpper(t), Address: coin.Address})
		}
	}
	return c
}

func (s *Server) weekPrice() int {
	if s.cfg.AdWeekCents > 0 {
		return s.cfg.AdWeekCents
	}
	return 100
}

func (s *Server) startAdPay(w http.ResponseWriter, r *http.Request, uid, adID int64, weeks int, via string, renew bool, email string) {
	if weeks < 1 {
		weeks = 1
	}
	if via == "crypto" {
		renew = false
	}
	total := weeks * s.weekPrice()
	end := pay.PeriodEnd(weeks, "week")
	var oid int64
	_ = s.pool.QueryRow(context.Background(),
		`INSERT INTO orders(user_id,ad_id,email,total_cents,weeks,pay_via,status,renew,period_end)
		 VALUES($1,$2,$3,$4,$5,$6,'pending',$7,$8) RETURNING id`,
		uid, adID, email, total, weeks, via, renew, end).Scan(&oid)
	origin := strings.TrimRight(s.cfg.URL, "/")
	intent := pay.Intent{
		OrderID: strconv.FormatInt(oid, 10), AmountCents: total, Currency: "USD",
		Title: fmt.Sprintf("classified ad · %d week(s)", weeks), Email: email,
		IntervalN: 1, Interval: "week", Renew: renew,
		SuccessURL: origin + "/pay/return?order=" + strconv.FormatInt(oid, 10),
		CancelURL:  origin + "/dash",
	}
	switch via {
	case "stripe":
		sess, err := pay.StripeSession(s.payCfg(), intent)
		if err != nil {
			http.Error(w, err.Error(), 502)
			return
		}
		_, _ = s.pool.Exec(context.Background(), `UPDATE orders SET provider_ref=$1 WHERE id=$2`, sess.Ref, oid)
		http.Redirect(w, r, sess.Redirect, http.StatusSeeOther)
	case "paypal":
		sess, err := pay.PayPalSession(s.payCfg(), intent)
		if err != nil {
			http.Error(w, err.Error(), 502)
			return
		}
		_, _ = s.pool.Exec(context.Background(), `UPDATE orders SET provider_ref=$1 WHERE id=$2`, sess.Ref, oid)
		http.Redirect(w, r, sess.Redirect, http.StatusSeeOther)
	case "crypto":
		_, _ = s.pool.Exec(context.Background(), `INSERT INTO crypto_payments(order_id) VALUES($1)`, oid)
		p := s.page(r, "Pay with crypto")
		p["Order"], p["Total"], p["Coins"] = oid, fmt.Sprintf("%.2f", float64(total)/100), s.payCfg().Coins
		s.render(w, "pay-crypto.html", p)
	default:
		s.fulfill(oid)
		http.Redirect(w, r, "/pay/return?order="+strconv.FormatInt(oid, 10), http.StatusSeeOther)
	}
}

func (s *Server) payReturn(w http.ResponseWriter, r *http.Request) {
	oid, _ := strconv.ParseInt(r.URL.Query().Get("order"), 10, 64)
	if token := r.URL.Query().Get("token"); token != "" {
		_ = pay.CapturePayPal(s.payCfg(), token)
		var id int64
		_ = s.pool.QueryRow(context.Background(), `SELECT id FROM orders WHERE provider_ref=$1`, token).Scan(&id)
		if id > 0 {
			oid = id
		}
	}
	if oid > 0 {
		s.fulfill(oid)
	}
	p := s.page(r, "Receipt")
	p["Flash"] = "Paid. Stripe/PayPal subscriptions renew until you cancel with the processor. Crypto never renews."
	s.render(w, "receipt.html", p)
}

func (s *Server) payStripeWH(w http.ResponseWriter, r *http.Request) {
	b, _ := io.ReadAll(r.Body)
	var ev struct {
		Type string `json:"type"`
		Data struct {
			Object map[string]any `json:"object"`
		} `json:"data"`
	}
	_ = json.Unmarshal(b, &ev)
	if ev.Type == "checkout.session.completed" || ev.Type == "invoice.paid" {
		client := fmt.Sprint(ev.Data.Object["client_reference_id"])
		ref := fmt.Sprint(ev.Data.Object["id"])
		var oid int64
		if client != "" && client != "<nil>" {
			oid, _ = strconv.ParseInt(client, 10, 64)
		}
		if oid == 0 {
			_ = s.pool.QueryRow(context.Background(), `SELECT id FROM orders WHERE provider_ref=$1`, ref).Scan(&oid)
		}
		if oid > 0 {
			s.fulfill(oid)
		}
	}
	w.WriteHeader(200)
}

func (s *Server) payPaypalWH(w http.ResponseWriter, r *http.Request) {
	b, _ := io.ReadAll(r.Body)
	var ev map[string]any
	_ = json.Unmarshal(b, &ev)
	res, _ := ev["resource"].(map[string]any)
	id := fmt.Sprint(res["id"])
	custom := fmt.Sprint(res["custom_id"])
	var oid int64
	if custom != "" && custom != "<nil>" {
		oid, _ = strconv.ParseInt(custom, 10, 64)
	}
	if oid == 0 && id != "" && id != "<nil>" {
		_ = s.pool.QueryRow(context.Background(), `SELECT id FROM orders WHERE provider_ref=$1`, id).Scan(&oid)
	}
	if oid > 0 {
		s.fulfill(oid)
	}
	w.WriteHeader(200)
}

func (s *Server) payCryptoNote(w http.ResponseWriter, r *http.Request) {
	_ = r.ParseForm()
	oid, _ := strconv.ParseInt(r.FormValue("order"), 10, 64)
	_, _ = s.pool.Exec(context.Background(),
		`UPDATE crypto_payments SET status='awaiting', note=$1 WHERE order_id=$2`, r.FormValue("txid"), oid)
	p := s.page(r, "Crypto")
	p["Flash"] = "Marked as sent. Admin will confirm. Prepaid — will not renew."
	s.render(w, "receipt.html", p)
}

func (s *Server) dashPay(w http.ResponseWriter, r *http.Request) {
	u := s.need(w, r)
	if u == nil {
		return
	}
	if r.Method == http.MethodPost && u.Role == "admin" {
		_ = r.ParseForm()
		if id := r.FormValue("confirm"); id != "" {
			oid, _ := strconv.ParseInt(id, 10, 64)
			s.fulfill(oid)
			_, _ = s.pool.Exec(context.Background(), `UPDATE crypto_payments SET status='confirmed' WHERE order_id=$1`, oid)
		}
		http.Redirect(w, r, "/dash/pay", http.StatusSeeOther)
		return
	}
	p := s.page(r, "Payments")
	q := `SELECT id,email,total_cents,pay_via,status,renew,weeks FROM orders ORDER BY id DESC LIMIT 80`
	if u.Role != "admin" {
		q = `SELECT id,email,total_cents,pay_via,status,renew,weeks FROM orders WHERE user_id=$1 ORDER BY id DESC LIMIT 80`
	}
	var rows interface{ Close() }
	var list []map[string]any
	if u.Role == "admin" {
		rr, _ := s.pool.Query(context.Background(), q)
		defer rr.Close()
		for rr.Next() {
			var id int64
			var email, via, st string
			var total, weeks int
			var renew bool
			_ = rr.Scan(&id, &email, &total, &via, &st, &renew, &weeks)
			list = append(list, map[string]any{"ID": id, "Email": email, "Total": fmt.Sprintf("%.2f", float64(total)/100), "Via": via, "Status": st, "Renew": renew, "Weeks": weeks, "Admin": true})
		}
	} else {
		rr, _ := s.pool.Query(context.Background(), q, u.ID)
		defer rr.Close()
		for rr.Next() {
			var id int64
			var email, via, st string
			var total, weeks int
			var renew bool
			_ = rr.Scan(&id, &email, &total, &via, &st, &renew, &weeks)
			list = append(list, map[string]any{"ID": id, "Email": email, "Total": fmt.Sprintf("%.2f", float64(total)/100), "Via": via, "Status": st, "Renew": renew, "Weeks": weeks})
		}
	}
	_ = rows
	p["Orders"] = list
	p["Admin"] = u.Role == "admin"
	s.render(w, "dash-pay.html", p)
}

func (s *Server) fulfill(oid int64) {
	if s.pool == nil || oid < 1 {
		return
	}
	var status string
	var adID *int64
	var weeks int
	var renew bool
	var via string
	err := s.pool.QueryRow(context.Background(),
		`SELECT status,ad_id,weeks,renew,pay_via FROM orders WHERE id=$1`, oid).
		Scan(&status, &adID, &weeks, &renew, &via)
	if err != nil || status == "paid" {
		return
	}
	end := pay.PeriodEnd(weeks, "week")
	_, _ = s.pool.Exec(context.Background(), `UPDATE orders SET status='paid', period_end=$1 WHERE id=$2`, end, oid)
	if adID != nil {
		_, _ = s.pool.Exec(context.Background(),
			`UPDATE ads SET status='live', expires_at=$1, renew=$2, pay_via=$3 WHERE id=$4`,
			end, renew, via, *adID)
	}
}
