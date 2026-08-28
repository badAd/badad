package httpx

import (
	"context"
	"net/http"
	"os/exec"
	"strings"
)

func (s *Server) contact(w http.ResponseWriter, r *http.Request) {
	p := s.page(r, "Contact")
	if r.Method == http.MethodPost {
		_ = r.ParseForm()
		name, email, msg := r.FormValue("name"), r.FormValue("email"), r.FormValue("message")
		if s.pool != nil {
			_, _ = s.pool.Exec(context.Background(), `INSERT INTO contacts(name,email,message) VALUES($1,$2,$3)`, name, email, msg)
		}
		if s.cfg.ContactNotify != "" && s.cfg.MailTransport != "off" {
			cmd := exec.Command("sendmail", "-t")
			body := "To: " + s.cfg.ContactNotify + "\nSubject: badAd contact\n\n" + name + " <" + email + ">\n\n" + msg
			cmd.Stdin = strings.NewReader(body)
			_ = cmd.Run()
		}
		p["Flash"] = "Sent. We will reply."
		s.render(w, "contact.html", p)
		return
	}
	s.render(w, "contact.html", p)
}
