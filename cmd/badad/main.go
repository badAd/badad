package main

import (
	"log"
	"os"
	"path/filepath"

	"github.com/badAd/badad/internal/config"
	"github.com/badAd/badad/internal/httpx"
)

func main() {
	cfgPath := config.Find()
	cfg, err := config.Load(cfgPath)
	if err != nil {
		cfg = &config.Config{Bind: "127.0.0.1", Port: "9003"}
	}
	root := findRoot()
	log.Printf("badAd %s config=%s", cfg.Addr(), cfgPath)
	if err := httpx.Listen(cfg, root); err != nil {
		log.Fatal(err)
	}
}

func findRoot() string {
	cands := []string{"."}
	if exe, err := os.Executable(); err == nil {
		cands = append(cands, filepath.Dir(exe))
	}
	for _, c := range cands {
		if st, err := os.Stat(filepath.Join(c, "web", "templates")); err == nil && st.IsDir() {
			abs, _ := filepath.Abs(c)
			return abs
		}
	}
	abs, _ := filepath.Abs(".")
	return abs
}
