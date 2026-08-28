# badAd (Go)

Classified text ads. **PHP 2017 lives on the `phpinitial` branch.** This `golang` branch is the rewrite.

Login: password, Google, Apple, GitHub, **Login with pdt**. Authenticator still applies when enabled. Developer keys mint automatically when a pdt-news account is linked; otherwise they are shown in the dash for WordPress / custom apps.

Embed (`/embed.js?l=SERIAL`) inherits font and color from the host page so it plays with whatever theme is already there — including pdt-news reader modes.

```
go build -o badad ./cmd/badad
./badad
```

Config: `/etc/badad/config` or `config` next to the binary. PostgreSQL over TCP (`127.0.0.1`).
