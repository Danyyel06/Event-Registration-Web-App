**Faculty Event & Seminar Registration System**
A complete, secure web-based application built with PHP (PDO) and MySQL that allows university departments to manage event sign-ups digitally. This system replaces traditional paper sign-in sheets with an automated platform that features secure admin management and real-time capacity enforcement.


🚀 Key Features
Admin Panel (Secure Management)Secure Authentication: A protected login system using sessions and password hashing.
Event Dashboard: A quick overview of total events, upcoming seminars, and total student registrations.
Full CRUD: Create, Read, Update, and Delete events with ease.
Participant Lists: View and print a digital roster of every student registered for a specific event.
Student RegistrationLive Event List: A public-facing page showing all upcoming events.
Countdown Timers: Real-time "days remaining" display to create urgency for registration.
Capacity Enforcement: The system automatically counts current registrations and blocks new sign-ups when an event is full.
Unique Registration Codes: Every student receives a professional, unique confirmation code (e.g., EVT-A3F9B2) upon successful sign-up.


🛠️ Technical Stack
Backend: PHP 8.x.
Database: MySQL using PDO (PHP Data Objects) for secure, modern database interaction.
Security: Prepared Statements to prevent SQL Injection, password_hash() for credentials, and htmlspecialchars() for XSS protection.
Frontend: Clean HTML5 and CSS3 (fully responsive grid layouts).

📂 Project Structure
/admin: Contains all restricted pages like the dashboard, event creation, and editing tools.
/config: Holds the central db.php file for the database connection.
/includes: Reusable code snippets such as the auth_check.php gatekeeper.
/index.php: Main entry point that redirects users to the public event list.



⚙️ Installation & Setup
Environment: Install XAMPP (or any local server with Apache and MySQL).
Project Folder: Create a folder named event_registration inside your htdocs directory and place the project files there.
Database Setup:Open phpMyAdmin at http://localhost/phpmyadmin.
Create a new database named event_reg_db.
Import the provided SQL script to create the events, registrations, and admins tables.

Configuration: Ensure config/db.php has your correct database credentials (defaults are set for XAMPP).
