# Groq Chat Interface

A multi-user AI chat application built on top of [Groq's](https://groq.com) inference API. It gives you a fully working chat product out of the box — streaming responses, conversation history, usage controls, and an admin panel — so you can focus on customizing rather than building from scratch.

---

## The Problem It Solves

Integrating an LLM into a real product means dealing with more than just an API call. You need user accounts, conversation persistence, streaming UI, token/rate-limit enforcement, and a way to manage settings without touching code. This project wires all of that together in a clean Laravel + Vue stack so you can deploy a production-ready Groq-powered chat app without starting from zero.

---

## Features

### Chat
- Real-time **streaming responses** via Server-Sent Events
- Persistent **conversation history** with the ability to delete conversations
- **Markdown rendering** with syntax-highlighted code blocks
- **Follow-up suggestions** after each AI response
- **Token estimation** before a message is sent — no surprise context overflows

### Authentication
- Register, login, logout
- Forgot password / reset password via email

### Usage & Limits
- **Free plan** enforcement: configurable daily message limit, daily token limit, and monthly token limit
- **Usage dashboard** showing how much of the allowance has been consumed
- Automatic blocking with clear error messages when limits are hit

### Admin Panel
- **AI Settings**: set the Groq API key, choose the active model, write the system prompt, and configure context window / token / rate-limit values — all from the UI, no `.env` changes needed
- **User Management**: create, update, and delete users; assign free or pro plans

### Profile
- Update name and email
- Change password

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend bridge | Inertia.js |
| Frontend | Vue 3, Bootstrap 5, Bootstrap Icons, SCSS |
| AI | Groq API (HTTP, streaming SSE) |
| Markdown | markdown-it, highlight.js, DOMPurify |
| Alerts | SweetAlert2 |
| Build | Vite + Laravel Vite Plugin |

---

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- A database (SQLite works fine for local dev)
- A [Groq API key](https://console.groq.com)
- A mail driver configured (for password reset emails)

---

## Installation

**1. Clone and install dependencies**

```bash
git clone <repo-url> groq-chat
cd groq-chat
composer install
npm install
```

**2. Set up environment**

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure your database and mail settings:

```env
DB_CONNECTION=sqlite          # or mysql / pgsql

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=your@email.com
MAIL_FROM_NAME="Groq Chat"
```

> The Groq API key is managed from the admin panel after setup, not from `.env`.

**3. Run migrations**

```bash
php artisan migrate
```

**4. Build frontend assets**

```bash
npm run build
```

**5. Start the dev server**

```bash
composer run dev
```

This starts Laravel, the queue worker, log watcher, and Vite all at once. Visit `http://localhost:8000`.

---

## First-time Setup

1. Register an account — the first registered user is a regular user by default.
2. To make yourself an admin, run:
   ```bash
   php artisan tinker
   # then:
   App\Models\User::first()->update(['is_admin' => true]);
   ```
3. Log in, go to **Admin > AI Settings**, and paste your Groq API key.
4. Choose a model, optionally write a system prompt, and save. Chat is now live.

---

## Project Structure (quick reference)

```
app/
  Http/Controllers/
    Admin/          # AiSettings, UserManagement
    Auth/           # Login, Register, ForgotPassword, ResetPassword
    ChatController, ConversationController, ProfileController, WorkspaceController
  Models/           # User, Conversation, Message, AiSetting
  Services/         # GroqService, AiSettingsService, ChatLimitService, UserUsageService
  Support/          # AssistantPrompt, AssistantMessageFormatter, TokenEstimator

resources/js/
  Pages/            # Chat, Admin, Auth, Settings, Usage, Landing
  Components/
    Chat/           # ChatWindow, ChatWorkspace, MessageBubble, MessageInput, StreamingText, ...
    Layout/         # AppLayout, GuestLayout, Navbar, Sidebar
    UI/             # AppButton, AppCard, AppInput, StatusBadge
  Utils/            # renderRichText, estimateTokens
```

---

## License

MIT
