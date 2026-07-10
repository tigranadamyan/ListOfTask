.PHONY: up down restart logs build clean

# ─── Default target ──────────────────────────────────────────
.DEFAULT_GOAL := up

# ─── Copy .env if missing ────────────────────────────────────
.env:
	@cp .env.example .env
	@echo "✅ Created .env from .env.example"

# ─── Start the full stack ────────────────────────────────────
up: .env
	@echo "🔧 Building Docker images..."
	@docker compose build --quiet
	@echo "🚀 Starting containers..."
	@docker compose up -d
	@echo "⏳ Waiting for API to be ready..."
	@timeout=60; while [ $$timeout -gt 0 ]; do \
		if docker compose exec -T api php artisan about --only=Environment > /dev/null 2>&1; then \
			break; \
		fi; \
		sleep 2; \
		timeout=$$((timeout - 2)); \
	done; \
	if [ $$timeout -le 0 ]; then \
		echo "❌ API failed to start. Check logs: make logs"; \
		exit 1; \
	fi
	@echo "✅ Application is ready!"
	@echo ""
	@echo "   🌐 App:       http://localhost:8080"
	@echo "   📡 API:       http://localhost:8080/api"
	@echo "   📄 API Docs:  http://localhost:8080/api/docs"
	@echo "   🔧 API direct: http://localhost:8000"
	@echo ""

# ─── Stop the stack ──────────────────────────────────────────
down:
	@docker compose down

# ─── Restart containers ──────────────────────────────────────
restart:
	@docker compose restart

# ─── View logs ───────────────────────────────────────────────
logs:
	@docker compose logs -f

# ─── Rebuild images ──────────────────────────────────────────
build:
	@docker compose build --no-cache

# ─── Clean: remove containers, images, volumes ───────────────
clean:
	@docker compose down -v --rmi all
	@echo "🧹 Cleaned up all Docker resources"

# ─── Shell into a container ──────────────────────────────────
shell-api:
	@docker compose exec api sh

shell-frontend:
	@docker compose exec frontend sh
