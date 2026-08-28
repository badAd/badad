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
	StripeSecret, StripePub                string
	PaypalID, PaypalSecret                 string
	PaypalSandbox                          bool
	WalletDir                              string
	AdWeekCents                            int
	ContactNotify                          string
	Coins                                  map[string]Coin
}

type Coin struct {
	Ticker, Address, KeyFile string
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
	c := &Config{Bind: "127.0.0.1", Port: "9003", MailTransport: "off", Path: path, AdWeekCents: 100, Coins: map[string]Coin{}}
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
		case "stripe_secret":
			c.StripeSecret = v
		case "stripe_publishable":
			c.StripePub = v
		case "paypal_client_id":
			c.PaypalID = v
		case "paypal_secret":
			c.PaypalSecret = v
		case "paypal_sandbox":
			c.PaypalSandbox = v == "1" || strings.EqualFold(v, "true")
		case "wallet_dir":
			c.WalletDir = v
		case "ad_week_cents":
			fmtSscanf := 0
			for _, ch := range v {
				if ch >= '0' && ch <= '9' {
					fmtSscanf = fmtSscanf*10 + int(ch-'0')
				}
			}
			if fmtSscanf > 0 {
				c.AdWeekCents = fmtSscanf
			}
		case "contact_notify":
			c.ContactNotify = v
		default:
			if strings.HasSuffix(k, "_key") {
				t := strings.TrimSuffix(k, "_key")
				coin := c.Coins[t]
				coin.Ticker = t
				coin.KeyFile = v
				c.Coins[t] = coin
			} else if isTicker(k) {
				coin := c.Coins[k]
				coin.Ticker = k
				coin.Address = v
				c.Coins[k] = coin
			}
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

func isTicker(k string) bool {
	known := "btc eth sol xrp xlm avax hbar usdt usdc dai shib lunc volt pepe doge ada ltc bch"
	return strings.Contains(" "+known+" ", " "+strings.ToLower(k)+" ")
}
