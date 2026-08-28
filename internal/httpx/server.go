package httpx

import (
	"context"
	"crypto/rand"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"html"
	"html/template"
	"log"
	"net/http"
	"net/url"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"

	"github.com/badAd/badad/internal/config"
	"github.com/jackc/pgx/v5/pgxpool"
	"golang.org/x/crypto/bcrypt"
)

type Server struct {
	cfg  *config.Config
	root string
	pool *pgxpool.Pool
	tmpl *template.Template
	mux  *http.ServeMux
}

func Listen(cfg *config.Config, root string) error {
	s := &Server{cfg: cfg, root: root, mux: http.NewServeMux()}
	s.tmpl, _ = template.New("x").Funcs(template.FuncMap{"h": html.EscapeString}).ParseGlob(filepath.Join(root, "web/templates/*.html"))
	s.routes()
	if cfg.DBName != "" {
		p, err := pgxpool.New(context.Background(), cfg.DSN())
		if err == nil {
			s.pool = p
			if b, err := os.ReadFile(filepath.Join(root, "sql/schema.sql")); err == nil {
				for _, st := range strings.Split(string(b), ";") {
					st = strings.TrimSpace(st)
					if st != "" {
						_, _ = p.Exec(context.Background(), st)
					}
				}
			}
		} else {
			log.Printf("db: %v", err)
		}
	}
	return http.ListenAndServe(cfg.Addr(), s.mux)
}

func (s *Server) routes() {
	s.mux.Handle("/static/", http.StripPrefix("/static/", http.FileServer(http.Dir(filepath.Join(s.root, "web/static")))))
	s.mux.HandleFunc("/", s.home)
	s.mux.HandleFunc("/login", s.login)
	s.mux.HandleFunc("/logout", s.logout)
	s.mux.HandleFunc("/register", s.register)
	s.mux.HandleFunc("/auth/", s.oauth)
	s.mux.HandleFunc("/login/pdt", s.loginPdt)
	s.mux.HandleFunc("/dash", s.dash)
	s.mux.HandleFunc("/dash/ad", s.editAd)
	s.mux.HandleFunc("/dash/site", s.editSite)
	s.mux.HandleFunc("/dash/keys", s.keys)
	s.mux.HandleFunc("/dash/security", s.security)
	s.mux.HandleFunc("/dash/pay", s.dashPay)
	s.mux.HandleFunc("/pay/return", s.payReturn)
	s.mux.HandleFunc("/pay/stripe/webhook", s.payStripeWH)
	s.mux.HandleFunc("/pay/paypal/webhook", s.payPaypalWH)
	s.mux.HandleFunc("/pay/crypto", s.payCryptoNote)
	s.mux.HandleFunc("/contact", s.contact)
	s.mux.HandleFunc("/embed.js", s.embedJS)
	s.mux.HandleFunc("/embed.json", s.embedJSON)
	s.mux.HandleFunc("/api/pdt/keys", s.apiPdtKeys)
	s.mux.HandleFunc("/a/", s.viewAd)
}

func tok(n int) string {
	b := make([]byte, n)
	rand.Read(b)
	return hex.EncodeToString(b)
}

func (s *Server) render(w http.ResponseWriter, name string, data any) {
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	if s.tmpl == nil {
		fmt.Fprint(w, "no templates")
		return
	}
	_ = s.tmpl.ExecuteTemplate(w, name, data)
}

type user struct {
	ID                         int64
	LoginID, Email, Name, Role string
	TOTPOn                     bool
	TOTP                       *string
}

func (s *Server) user(r *http.Request) *user {
	c, err := r.Cookie("badad")
	if err != nil || s.pool == nil {
		return nil
	}
	var u user
	err = s.pool.QueryRow(context.Background(),
		`SELECT u.id,u.login_id,u.email,u.name,u.role,u.totp_enabled,u.totp_secret
		 FROM sessions s JOIN users u ON u.id=s.user_id WHERE s.token=$1 AND s.expires>now()`, c.Value).
		Scan(&u.ID, &u.LoginID, &u.Email, &u.Name, &u.Role, &u.TOTPOn, &u.TOTP)
	if err != nil {
		return nil
	}
	return &u
}

func (s *Server) setSess(w http.ResponseWriter, uid int64) {
	t := tok(24)
	_, _ = s.pool.Exec(context.Background(), `INSERT INTO sessions(token,user_id,expires) VALUES($1,$2,now()+interval '14 days')`, t, uid)
	http.SetCookie(w, &http.Cookie{Name: "badad", Value: t, Path: "/", HttpOnly: true, MaxAge: 14 * 86400, SameSite: http.SameSiteLaxMode})
}

func (s *Server) page(r *http.Request, title string) map[string]any {
	return map[string]any{"Title": title, "User": s.user(r), "Pdt": s.cfg.PdtURL != ""}
}

func (s *Server) home(w http.ResponseWriter, r *http.Request) {
	if r.URL.Path != "/" {
		http.NotFound(w, r)
		return
	}
	p := s.page(r, "classified ads")
	var ads []map[string]any
	if s.pool != nil {
		rows, _ := s.pool.Query(context.Background(),
			`SELECT id,heading,body,contact FROM ads WHERE status='live' AND (expires_at IS NULL OR expires_at>now()) ORDER BY id DESC LIMIT 40`)
		for rows.Next() {
			var id int64
			var h, b, c string
			_ = rows.Scan(&id, &h, &b, &c)
			ads = append(ads, map[string]any{"ID": id, "Heading": h, "Body": b, "Contact": c})
		}
		rows.Close()
	}
	p["Ads"] = ads
	s.render(w, "home.html", p)
}

func (s *Server) login(w http.ResponseWriter, r *http.Request) {
	p := s.page(r, "Sign in")
	if r.Method == http.MethodPost {
		_ = r.ParseForm()
		var id int64
		var hash *string
		var totpOn bool
		err := s.pool.QueryRow(context.Background(),
			`SELECT id,pass_hash,totp_enabled FROM users WHERE login_id=$1`, r.FormValue("login_id")).Scan(&id, &hash, &totpOn)
		if err != nil || hash == nil || bcrypt.CompareHashAndPassword([]byte(*hash), []byte(r.FormValue("password"))) != nil {
			p["Err"] = "Login ID or password did not match."
			s.render(w, "login.html", p)
			return
		}
		if totpOn && r.FormValue("totp") == "" {
			p["Err"] = "Authenticator code required."
			s.render(w, "login.html", p)
			return
		}
		s.setSess(w, id)
		http.Redirect(w, r, "/dash", http.StatusSeeOther)
		return
	}
	s.render(w, "login.html", p)
}

func (s *Server) logout(w http.ResponseWriter, r *http.Request) {
	http.SetCookie(w, &http.Cookie{Name: "badad", Value: "", Path: "/", MaxAge: -1})
	http.Redirect(w, r, "/", http.StatusSeeOther)
}

func (s *Server) register(w http.ResponseWriter, r *http.Request) {
	p := s.page(r, "Register")
	if r.Method == http.MethodPost {
		_ = r.ParseForm()
		var pass *string
		if pw := r.FormValue("password"); pw != "" {
			h, _ := bcrypt.GenerateFromPassword([]byte(pw), 12)
			hs := string(h)
			pass = &hs
		}
		var id int64
		err := s.pool.QueryRow(context.Background(),
			`INSERT INTO users(login_id,email,pass_hash,name,role) VALUES($1,$2,$3,$4,'user') RETURNING id`,
			r.FormValue("login_id"), r.FormValue("email"), pass, r.FormValue("name")).Scan(&id)
		if err != nil {
			p["Err"] = err.Error()
			s.render(w, "register.html", p)
			return
		}
		s.setSess(w, id)
		http.Redirect(w, r, "/dash", http.StatusSeeOther)
		return
	}
	s.render(w, "register.html", p)
}

func (s *Server) need(w http.ResponseWriter, r *http.Request) *user {
	u := s.user(r)
	if u == nil {
		http.Redirect(w, r, "/login", http.StatusSeeOther)
	}
	return u
}

func (s *Server) dash(w http.ResponseWriter, r *http.Request) {
	u := s.need(w, r)
	if u == nil {
		return
	}
	p := s.page(r, "Dash")
	rows, _ := s.pool.Query(context.Background(), `SELECT id,heading,status FROM ads WHERE user_id=$1 ORDER BY id DESC`, u.ID)
	var ads []map[string]any
	for rows.Next() {
		var id int64
		var h, st string
		_ = rows.Scan(&id, &h, &st)
		ads = append(ads, map[string]any{"ID": id, "Heading": h, "Status": st})
	}
	rows.Close()
	p["Ads"] = ads
	s.render(w, "dash.html", p)
}

func (s *Server) editAd(w http.ResponseWriter, r *http.Request) {
	u := s.need(w, r)
	if u == nil {
		return
	}
	if r.Method == http.MethodPost {
		_ = r.ParseForm()
		weeks, _ := strconv.Atoi(r.FormValue("weeks"))
		if weeks < 1 {
			weeks = 1
		}
		via := r.FormValue("pay_via")
		if via == "" {
			via = "invoice"
		}
		renew := r.FormValue("renew") == "1" && via != "crypto"
		var adID int64
		err := s.pool.QueryRow(context.Background(),
			`INSERT INTO ads(user_id,heading,body,info,rate,contact,category,weeks,status,renew,pay_via)
			 VALUES($1,$2,$3,$4,$5,$6,$7,$8,'pending',$9,$10) RETURNING id`,
			u.ID, r.FormValue("heading"), r.FormValue("body"), r.FormValue("info"),
			r.FormValue("rate"), r.FormValue("contact"), r.FormValue("category"), weeks, renew, via).Scan(&adID)
		if err != nil {
			http.Error(w, err.Error(), 500)
			return
		}
		s.startAdPay(w, r, u.ID, adID, weeks, via, renew, u.Email)
		return
	}
	p := s.page(r, "New ad")
	p["WeekPrice"] = fmt.Sprintf("%.2f", float64(s.weekPrice())/100)
	s.render(w, "ad.html", p)
}

func (s *Server) editSite(w http.ResponseWriter, r *http.Request) {
	u := s.need(w, r)
	if u == nil {
		return
	}
	if r.Method == http.MethodPost {
		_ = r.ParseForm()
		serial := tok(16)
		slug := strings.ToLower(strings.Map(func(r rune) rune {
			if r >= 'a' && r <= 'z' || r >= '0' && r <= '9' {
				return r
			}
			return '-'
		}, r.FormValue("name")))
		_, _ = s.pool.Exec(context.Background(),
			`INSERT INTO sites(user_id,name,slug,serial,n_ads) VALUES($1,$2,$3,$4,$5)`,
			u.ID, r.FormValue("name"), slug+tok(2), serial, 3)
		http.Redirect(w, r, "/dash/site", http.StatusSeeOther)
		return
	}
	p := s.page(r, "Partner sites")
	rows, _ := s.pool.Query(context.Background(), `SELECT name,serial,n_ads FROM sites WHERE user_id=$1`, u.ID)
	var list []map[string]any
	for rows.Next() {
		var n, ser string
		var k int
		_ = rows.Scan(&n, &ser, &k)
		list = append(list, map[string]any{"Name": n, "Serial": ser, "N": k, "Snippet": `<script src="` + s.cfg.URL + `/embed.js?l=` + ser + `"></script>`})
	}
	rows.Close()
	p["Sites"] = list
	s.render(w, "site.html", p)
}

func (s *Server) keys(w http.ResponseWriter, r *http.Request) {
	u := s.need(w, r)
	if u == nil {
		return
	}
	if r.Method == http.MethodPost && r.FormValue("mint") == "1" {
		s.mintKeys(u.ID, false)
		http.Redirect(w, r, "/dash/keys", http.StatusSeeOther)
		return
	}
	p := s.page(r, "Developer keys")
	var pub, sec string
	var managed bool
	_ = s.pool.QueryRow(context.Background(),
		`SELECT coalesce(live_pub,''),coalesce(live_sec,''),pdt_managed FROM devkeys WHERE user_id=$1 ORDER BY id DESC LIMIT 1`, u.ID).
		Scan(&pub, &sec, &managed)
	p["Pub"], p["Sec"], p["Managed"] = pub, sec, managed
	s.render(w, "keys.html", p)
}

func (s *Server) mintKeys(uid int64, pdt bool) (string, string) {
	pub, sec := "pub_"+tok(24), "sec_"+tok(24)
	_, _ = s.pool.Exec(context.Background(),
		`INSERT INTO devkeys(user_id,status,live_pub,live_sec,test_pub,test_sec,pdt_managed)
		 VALUES($1,'live',$2,$3,$2,$3,$4)`, uid, pub, sec, pdt)
	return pub, sec
}

func (s *Server) security(w http.ResponseWriter, r *http.Request) {
	u := s.need(w, r)
	if u == nil {
		return
	}
	p := s.page(r, "Security")
	s.render(w, "security.html", p)
}

func (s *Server) viewAd(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(strings.TrimPrefix(r.URL.Path, "/a/"), 10, 64)
	var h, b, info, rate, c string
	err := s.pool.QueryRow(context.Background(),
		`SELECT heading,body,info,rate,contact FROM ads WHERE id=$1 AND status='live'`, id).Scan(&h, &b, &info, &rate, &c)
	if err != nil {
		http.NotFound(w, r)
		return
	}
	_, _ = s.pool.Exec(context.Background(), `INSERT INTO hits(ad_id,kind) VALUES($1,'click')`, id)
	p := s.page(r, h)
	p["Ad"] = map[string]any{"Heading": h, "Body": b, "Info": info, "Rate": rate, "Contact": c}
	s.render(w, "view.html", p)
}

func (s *Server) embedJSON(w http.ResponseWriter, r *http.Request) {
	n, _ := strconv.Atoi(r.URL.Query().Get("n"))
	if n < 1 {
		n = 3
	}
	rows, err := s.pool.Query(context.Background(),
		`SELECT id,heading,body FROM ads WHERE status='live' ORDER BY random() LIMIT $1`, n)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	defer rows.Close()
	var ads []map[string]any
	for rows.Next() {
		var id int64
		var h, b string
		_ = rows.Scan(&id, &h, &b)
		ads = append(ads, map[string]any{"id": id, "heading": h, "body": b, "url": s.cfg.URL + "/a/" + strconv.FormatInt(id, 10)})
	}
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")
	_ = json.NewEncoder(w).Encode(ads)
}

func (s *Server) embedJS(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/javascript")
	w.Header().Set("Access-Control-Allow-Origin", "*")
	n := 3
	fmt.Fprintf(w, `(function(){
var s=document.currentScript;
var n=%d;
var origin=%q;
fetch(origin+"/embed.json?n="+n).then(function(r){return r.json()}).then(function(ads){
  var box=document.createElement("div");
  box.className="badad-slot";
  box.setAttribute("style","font:inherit;color:inherit;background:transparent");
  (ads||[]).forEach(function(ad){
    var a=document.createElement("article");
    a.setAttribute("style","border-top:1px solid currentColor;padding:.6em 0;font:inherit;color:inherit");
    a.innerHTML="<strong><a href=\""+ad.url+"\" style=\"color:inherit\">"+ad.heading+"</a></strong><p style=\"margin:.3em 0 0\">"+ad.body+"</p>";
    box.appendChild(a);
  });
  s.parentNode.insertBefore(box,s);
}).catch(function(){});
})();`, n, s.cfg.URL)
}

func (s *Server) apiPdtKeys(w http.ResponseWriter, r *http.Request) {
	var m map[string]string
	_ = json.NewDecoder(r.Body).Decode(&m)
	if m["secret"] != s.cfg.PdtSecret || s.cfg.PdtSecret == "" {
		http.Error(w, "no", 401)
		return
	}
	var uid int64
	_ = s.pool.QueryRow(context.Background(), `SELECT id FROM users WHERE pdt_user=$1`, m["pdt_user"]).Scan(&uid)
	if uid == 0 {
		_ = s.pool.QueryRow(context.Background(),
			`INSERT INTO users(login_id,email,name,role,pdt_user) VALUES($1,$2,$3,'user',$4) RETURNING id`,
			"pdt"+m["pdt_user"], "pdt-"+m["pdt_user"]+"@linked.local", "pdt author", m["pdt_user"]).Scan(&uid)
	}
	_, _ = s.pool.Exec(context.Background(),
		`INSERT INTO devkeys(user_id,status,live_pub,live_sec,pdt_managed) VALUES($1,'live',$2,$3,true)`,
		uid, m["pub"], m["sec"])
	w.Header().Set("Content-Type", "application/json")
	fmt.Fprint(w, `{"ok":true}`)
}

func (s *Server) oauth(w http.ResponseWriter, r *http.Request) {
	p := strings.TrimPrefix(r.URL.Path, "/auth/")
	p = strings.Split(p, "/")[0]
	if r.URL.Query().Get("code") != "" {
		s.oauthFinish(w, r, p)
		return
	}
	st := tok(12)
	http.SetCookie(w, &http.Cookie{Name: "ba_oauth", Value: st, Path: "/", HttpOnly: true, MaxAge: 600})
	cb := s.cfg.URL + "/auth/" + p + "/callback"
	var dest string
	switch p {
	case "google":
		dest = "https://accounts.google.com/o/oauth2/v2/auth?" + url.Values{"client_id": {s.cfg.GoogleID}, "redirect_uri": {cb}, "response_type": {"code"}, "scope": {"openid email profile"}, "state": {st}}.Encode()
	case "github":
		dest = "https://github.com/login/oauth/authorize?" + url.Values{"client_id": {s.cfg.GithubID}, "redirect_uri": {cb}, "scope": {"user:email"}, "state": {st}}.Encode()
	case "apple":
		dest = "https://appleid.apple.com/auth/authorize?" + url.Values{"client_id": {s.cfg.AppleID}, "redirect_uri": {cb}, "response_type": {"code"}, "scope": {"name email"}, "state": {st}}.Encode()
	default:
		http.Error(w, "provider", 400)
		return
	}
	http.Redirect(w, r, dest, http.StatusFound)
}

func (s *Server) oauthFinish(w http.ResponseWriter, r *http.Request, p string) {
	c, _ := r.Cookie("ba_oauth")
	if c == nil || c.Value != r.URL.Query().Get("state") {
		http.Error(w, "state", 400)
		return
	}
	email := r.URL.Query().Get("email") // fallback; real token exchange omitted for github/google in this handler path via finishProfile
	prof := s.finishProfile(p, r.URL.Query().Get("code"))
	if prof == nil {
		http.Error(w, "oauth", 400)
		return
	}
	_ = email
	var uid int64
	_ = s.pool.QueryRow(context.Background(), `SELECT user_id FROM oauth_identities WHERE provider=$1 AND subject=$2`, p, prof["sub"]).Scan(&uid)
	if uid == 0 && prof["email"] != "" {
		_ = s.pool.QueryRow(context.Background(), `SELECT id FROM users WHERE email=$1`, prof["email"]).Scan(&uid)
	}
	if uid == 0 {
		_ = s.pool.QueryRow(context.Background(),
			`INSERT INTO users(login_id,email,name,role) VALUES($1,$2,$3,'user') RETURNING id`,
			"u"+tok(4), prof["email"], prof["name"]).Scan(&uid)
	}
	_, _ = s.pool.Exec(context.Background(),
		`INSERT INTO oauth_identities(user_id,provider,subject,email) VALUES($1,$2,$3,$4) ON CONFLICT DO NOTHING`,
		uid, p, prof["sub"], prof["email"])
	var totpOn bool
	_ = s.pool.QueryRow(context.Background(), `SELECT totp_enabled FROM users WHERE id=$1`, uid).Scan(&totpOn)
	if totpOn {
		http.Redirect(w, r, "/login?totp=1", http.StatusSeeOther)
		return
	}
	s.setSess(w, uid)
	http.Redirect(w, r, "/dash", http.StatusSeeOther)
}

func (s *Server) finishProfile(p, code string) map[string]string {
	cb := s.cfg.URL + "/auth/" + p + "/callback"
	form := url.Values{"code": {code}, "redirect_uri": {cb}, "grant_type": {"authorization_code"}}
	var tokenURL, cid, sec, infoURL string
	switch p {
	case "google":
		tokenURL, cid, sec, infoURL = "https://oauth2.googleapis.com/token", s.cfg.GoogleID, s.cfg.GoogleSecret, "https://openidconnect.googleapis.com/v1/userinfo"
		form.Set("client_id", cid)
		form.Set("client_secret", sec)
	case "github":
		tokenURL, cid, sec, infoURL = "https://github.com/login/oauth/access_token", s.cfg.GithubID, s.cfg.GithubSecret, "https://api.github.com/user"
		form.Set("client_id", cid)
		form.Set("client_secret", sec)
	default:
		return nil
	}
	req, _ := http.NewRequest("POST", tokenURL, strings.NewReader(form.Encode()))
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	req.Header.Set("Accept", "application/json")
	resp, err := http.DefaultClient.Do(req)
	if err != nil {
		return nil
	}
	defer resp.Body.Close()
	var tok map[string]any
	_ = json.NewDecoder(resp.Body).Decode(&tok)
	access, _ := tok["access_token"].(string)
	if access == "" {
		return nil
	}
	ir, _ := http.NewRequest("GET", infoURL, nil)
	ir.Header.Set("Authorization", "Bearer "+access)
	ir.Header.Set("User-Agent", "badAd")
	iresp, err := http.DefaultClient.Do(ir)
	if err != nil {
		return nil
	}
	defer iresp.Body.Close()
	var info map[string]any
	_ = json.NewDecoder(iresp.Body).Decode(&info)
	sub := fmt.Sprint(info["id"])
	if info["sub"] != nil {
		sub = fmt.Sprint(info["sub"])
	}
	email, _ := info["email"].(string)
	name, _ := info["name"].(string)
	return map[string]string{"sub": sub, "email": email, "name": name}
}

func (s *Server) loginPdt(w http.ResponseWriter, r *http.Request) {
	if s.cfg.PdtURL == "" {
		http.Error(w, "pdt oauth off", 400)
		return
	}
	if code := r.URL.Query().Get("code"); code != "" {
		s.finishPdt(w, r, code)
		return
	}
	st := tok(12)
	http.SetCookie(w, &http.Cookie{Name: "ba_pdt", Value: st, Path: "/", HttpOnly: true, MaxAge: 600})
	cb := s.cfg.URL + "/login/pdt"
	q := url.Values{"client_id": {s.cfg.PdtClientID}, "redirect_uri": {cb}, "response_type": {"code"}, "state": {st}}
	http.Redirect(w, r, s.cfg.PdtURL+"/oauth/authorize?"+q.Encode(), http.StatusFound)
}

func (s *Server) finishPdt(w http.ResponseWriter, r *http.Request, code string) {
	form := url.Values{
		"code": {code}, "client_id": {s.cfg.PdtClientID}, "client_secret": {s.cfg.PdtSecret},
		"grant_type": {"authorization_code"}, "redirect_uri": {s.cfg.URL + "/login/pdt"},
	}
	resp, err := http.Post(s.cfg.PdtURL+"/oauth/token", "application/x-www-form-urlencoded", strings.NewReader(form.Encode()))
	if err != nil {
		http.Error(w, err.Error(), 502)
		return
	}
	var tokenMap map[string]any
	_ = json.NewDecoder(resp.Body).Decode(&tokenMap)
	resp.Body.Close()
	access, _ := tokenMap["access_token"].(string)
	req, _ := http.NewRequest("GET", s.cfg.PdtURL+"/oauth/userinfo", nil)
	req.Header.Set("Authorization", "Bearer "+access)
	iresp, err := http.DefaultClient.Do(req)
	if err != nil {
		http.Error(w, err.Error(), 502)
		return
	}
	var info map[string]any
	_ = json.NewDecoder(iresp.Body).Decode(&info)
	iresp.Body.Close()
	pdtID := fmt.Sprint(info["id"])
	email, _ := info["email"].(string)
	name, _ := info["name"].(string)
	login, _ := info["login_id"].(string)
	var uid int64
	_ = s.pool.QueryRow(context.Background(), `SELECT id FROM users WHERE pdt_user=$1`, pdtID).Scan(&uid)
	if uid == 0 && email != "" {
		_ = s.pool.QueryRow(context.Background(), `SELECT id FROM users WHERE email=$1`, email).Scan(&uid)
	}
	if uid == 0 {
		if login == "" {
			login = "pdt" + tok(3)
		}
		_ = s.pool.QueryRow(context.Background(),
			`INSERT INTO users(login_id,email,name,role,pdt_user) VALUES($1,$2,$3,'user',$4) RETURNING id`,
			login, email, name, pdtID).Scan(&uid)
	} else {
		_, _ = s.pool.Exec(context.Background(), `UPDATE users SET pdt_user=$1 WHERE id=$2`, pdtID, uid)
	}
	pub, sec := s.mintKeys(uid, true)
	_ = pub
	_ = sec
	s.setSess(w, uid)
	http.Redirect(w, r, "/dash/keys", http.StatusSeeOther)
}

var _ = time.Now
