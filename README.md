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
| Стили | Tailwind CSS 4 |
| Уведомления | vue-sonner |
| Контейнеризация | Docker Compose |
| Тестирование | PHPUnit 12 (26 тестов) |

---

## Быстрый старт (Docker)

```bash
docker compose up -d
```

- API: http://localhost:8000
- Frontend: http://localhost:3000
- Тестовый аккаунт: `test@example.com` / `password`

---

## Ручная установка

### Требования

- PHP 8.3+
- Composer
- Node.js 22+
- npm 10+

### Backend

```bash
# Установка зависимостей
composer install

# Генерация ключа приложения
php artisan key:generate

# Миграции и сидинг демо-данных
php artisan migrate --seed

# Запуск dev-сервера
php artisan serve --port=8000
```

### Frontend

```bash
cd frontend

# Установка зависимостей
npm install

# Запуск dev-сервера
npm run dev
```

---

## Окружение

### Backend (`.env`)

```env
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000
SANCTUM_TOKEN_PREFIX=todo_
```

### Frontend (`frontend/nuxt.config.ts`)

```ts
runtimeConfig: {
  public: {
    apiBaseUrl: 'http://localhost:8000/api',
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
| `GET` | `/api/tasks/{task}` | Просмотр (только владелец) |
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

### Почему без Repository Pattern

Eloquent уже предоставляет богатый query builder и active record. Добавление Repository поверх Eloquent:
- Добавит абстракцию без пользы для данного масштаба
- Дублирует функциональность Eloquent scopes
- Увеличит накладные расходы без значимого разделения

`TaskService` + Eloquent scopes + методы query builder дают достаточное разделение.

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
│   ├── Enums/TaskStatus.php          # Enum статусов задачи
│   ├── Http/
│   │   ├── Controllers/Api/          # REST-контроллеры
│   │   ├── Requests/Task/            # Валидация форм
│   │   └── Resources/                # API-ресурсы
│   ├── Models/
│   │   ├── User.php                  # Пользователь с Sanctum
│   │   └── Task.php                  # Задача со scope-методами
│   ├── Policies/TaskPolicy.php       # Авторизация: только владелец
│   └── Services/TaskService.php      # Слой бизнес-логики
├── bootstrap/app.php                 # Обработчик исключений, маршрутизация
├── config/                           # Конфигурация Laravel
├── database/
│   ├── factories/                    # Фабрики моделей
│   ├── migrations/                   # Миграции БД
│   └── seeders/                      # Демо-данные
├── routes/api.php                    # API-маршруты
├── tests/
│   ├── Feature/Auth/                 # Тесты авторизации
│   ├── Feature/Task/                 # Тесты CRUD задач
│   └── Unit/TaskPolicyTest.php       # Unit-тесты политик
├── frontend/                         # Приложение Nuxt 4
│   ├── components/
│   │   ├── ui/                       # Переиспользуемые UI-компоненты
│   │   └── task/                     # Компоненты задач
│   ├── composables/                  # Vue-композаблы
│   ├── layouts/                      # Макеты страниц
│   ├── middleware/                    # Middleware маршрутов
│   ├── pages/                        # Страницы (login, dashboard)
│   ├── services/api.ts               # HTTP-клиент
│   ├── stores/                       # Управление состоянием Pinia
│   └── types/                        # TypeScript-интерфейсы
├── Dockerfile                        # Контейнер Laravel
├── frontend/Dockerfile               # Контейнер Nuxt
└── docker-compose.yml                # Оркестрация
```

---

## Тестирование

```bash
# Тесты backend (26 тестов, 52 assertion)
php artisan test

# Проверка типов frontend
cd frontend && npx nuxi typecheck
```

---

## Демо-данные

После `php artisan db:seed` база содержит:

- **3 пользователя** (1 тестовый + 2 с задачами)
- **62 задачи** (12 для тестового, по 25 у остальных)
- Статусы: ожидает, в работе, завершена

Тестовый аккаунт: `test@example.com` / `password`

---

## Возможности

- ✅ Авторизация (вход/выход)
- ✅ CRUD задач (создание, просмотр, редактирование, удаление)
- ✅ Управление статусом (ожидает → в работе → завершена)
- ✅ Доступ только владельца (Policy)
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
- ✅ Поддержка Docker

---

## Лицензия

MIT
# ListOfTask
