# Study Group Platform

Simple PHP + MySQL web app that helps students:
- register/login
- create study sessions
- browse available sessions
- join or leave sessions
- manage participants as session creator
- view upcoming/joined sessions

## Stack
- Frontend: HTML, CSS, JavaScript
- Backend: PHP (PDO)
- Database: MySQL

## Setup with XAMPP + phpMyAdmin
1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Open phpMyAdmin at [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Create a database named `study_platform`.
4. Select the new database, click **Import**, and import `database/schema.sql`.
5. Confirm DB settings in `config/database.php`:
   - host: `localhost`
   - database: `study_platform`
   - user: `root`
   - password: `""` (empty by default in XAMPP)
6. Move this project folder into XAMPP `htdocs` (or create a virtual host).
7. Open the app in browser:
   - `http://localhost/Study%20Platform/register.php`

## Alternative (built-in PHP server)
- You can still run: `php -S localhost:8000`
- Then open: [http://localhost:8000/register.php](http://localhost:8000/register.php)

## Main Pages
- `register.php` - account creation
- `login.php` - user login
- `index.php` - browse and join sessions
- `create_session.php` - create session
- `my_sessions.php` - created and joined sessions
- `session_details.php` - participant list and management
