# Travel Website Design — Nepal Travel Agency Management System

A full-stack PHP travel agency web application featuring package browsing, online booking, user authentication, an AI-powered chatbot, and a comprehensive admin dashboard. Built to run on XAMPP/WAMP (Apache + MySQL + PHP).

---

## Features

### User-Facing
- **Homepage** — Hero slider showcasing Nepal destinations with call-to-action links
- **Package Browsing** — Browse and search travel packages with live search suggestions
- **Package Details** — Per-package detail pages with description and pricing
- **Booking System** — Multi-step booking form with date selection and guest count
- **PDF Invoice** — Auto-generated booking invoice (via FPDF library)
- **Contact / Messages** — Contact form that notifies the admin panel
- **User Authentication** — Register, login, logout with PHP session management
- **AI Chatbot** — Conversational assistant powered by Google Gemini 1.5 Flash

### Admin Dashboard (`/admin/`)
- Overview stats: total bookings, today's signups, total sales
- Manage all bookings and view recent activity
- Add, edit, and delete travel packages directly from the dashboard
- View and manage registered users
- Read contact messages with seen/unseen status and notification banner
- Chatbot history viewer and API usage analytics
- Payment records
- Reports

---

## Tech Stack

| Layer | Technology |
|---|---|
| Server | Apache (XAMPP) |
| Backend | PHP 7.4+ (PDO for DB access) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript |
| Slider | Swiper.js |
| PDF | FPDF 1.86 |
| AI | Google Gemini 1.5 Flash API |

---

## Project Structure

```
Travel-website-Design-main/
├── index.php                  # Homepage
├── about.php                  # About page
├── package.php                # Package listing
├── package_details.php        # Single package view
├── book.php                   # Booking form
├── book_form.php              # Booking form handler
├── login.php / signup.php     # Auth pages
├── message.php                # Floating chatbot widget (included in pages)
├── search_results.php         # Search results
├── search_suggest.php         # AJAX search suggestions
├── submit_message.php         # Contact form handler
├── admin/                     # Admin dashboard
│   ├── admin.php              # Dashboard overview
│   ├── all_booking.php        # All bookings list
│   ├── users.php              # User management
│   ├── messages.php           # Contact messages inbox
│   ├── payment.php            # Payment / API credits
│   ├── reports.php            # Reports
│   ├── chatbot_history.php    # Chatbot conversation log
│   ├── api_usage.php          # API usage analytics
│   ├── api_dashboard.php      # API dashboard
│   ├── auth_admin.php         # Admin session guard
│   ├── package/               # Package CRUD
│   │   ├── package_form.php
│   │   ├── packages_enhanced.php
│   │   └── edit_package_enhanced.php
│   └── component/
│       └── nav_admin.php      # Admin sidebar nav
├── chatbot/                   # AI chatbot module
│   ├── chat.php               # Chat endpoint
│   ├── aiService.php          # Gemini API integration
│   ├── intent.php             # Intent detection
│   ├── packageService.php     # Package query handler
│   ├── quota.php              # API quota management
│   ├── cache.php              # Response caching (1 hr TTL)
│   └── .env                   # GEMINI_API_KEY (git-ignored)
├── database/
│   ├── db_connect.php         # PDO connection
│   ├── create_database.sql    # Full schema + seed data
│   ├── setup_database.php     # Browser-based setup helper
│   └── *.sql                  # Additional migration scripts
├── component/
│   ├── navbar_links.php       # Shared navbar
│   └── footer.php             # Shared footer
├── css/
│   ├── style.css              # Main stylesheet
│   └── admin.css              # Admin panel styles
├── js/
│   ├── script.js              # Frontend scripts
│   └── admin.js               # Admin scripts
├── images/                    # Static images
└── fpdf186/                   # FPDF library
```

---

## Database Schema

The application uses **two database names** across different setup files (`traveldb` in the legacy schema, `travel_website_db` in the current one). Use `travel_website_db` for a fresh install.

### Core Tables

| Table | Purpose |
|---|---|
| `users` | Registered users and admins (role: `user` / `admin`) |
| `packages` | Travel packages (title, description, price, image) |
| `bookings` | Booking records with status tracking |
| `messages` | Contact form submissions with seen/unseen flag |

### Chatbot Tables

| Table | Purpose |
|---|---|
| `chatbot_history` | Per-conversation message + reply log |
| `chatbot_api_usage` | Per-call API usage log |
| `chatbot_system_quota` | System-wide API call limit (default: 500) |

---

## Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP 7.4+) running
- PHP extensions: `pdo_mysql`, `curl`, `mbstring`

### 1. Place Files
Copy the project folder into `C:\xampp\htdocs\12\Travel-website-Design-main\`.

### 2. Create the Database
**Option A — Browser setup:**
```
http://localhost/12/Travel-website-Design-main/database/setup_database.php
```

**Option B — phpMyAdmin:**
Import `database/create_database.sql` into phpMyAdmin.

Then run the additional migration scripts in order:
```
database/chatbot_admin_tables.sql
database/chatbot_messages.sql
database/create_additional_tables.sql
```

### 3. Configure Database Connection
Edit `database/db_connect.php` and set your MySQL credentials:
```php
$host = 'localhost';
$dbname = 'travel_website_db';
$username = 'root';
$password = '';
```

### 4. Configure the AI Chatbot
Create `chatbot/.env` and add your Gemini API key:
```
GEMINI_API_KEY=your_key_here
```
Get a free key at [Google AI Studio](https://aistudio.google.com/).

### 5. Done
Visit `http://localhost/12/Travel-website-Design-main/` in your browser.

---

## Default Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@travel.com | admin123 |

> **Change the default admin password immediately after first login.**

---

## Security Notes

- Delete `database/setup_database.php` after successful setup.
- The `chatbot/.env` file is git-ignored — never commit API keys.
- Passwords are hashed with `password_hash()` (bcrypt).
- All database queries use PDO prepared statements.
- Admin routes are protected by session-based auth (`admin/auth_admin.php`).
