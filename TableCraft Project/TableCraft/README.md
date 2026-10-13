# TableCraft
### An Automated Multi Category Data Formatting Web Application

**Made by:** Thumar Paras — BCA Semester 6, GK & CK Bosamia College, Jetpur
**Stack:** HTML5, CSS3 + Bootstrap 5, PHP, MySQL (WampServer)

---

## What it does
Type your data and TableCraft instantly formats it into a clean, styled table.
Every table can be customised (theme, accent color, font), edited live, and
exported as **PDF, Word (DOC) or PNG**.

Categories included: Student Record · Shop Order · Employee Info ·
Library Book List · Product Stock · Commercial Category · Custom Category Editor.

## Quick Start (WampServer)
1. Copy the `tablecraft` folder into `C:\wamp64\www\`
2. Start WampServer (wait for the icon to turn green)
3. Open `http://localhost/phpmyadmin`, create nothing manually — instead
   go to **Import** and import `database/tablecraft.sql`
   (this creates the `tablecraft` database and all tables automatically)
4. Visit `http://localhost/tablecraft` in your browser
5. Login with the demo account — **demo@tablecraft.com / Demo@123** —
   or click **Register** to create your own account

Full step-by-step instructions, screenshots guide and the complete project
report are provided separately as `TableCraft_Project_Report.pdf`.

## Folder Structure
```
tablecraft/
├── index.php                 entry point
├── new_table.php             creates a new table for any category
├── table_view.php             the dynamic table editor + export
├── dashboard.php               animated dashboard
├── my_tables.php                all saved tables, searchable
├── auth/                    login, register, logout
├── categories/               one page per category
├── includes/                header, footer, sidebar, shared logic
├── config/db.php               database connection
├── database/tablecraft.sql       import this in phpMyAdmin
├── assets/css/style.css          theme system (dark/bright, colors, fonts)
└── assets/js/                  table engine, theme toggle, export logic
```

## Default credentials
| Email | Password |
|---|---|
| demo@tablecraft.com | Demo@123 |

## Notes
- Change `DB_USER` / `DB_PASS` in `config/db.php` only if your MySQL root
  account is not the default blank-password WampServer setup.
- Figtree font and Bootstrap/Bootstrap Icons load from CDN — an internet
  connection is needed the first time each page loads them (or replace with
  local copies for a fully offline lab demo).
