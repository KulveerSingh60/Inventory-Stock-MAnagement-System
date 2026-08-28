# 📦 Inventory & Stock Management System

A full-stack web application for managing inventory, tracking stock levels, and monitoring sales & purchases. Built with PHP and MySQL, designed for small to medium-sized businesses.

**🔗 Live Demo:** [kulveer-singh.wuaze.com](https://kulveer-singh.wuaze.com)

---

## ✨ Features

### Core Functionality
- ✅ **User Authentication** - Secure login (bcrypt-hashed passwords)
- ✅ **Product Management** - Add, edit, delete, and track products
- ✅ **Stock Tracking** - Real-time inventory monitoring with low-stock alerts
- ✅ **Sales Management** - Record and track all sales transactions
- ✅ **Purchase Management** - Manage supplier purchases and restocking
- ✅ **Dashboard** - Analytics and reporting with visual charts
- ✅ **Responsive Design** - Mobile-friendly interface

### Technical Highlights
- Secure password hashing (bcrypt) and session management
- Prepared statements against SQL injection
- Non-invasive, filterable reports for products, sales, and stock

---

## 🛠️ Tech Stack

| Category | Technology |
|----------|-----------|
| **Backend** | PHP 7.x |
| **Database** | MySQL |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Server** | Apache (Infinity Free Hosting) |

---

## 📁 Project Structure

```
Inventory-Stock-Management-System/
├── db_config.php           # Database connection (edit credentials here)
├── db/
│   └── schema.sql          # MySQL schema + seed data (import first)
├── html/                   # All pages
│   ├── login.php           # Login & authentication
│   ├── index.php           # Dashboard (stats & charts)
│   ├── products.php        # Product management
│   ├── categories.php      # Category management
│   ├── sales.php           # Sales transactions
│   ├── purchases.php       # Purchase / restocking
│   ├── stock_status.php    # Real-time stock levels
│   └── reports.php         # Reports & analytics
├── css/                    # Stylesheets
│   ├── style.css
│   └── ihover.css
└── js/                     # JavaScript files
```

---

## 🚀 Getting Started

### Installation

1. **Download the project**
   ```bash
   git clone https://github.com/KulveerSingh60/Inventory-Stock-MAnagement-System.git
   ```

2. **Set up Database**
   - Create a MySQL database (or let `db/schema.sql` create it)
   - Import the schema: `mysql -u root -p < db/schema.sql`
   - The schema creates the `inventory_system` database, all tables, and seed data.

3. **Configure Settings**
   ```php
   // db_config.php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "inventory_system";
   ```

4. **Access the Application**
   - Start Apache + MySQL (e.g. XAMPP/WAMP)
   - Open `http://localhost/Inventory-Stock-MAnagement-System/html/login.php`
   - Default login credentials (change after first login):
     - Username: `admin`
     - Password: `admin123`
   - The password is stored as a bcrypt hash in the database.

---

## 📊 Database Schema

### Tables Overview
- **users** - User accounts and authentication
- **products** - Product inventory
- **sales** - Sales transactions
- **purchases** - Purchase orders
- **stock_movements** - Stock history logs

---

## 👨‍💻 Usage

### For Admin Users
1. Log in with admin credentials
2. Access dashboard for overview
3. Manage products, sales, and purchases
4. Generate reports and analytics

### For Staff Users
1. Log in with staff credentials
2. Record sales transactions
3. View inventory status
4. Process purchase orders

---

## 🔐 Security Features

- ✅ SQL Injection Prevention — all queries use **prepared statements** / parameterized binding
- ✅ Secure password storage — bcrypt (`password_hash` / `password_verify`)
- ✅ Session regeneration on login and server-side session auth check on every page
- ✅ Output escaping in user-supplied search values (`htmlspecialchars`)
- ⚠️ Note: this is a learning/demo project. For a production deployment, add **CSRF tokens** to all POST forms and **role-based access control** before going live.

---

## 📱 Responsive Design

The application is fully responsive and works on:
- 📱 Mobile devices (320px+)
- 📱 Tablets (768px+)
- 🖥️ Desktop (1024px+)

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🐛 Bug Reports & Feature Requests

Have a bug or feature request? Please create an issue on GitHub:
- [Report a Bug](https://github.com/KulveerSingh60/Inventory-Stock-MAnagement-System/issues)

---

## 📧 Contact & Support

- **Email:** kulveerxsingh60@gmail.com
- **GitHub:** [@KulveerSingh60](https://github.com/KulveerSingh60)
- **LinkedIn:** [Kulveer Singh](https://linkedin.com/in/kulveer-singh-136a56308)

---

## 📈 Project Statistics

- **Language:** PHP, MySQL, HTML5, CSS3, JavaScript
- **Status:** ✅ Active & Maintained
- **Last Updated:** August 2026
- **Database Tables:** 5 (users, categories, products, sales, purchases)
- **Features:** Authentication, inventory, categories, sales, purchases, stock alerts, reports & analytics

---

**⭐ If you found this helpful, please star this repository!**

Made with ❤️ by Kulveer Singh
