# Sports Tournament Management System

A full-featured PHP/MySQL web application for managing sports tournaments, teams, matches, results, and schedules.

## Features

- **Tournaments** - Create, view, and manage tournaments with registration
- **Teams** - Register teams for tournaments with captain and contact info
- **Matches** - Schedule matches between teams with dates, times, and venues
- **Results** - Record match scores and view result history
- **Schedule** - View match schedule by date, filtered by tournament
- **Dashboard** - Overview with stats and tournament card catalogue
- **User System** - Registration, login, and role-based access

## Requirements

- PHP 7.4+ (with PDO, mysqli extensions)
- MySQL 5.7+ or MariaDB
- Web server (Apache/Nginx)

## Installation

1. **Clone or copy** the project into your web server document root (e.g., `htdocs/sports` or `www/sports`).

2. **Create the database:**
   ```bash
   mysql -u root -p < schema.sql
   ```

3. **Configure database** in `config.php`:
   - Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` if needed.

4. **Set BASE_PATH** in `config.php` if the app is in a subdirectory:
   - Example: If the app is at `http://localhost/sports/`, set `BASE_PATH` to `'/sports'`.

5. **Access the application** in your browser.

## Default Admin Login

- **Username:** admin
- **Password:** password

Change the password after first login.

## Project Structure

```
sports/
├── api/              # API endpoints (e.g., teams JSON)
├── assets/
│   └── css/          # Stylesheets
├── includes/         # Header, footer components
├── config.php        # Database and app config
├── index.php         # Home page
├── login.php         # Login form
├── register.php      # Registration form
├── logout.php        # Logout handler
├── dashboard.php     # User dashboard
├── tournaments.php   # Tournament management
├── teams.php         # Teams list
├── matches.php       # Match scheduling
├── results.php       # Result recording
├── schedule.php      # Match schedule view
├── schema.sql        # Database schema
└── README.md
```

## Technologies

- **Frontend:** PHP (no .html files), CSS
- **Backend:** PHP, MySQL
- **Database:** MySQL (schema in schema.sql)
