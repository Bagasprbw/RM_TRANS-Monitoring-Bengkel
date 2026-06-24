# ============================================================
# Makefile - RM Trans Monitoring Bengkel
# Shortcut commands untuk Docker deployment
# ============================================================

.PHONY: help build up down restart logs ps pull deploy clean

## ---- Info ----
help: ## Tampilkan semua perintah
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

## ---- Setup Awal ----
setup: ## Setup pertama kali: salin .env dan build semua container
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "✅ File .env dibuat. Silahkan edit nilainya dulu lalu jalankan 'make up'."; \
	else \
		echo "⚠️  File .env sudah ada."; \
	fi

## ---- Docker Operations ----
build: ## Build semua image dari awal
	docker compose build --no-cache

up: ## Jalankan semua container (background)
	docker compose up -d

down: ## Matikan semua container
	docker compose down

restart: ## Restart semua container
	docker compose restart

ps: ## Lihat status container
	docker compose ps

logs: ## Lihat log semua container (Ctrl+C untuk keluar)
	docker compose logs -f

logs-backend: ## Lihat log backend saja
	docker compose logs -f backend

logs-db: ## Lihat log database saja
	docker compose logs -f db

## ---- Deployment (update dari GitHub) ----
deploy: ## Pull kode terbaru & rebuild frontend/backend
	git pull origin main
	docker compose build --no-cache backend frontend
	docker compose up -d --force-recreate backend nginx-backend frontend
	@echo "✅ Deploy selesai!"

## ---- Laravel Utilities ----
artisan: ## Jalankan artisan command. Contoh: make artisan CMD="migrate:fresh --seed"
	docker compose exec backend php artisan $(CMD)

migrate: ## Jalankan migrasi database
	docker compose exec backend php artisan migrate --force

seed: ## Jalankan database seeder
	docker compose exec backend php artisan db:seed

tinker: ## Buka Laravel Tinker
	docker compose exec backend php artisan tinker

## ---- Cleanup ----
clean: ## Hapus semua container, volume, dan image (HATI-HATI: data DB hilang!)
	docker compose down -v --rmi all
	@echo "⚠️  Semua container, volume, dan image telah dihapus."
