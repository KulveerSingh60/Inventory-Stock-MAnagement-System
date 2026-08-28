STOCKSMASTER PRO - DATABASE SETUP INSTRUCTIONS
===============================================

IMPORTANT: The application now uses bcrypt password hashing (see html/login.php),
so passwords MUST be stored as hashes. Do NOT insert a plaintext password.

Simplest setup:

1. Start WampServer / XAMPP (Apache + MySQL, icon should be green).
2. Open PHPMyAdmin (usually http://localhost/phpmyadmin).
3. Import the file  db/schema.sql
   This creates the inventory_system database, all tables, and seed data
   (including the admin user with a bcrypt-hashed password).

Or from the command line:
   mysql -u root -p < db/schema.sql

Default login:
   Username: admin
   Password: admin123   (change it after your first login)

To create additional users with a hashed password, generate a hash first:
   php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_DEFAULT);"
Then insert the returned hash into the users table.
