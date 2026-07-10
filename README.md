# Список задач — Fullstack-приложение

Production-ready приложение для управления задачами на **Laravel 13** (REST API) и **Nuxt 4** (SPA).

---

## Технологии

| Слой | Технология |
|---|---|
| Backend | Laravel 13, PHP 8.4, SQLite |
| Авторизация | Laravel Sanctum (API-токены) |
| Frontend | Nuxt 4, Vue 3.5, TypeScript |
| Управление состоянием | Pinia |
| Стили | Tailwind CSS 3 |
| Уведомления | vue-sonner |
| Контейнеризация | Docker Compose + Nginx |
| Тестирование | PHPUnit 12 (33 теста), Vitest (13 тестов) |
| API-документация | Scribe (OpenAPI) |

---

## Быстрый старт (Docker)

```bash
git clone https://github.com/tigranadamyan/ListOfTask.git
cd ListOfTask
cp .env.example .env
make up
```

Всё автоматически: сборка образов, миграции, сидинг демо-данных, запуск сервисов.

### Сервисы

| Сервис | URL | Описание |
|---|---|---|
| Приложение | http://localhost:8080 | Frontend (Nuxt SSR) через Nginx |
| API | http://localhost:8080/api | REST API (Laravel) через Nginx |
| API Docs | http://localhost:8080/docs | OpenAPI/Swagger документация (Scribe) |
| API (напрямую) | http://localhost:8000 | Laravel без прокси (для отладки) |

### Демо-аккаунты

| Роль | Email | Пароль |
|---|---|---|
| Администратор | `admin@example.com` | `password` |
| Обычный пользователь | `user@example.com` | `password` |

### Команды Make

```bash
make up          # Сборка + запуск всего стека
make down        # Остановка контейнеров
make restart     # Перезапуск контейнеров
make logs        # Просмотр логов (в реальном времени)
make build       # Полная пересборка образов (без кэша)
make clean       # Удаление контейнеров, образов и volume'ов
make shell-api   # Shell в контейнер API
make shell-frontend  # Shell в контейнер Frontend
```

---

## Ручная установка

### Требования

- PHP 8.3+
- Composer
- Node.js 22+
- npm 10+

### Backend

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

---

## Архитектура Docker

```
                    ┌─────────────┐
   :8080 ─────────►│   Nginx     │
                    │  (reverse   │
                    │   proxy)    │
                    └──┬──────┬──┘
                       │      │
            /api/* ────┘      └──── /* (остальное)
               │                    │
        ┌──────▼──────┐    ┌───────▼──────┐
        │   Laravel   │    │    Nuxt 4    │
        │   (PHP-FPM) │    │  (Node SSR)  │
        │   :8000     │    │   :3000      │
        └─────────────┘    └──────────────┘
               │
        ┌──────▼──────┐
        │   SQLite    │
        │ (volume)    │
        └─────────────┘
```

- **Nginx** — единая точка входа, маршрутизация по URL-путям
- **API-контейнер** — entrypoint автоматически создаёт `.env`, генерирует ключ, запускает миграции и сидинг (только при пустой БД)
- **Frontend-контейнер** — multi-stage сборка (build + runtime), SSR с server/client разделением API URL
- **Volume** `api-database` — персистентное хранилище SQLite между перезапусками

---

## Окружение

### Backend (`.env`)

```env
APP_NAME=ЛистЗадач
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
FRONTEND_URL=http://localhost:8080
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8080
```

### Frontend (`frontend/nuxt.config.ts`)

```ts
runtimeConfig: {
  // SSR: обращается к API внутри Docker-сети
  apiBaseUrl: 'http://api:8000/api',
  public: {
    // Клиент: относительный URL через Nginx
    apiBaseUrl: '/api',
  },
}
```

---

## API-справочник

### Авторизация

| Метод | Эндпоинт | Описание |
|---|---|---|
| `POST` | `/api/login` | Вход — возвращает Bearer-токен |
| `POST` | `/api/logout` | Отзыв токена |
| `GET` | `/api/user` | Данные текущего пользователя |

### Задачи (требуется авторизация: `Bearer <token>`)

| Метод | Эндпоинт | Описание |
|---|---|---|
| `GET` | `/api/tasks` | Список (пагинация, фильтрация) |
| `POST` | `/api/tasks` | Создать задачу |
| `GET` | `/api/tasks/{task}` | Просмотр (только владелец или админ) |
| `PUT` | `/api/tasks/{task}` | Обновить (только владелец) |
| `PATCH` | `/api/tasks/{task}/status` | Изменить только статус |
| `DELETE` | `/api/tasks/{task}` | Удалить (только владелец) |

**Параметры запроса** (`GET /api/tasks`):
- `status` — `pending` | `in_progress` | `completed`
- `search` — поиск по названию
- `sort` — `due_date` | `status` | `created_at` | `title`
- `direction` — `asc` | `desc`
- `page`, `per_page` — пагинация (макс. 50)

### Формат ошибок

```json
{
  "error": {
    "code": 422,
    "message": "Validation failed.",
    "details": {
      "title": ["The title field is required."]
    }
  }
}
```

Коды ответов: `401` (Не авторизован), `403` (Доступ запрещён), `404` (Не найдено), `422` (Ошибка валидации), `500` (Ошибка сервера).

---

## Архитектурные решения

### Почему decoupled-архитектура (Laravel ⇄ Nuxt)

Frontend и backend — отдельные проекты, общающиеся через REST API:

1. **Разделение ответственности** — backend отвечает за данные и логику; frontend — за UI/UX
2. **Независимое масштабирование** — API и SPA масштабируются независимо
3. **Свобода технологий** — frontend можно переписать без затрагивания backend
4. **Production-ready** — та же архитектура используется в любом серьёзном SaaS

### Почему Laravel Sanctum (а не JWT/Passport)

1. **Простота** — API-токены без лишней конфигурации
2. **Без overhead OAuth** — Passport избыточен для SPA
3. **Встроенный** — first-party пакет Laravel, без внешних зависимостей
4. **Управление токенами** — встроенное создание, отзыв и управление способностями

### Почему Service Layer (TaskService)

`TaskService` инкапсулирует бизнес-логику:

- **Применение политик** — `Gate::authorize()` внутри методов сервиса гарантирует, что авторизация никогда не будет обойдена
- **Тестируемость** — unit-тесты бизнес-логики без HTTP-слоя
- **Тонкие контроллеры** — контроллеры только обрабатывают HTTP-задачи (парсинг запроса, форматирование ответа)

### Почему PHP 8.4 Enum для статуса

```php
enum TaskStatus: string {
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
```

- **Типобезопасность** — невозможно передать невалидный статус
- **Автоматическое кастование** — Eloquent преобразует enum ↔ строку БД
- **Единый источник истины** — `TaskStatus::values()` задаёт правила валидации

---

## Структура проекта

```
ListOfTask/
├── app/
│   ├── Enums/TaskStatus.php              # Enum статусов задачи
│   ├── Http/
│   │   ├── Controllers/Api/              # REST-контроллеры
│   │   ├── Requests/Task/                # Валидация форм
│   │   └── Resources/                    # API-ресурсы
│   ├── Models/
│   │   ├── User.php                      # Пользователь с Sanctum
│   │   └── Task.php                      # Задача со scope-методами
│   ├── Policies/TaskPolicy.php           # Авторизация: владелец + админ
│   └── Services/TaskService.php          # Слой бизнес-логики
├── database/
│   ├── factories/                        # Фабрики моделей
│   ├── migrations/                       # Миграции БД
│   └── seeders/                          # Демо-данные (4 пользователя, 64 задачи)
├── tests/
│   ├── Feature/Auth/                     # Тесты авторизации
│   ├── Feature/Task/                     # Тесты CRUD задач (включая админа)
│   └── Unit/TaskPolicyTest.php           # Unit-тесты политик
├── frontend/                             # Приложение Nuxt 4
│   ├── components/
│   │   ├── ui/                           # Переиспользуемые UI-компоненты
│   │   └── task/                         # Компоненты задач
│   ├── composables/                      # Vue-композаблы
│   ├── layouts/                          # Макеты страниц
│   ├── middleware/                        # Middleware маршрутов
│   ├── pages/                            # Страницы (login, dashboard)
│   ├── services/api.ts                   # HTTP-клиент (SSR/client разделение)
│   ├── stores/                           # Управление состоянием Pinia
│   ├── tests/                            # Vitest: store + компоненты
│   └── types/                            # TypeScript-интерфейсы
├── nginx/nginx.conf                      # Nginx: reverse proxy конфигурация
├── config/scribe.php                     # OpenAPI/Swagger документация
├── Dockerfile                            # Контейнер Laravel
├── frontend/Dockerfile                   # Контейнер Nuxt (multi-stage)
├── docker-compose.yml                    # Оркестрация (api + frontend + nginx)
├── entrypoint.sh                         # Runtime: key, migrate, seed, cache
├── Makefile                              # Команды сборки и запуска
└── .env.example                          # Шаблон переменных окружения
```

---

## Тестирование

```bash
# Тесты backend (33 теста, 62 assertions)
php artisan test

# Тесты frontend (13 тестов)
cd frontend && npm test
```

---

## Демо-данные

При первом запуске `make up` база автоматически заполняется:

- **4 пользователя** (админ + 3 обычных)
- **64 задачи** с различными статусами
- Статусы: ожидает, в работе, завершена

При повторных запусках данные **не пересоздаются** (проверка по количеству пользователей). Для сброса: `make clean && make up`.

---

## Возможности

- ✅ Авторизация (вход/выход)
- ✅ CRUD задач (создание, просмотр, редактирование, удаление)
- ✅ Управление статусом (ожидает → в работе → завершена)
- ✅ Доступ владельца + админа (Policy)
- ✅ Фильтрация по статусу
- ✅ Поиск с debounce
- ✅ Сортировка по сроку, статусу, дате создания, названию
- ✅ Пагинация
- ✅ Skeleton-загрузка
- ✅ Empty-стейты
- ✅ Toast-уведомления
- ✅ Адаптивный дизайн (моб./планшет/десктоп)
- ✅ Клиентская валидация форм
- ✅ Единый JSON-формат ошибок
- ✅ Docker Compose + Nginx reverse proxy
- ✅ OpenAPI/Swagger документация (`/api/docs`)
- ✅ Тесты backend и frontend

---

## Лицензия

MIT
