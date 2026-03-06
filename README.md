# 🏆 SportDeck

**Smart Tournament Management for teams, players, and fans.**

SportDeck is a full-stack web application for managing sports tournaments — including fixtures, match results, team and player administration, and role-based dashboards for admins and players.

---

## 🌐 Live Demo (GitHub Pages)

A static HTML preview of the project is available via GitHub Pages:

👉 **[View Live Demo](https://iamziyan.github.io/SportDeck/)**

> The demo uses hardcoded sample data. No backend or database is required to browse it.

---

## ✨ Features

- 🏅 **Tournament Listings** — View all tournaments with sport type, dates, and status badges
- 📅 **Fixtures** — Browse upcoming scheduled matches with venue and time info
- 📊 **Results** — Score cards showing match outcomes and winners
- 🔐 **Login / Register** — Role-based authentication (Admin & Player)
- 👤 **Player Dashboard** — Personal stats, upcoming matches, recent results
- 🛠️ **Admin Panel** — Manage tournaments, teams, matches, results, players & users

---

## 🗂️ Project Structure

```
SportDeck/
│
├── docs/                        ← GitHub Pages static preview
│   ├── css/
│   │   └── style.css            ← Shared stylesheet
│   ├── index.html               ← Tournaments (home)
│   ├── fixtures.html            ← Upcoming fixtures
│   ├── results.html             ← Match results
│   ├── login.html               ← Login page
│   ├── register.html            ← Register page
│   ├── register-success.html    ← Registration confirmation
│   ├── player/
│   │   └── dashboard.html       ← Player dashboard
│   └── admin/
│       ├── dashboard.html       ← Admin overview
│       ├── tournaments.html     ← Manage tournaments
│       ├── teams.html           ← Manage teams
│       ├── matches.html         ← Manage matches
│       ├── results.html         ← Post results
│       ├── players.html         ← View players
│       └── users.html           ← View users
│
├── includes/
│   ├── config.php               ← DB connection & helpers
│   ├── header.php               ← Shared navbar/head
│   ├── footer.php               ← Shared footer
│   └── auth.php                 ← Auth guard
│
├── admin/                       ← PHP admin panel
│   ├── dashboard.php
│   ├── tournaments.php
│   ├── teams.php
│   ├── matches.php
│   ├── results.php
│   ├── players.php
│   └── users.php
│
├── player/
│   └── dashboard.php            ← PHP player dashboard
│
├── css/
│   └── style.css                ← Main stylesheet (PHP site)
│
├── index.php                    ← Home page
├── fixtures.php                 ← Fixtures page
├── results.php                  ← Results page
├── login.php                    ← Login page
├── register.php                 ← Register page
├── logout.php                   ← Logout handler
└── db.sql                       ← Database schema & seed data
```

---

## 🛠️ Tech Stack

| Layer     | Technology          |
|-----------|---------------------|
| Backend   | PHP 8+              |
| Database  | MySQL               |
| Frontend  | HTML, CSS (Vanilla) |
| Fonts     | Inter (Google Fonts)|
| Hosting   | GitHub Pages (demo) |

---

## ⚙️ Local Setup (PHP + MySQL)

### 1. Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB
- A local server: [XAMPP](https://www.apachefriends.org/), [MAMP](https://www.mamp.info/), or PHP built-in server

### 2. Clone the Repository

```bash
git clone https://github.com/iamziyan/SportDeck.git
cd SportDeck
```

### 3. Import the Database

Open **phpMyAdmin** (or MySQL CLI) and import the schema:

```bash
mysql -u root -p < db.sql
```

### 4. Configure Database Connection

Edit `includes/config.php` and set your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sportdeck');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Start the Server

**Option A – XAMPP/MAMP:** Place the project in your `htdocs` folder and start Apache.

**Option B – PHP built-in server:**

```bash
php -S localhost:8000
```

Then open: [http://localhost:8000](http://localhost:8000)

---

## 🔑 Demo Credentials

| Role   | Email                    | Password |
|--------|--------------------------|----------|
| Admin  | admin@sportdeck.com      | 123456   |
| Player | player@sportdeck.com     | 123456   |

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

<p align="center">Made with ❤️ by <a href="https://github.com/iamziyan">iamziyan</a></p>
