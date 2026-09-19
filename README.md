# BoardNest
> A verified student boarding platform for Sri Lanka

BoardNest connects university students with landlords offering verified, trustworthy accommodation. Every listing is physically inspected by a Field Agent and approved by an Admin before it goes live — replacing informal channels like Facebook groups and word-of-mouth with a structured, accountable platform.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Backend | PHP (no frameworks) |
| Database | MySQL |
| Distance Calculation | Haversine formula (implemented in PHP) |
| Payment | PayHere (sandbox mode) |
| Email | Native PHP mail() |
| Version Control | Git + GitHub |
| Local Server | XAMPP / WAMP (Apache) |

---

## Features

**Students** — Browse verified listings publicly without an account. Search by city, KM radius from a landmark, or proximity to a university with area saturation data. Filter by price, gender preference, room type, and rating. Book individual room slots, track booking status, leave reviews, and file complaints.

**Landlords** — Register properties and rooms, manage booking requests, track verification status per listing, and manage subscription tiers (Standard / Pro) with PayHere.

**Field Agents** — Receive verification task assignments for their city, complete GPS-gated on-site inspection checklists, submit verification reports, investigate complaints, and submit area reports.

**Admin** — Approve listings and registrations, manage field agents, moderate complaints and reviews, publish area profiles, and configure commission and subscription settings.

---

## Getting Started

### Prerequisites
- XAMPP or WAMP installed (PHP 8.0+, MySQL 5.7+)
- Git installed

### Setup

**1. Clone the repository**
```bash
git clone https://github.com/boardnest-student-accomodation-platform/boardnest.git
cd boardnest
```

**2. Import the database**
- Open phpMyAdmin (`localhost/phpmyadmin`)
- Create a new database named `boardnest`
- Import `boardnest.sql`

**3. Configure the database connection**

Open `config/db.php` and update if needed:
```php
$host     = 'localhost';
$dbname   = 'boardnest';
$username = 'root';
$password = '';
```

**4. Start your local server**
- Place the project folder inside `htdocs` (XAMPP) or `www` (WAMP)
- Start Apache and MySQL from the control panel
- Visit `http://localhost/boardnest`

### Default Admin Account
```
Email:    admin@boardnest.lk
Password: admin123
```
> Change this immediately after first login.

---

## Project Structure

```
boardnest/
├── config/          # Database connection
├── includes/        # Shared session, header, footer
├── modules/
│   ├── student/     # Student dashboard and actions
│   ├── landlord/    # Landlord dashboard and actions
│   ├── field_agent/ # Field agent dashboard and actions
│   └── admin/       # Admin dashboard and actions
├── public/
│   └── assets/      # CSS, JS, images, uploads
├── login.php
├── logout.php
├── register.php
├── index.php        # Role-based redirect after login
├── index.html       # Public landing page
└── boardnest.sql    # Database schema
```

---

## Team

| Member | Module |
|---|---|
| Member 1 | Student Module |
| Member 2 | Landlord Module |
| Member 3 | Field Agent Module |
| Member 4 | Admin Module |

---

## Contributing

1. Pull the latest changes before starting work
```bash
git pull
```
2. Work only inside your own module folder
3. Never modify `config/db.php` or `includes/session.php` without group agreement
4. Commit with a clear message
```bash
git add .
git commit -m "Brief description of what you changed"
git push
```

---

## License

This project is for academic purposes. See `LICENSE` for details.
