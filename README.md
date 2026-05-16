#  Metro Ticketing System
 
A PHP/MySQL web app for booking metro tickets online.
Users can register, book tickets between stations, make a simulated payment,
and get a QR-based ticket. Admins get a separate dashboard to manage everything.
 
## ✨ Features
 
**User Side**
- Register and login
- Book tickets by selecting source and destination
- Automatic fare calculation based on stations
- Simulated payment flow
- QR ticket generation after payment
- View travel history and logs
**Admin Side**
- Admin login and dashboard
- Manage all users and bookings
- Configure fare settings dynamically
- Export bookings and travel logs as CSV
- Dashboard charts and travel stats
## 🛠️ Built With
 
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
 
## ⚙️ Run Locally (WAMP/XAMPP)
 
```bash
git clone https://github.com/snehasanu64/metro-ticketing-system.git
```
 
1. Copy folder to `C:/wamp64/www/`
2. Import `database.sql` in phpMyAdmin
3. Update DB credentials in `config.php` if needed
4. Open `http://localhost/metro-ticketing-system`
## 📋 Requirements
 
- WAMP or XAMPP
- PHP 7+
- MySQL
## 📝 Note
 
This is a learning project with simulated payment and QR ticket generation.
Not connected to any real metro system or payment gateway.
 
---
Built by [Sneha](https://github.com/snehasanu64)
 
