# 📦 Inventory & Stock Management System

A full-stack web application for managing inventory, tracking stock levels, and monitoring sales & purchases. Built with PHP and MySQL, designed for small to medium-sized businesses.

**🔗 Live Demo:** [kulveer-singh.wuaze.com](https://kulveer-singh.wuaze.com)

---

## ✨ Features

### Core Functionality
- ✅ **User Authentication** - Secure login & registration system
- ✅ **Product Management** - Add, edit, delete, and track products
- ✅ **Stock Tracking** - Real-time inventory monitoring with low-stock alerts
- ✅ **Sales Management** - Record and track all sales transactions
- ✅ **Purchase Management** - Manage supplier purchases and restocking
- ✅ **Dashboard** - Analytics and reporting with visual charts
- ✅ **User Roles** - Admin and staff level access control
- ✅ **Responsive Design** - Mobile-friendly interface

### Technical Highlights
- Secure password hashing and session management
- Database optimization for fast queries
- Export functionality for reports
- Audit logs for transaction tracking

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
├── index.php              # Login & authentication
├── dashboard.php          # Admin dashboard
├── products/              # Product management pages
│   ├── add_product.php
│   ├── edit_product.php
│   └── view_products.php
├── sales/                 # Sales transaction pages
│   ├── add_sale.php
│   └── sales_history.php
├── purchases/             # Purchase management pages
│   ├── add_purchase.php
│   └── purchase_history.php
├── css/                   # Stylesheets
├── js/                    # JavaScript files
├── includes/              # Database connection & functions
│   ├── config.php
│   └── functions.php
└── uploads/               # Product images
```

---

## 🚀 Getting Started

### Installation

1. **Download the project**
   ```bash
   git clone https://github.com/KulveerSingh60/Inventory-Stock-MAnagement-System.git
   ```

2. **Set up Database**
   - Create a MySQL database
   - Import the database schema (included in `/db/schema.sql`)
   - Update database credentials in `includes/config.php`

3. **Configure Settings**
   ```php
   // includes/config.php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'inventory_system');
   ```

4. **Access the Application**
   - Open `http://localhost/inventory-system` in your browser
   - Default login credentials (change after first login):
     - Username: `admin`
     - Password: `admin123`

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

- ✅ SQL Injection Prevention (Prepared Statements)
- ✅ XSS Protection (Input Sanitization)
- ✅ CSRF Tokens on all forms
- ✅ Secure password hashing (bcrypt)
- ✅ Session management with timeout
- ✅ Role-based access control

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
- **Last Updated:** July 2026
- **Database Tables:** 5+
- **Features:** 50+

---

**⭐ If you found this helpful, please star this repository!**

Made with ❤️ by Kulveer Singh
