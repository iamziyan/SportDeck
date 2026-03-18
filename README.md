# 🏆 SportDeck

> **Smart Tournament Management** for teams, players, and fans.

SportDeck is a PHP-based web application for managing sports tournaments. It supports multi-sport tournaments, team and player management, match scheduling, results tracking, and player self-registration — with separate portals for admins and players.

---

## ✨ Features

### 🔐 Authentication
- Secure player & admin login with password hashing (`password_hash` / `PASSWORD_DEFAULT`)
- Role-based access control (`player` / `admin`)
- Session-based authentication

### 🏅 Player Portal
- Browse all upcoming, ongoing, and completed tournaments
- Self-register / cancel registration for open tournaments
- View fixtures and match results
- Track personal registration history

### ⚙️ Admin Panel
- **Dashboard** — Overview of the system
- **Tournaments** — Create, manage, and update tournament status
- **Teams** — Add teams with coach info, linked to tournaments
- **Players** — Manage player rosters per team
- **Matches** — Schedule matches between teams
- **Results** — Record scores and winners
- **Users** — View and manage registered users

---

## 🗂️ Project Structure

```
sportdeck/
├── index.php                  # Public homepage — all tournaments listing
├── login.php                  # Login page
├── register.php               # Player registration
├── logout.php                 # Session destroy
├── fixtures.php               # Public fixtures view
├── results.php                # Public results view
├── migrate.php                # DB migration helper
├── db.sql                     # Full database schema + sample data
│
├── admin/
│   ├── dashboard.php
│   ├── tournaments.php
│   ├── teams.php
│   ├── players.php
│   ├── matches.php
│   ├── results.php
│   └── users.php
│
├── player/
│   ├── dashboard.php
│   ├── tournaments.php        # Tournament registration
│   └── my_registrations.php
│
├── includes/
│   ├── config.php             # DB connection, base URL, helper functions
│   ├── auth.php               # Auth guards
│   ├── header.php             # Shared HTML header & nav
│   └── footer.php             # Shared HTML footer
│
└── css/                       # Stylesheets
```

---

## 🛠️ Setup & Installation

### Prerequisites
- PHP **7.4+**
- MySQL **5.7+** (or MariaDB)
- A local server like **XAMPP**, **MAMP**, or **Laragon**

### Steps

1. **Clone or copy** the `sportdeck` folder into your web server's root directory:
   ```
   /xampp/htdocs/sportdeck/
   ```

2. **Create the database** — open MySQL and run:
   ```sql
   SOURCE /path/to/sportdeck/db.sql;
   ```
   Or import `db.sql` via **phpMyAdmin**.

3. **Configure the database** in `includes/config.php`:
   ```php
   $db_host = 'localhost';
   $db_user = 'root';       // Your MySQL username
   $db_pass = '';           // Your MySQL password
   $db_name = 'sportdeck_db';
   ```

4. **Set the base URL** in `includes/config.php`:
   ```php
   $base_url = '/sportdeck'; // Change if hosted at a different path
   ```

5. **Open in browser:**
   ```
   http://localhost/sportdeck/
   ```

---

## 🔑 Default Credentials

| Role   | Email                  | Password |
|--------|------------------------|----------|
| Admin  | admin@sportdeck.com    | 123456   |
| Player | john@example.com       | 123456   |
| Player | jane@example.com       | 123456   |

> **Change these passwords immediately in a production environment.**

---

## 🗃️ Database Schema

| Table                      | Description                              |
|----------------------------|------------------------------------------|
| `users`                    | All users with roles (player / admin)    |
| `tournaments`              | Tournament listings with status          |
| `teams`                    | Teams linked to tournaments              |
| `players`                  | Player rosters linked to teams           |
| `matches`                  | Scheduled matches between two teams      |
| `results`                  | Match outcomes with scores               |
| `tournament_registrations` | Player self-registration records         |

---

## 📄 License

This project is for educational/personal use. No license applied.
