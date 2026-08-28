package config

import (
	"os"
	"path/filepath"
	"strings"
)

type Config struct {
	Bind, Port, URL                        string
	DBHost, DBPort, DBName, DBUser, DBPass string
	GoogleID, GoogleSecret                 string
	AppleID, AppleSecret                   string
	GithubID, GithubSecret                 string
	PdtURL, PdtClientID, PdtSecret         string
	MailTransport, MailFrom                string
	Path                                   string
}

func Find() string {
	if p := os.Getenv("BADAD_CONFIG"); p != "" {
		return p
	}
	for _, c := range []string{"/etc/badad/config", "config", "config.sample"} {
		if st, err := os.Stat(c); err == nil && !st.IsDir() {
			return c
		}
	}
	if exe, err := os.Executable(); err == nil {
		p := filepath.Join(filepath.Dir(exe), "config")
		if st, err := os.Stat(p); err == nil && !st.IsDir() {
			return p
		}
	}
	return "config.sample"
}

func Load(path string) (*Config, error) {
	b, err := os.ReadFile(path)
	if err != nil {
		return nil, err
	}
	c := &Config{Bind: "127.0.0.1", Port: "9003", MailTransport: "off", Path: path}
	for _, line := range strings.Split(string(b), "\n") {
		line = strings.TrimSpace(line)
		if line == "" || strings.HasPrefix(line, "#") {
			continue
		}
		k, v, ok := strings.Cut(line, "=")
		if !ok {
			continue
		}
		k, v = strings.TrimSpace(k), strings.TrimSpace(v)
		switch k {
		case "web_bind":
			c.Bind = v
		case "web_port":
			c.Port = v
		case "web_url":
			c.URL = strings.TrimRight(v, "/")
		case "db_host":
			c.DBHost = v
		case "db_port":
			c.DBPort = v
		case "db_name":
			c.DBName = v
		case "db_user":
			c.DBUser = v
		case "db_pass":
			c.DBPass = v
		case "oauth_google_id":
			c.GoogleID = v
		case "oauth_google_secret":
			c.GoogleSecret = v
		case "oauth_apple_id":
			c.AppleID = v
		case "oauth_apple_secret":
			c.AppleSecret = v
		case "oauth_github_id":
			c.GithubID = v
		case "oauth_github_secret":
			c.GithubSecret = v
		case "pdt_url":
			c.PdtURL = strings.TrimRight(v, "/")
		case "pdt_client_id":
			c.PdtClientID = v
		case "pdt_client_secret":
			c.PdtSecret = v
		case "mail_transport":
			c.MailTransport = v
		case "mail_from":
			c.MailFrom = v
		}
	}
	return c, nil
}

func (c *Config) Addr() string {
	return c.Bind + ":" + c.Port
}

func (c *Config) DSN() string {
	return "postgres://" + c.DBUser + ":" + c.DBPass + "@" + c.DBHost + ":" + c.DBPort + "/" + c.DBName + "?sslmode=disable"
}
