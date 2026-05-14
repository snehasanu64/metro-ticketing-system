# Metro Ticketing System

A simple metro booking and management system built with PHP and MySQL.

This project lets users book metro tickets, view QR-based tickets after payment, and keep track of travel logs. It also includes an admin panel for managing users, bookings, and fare settings.

## Features

- User registration and login
- Admin login and admin dashboard
- Metro ticket booking
- Fare calculation based on stations
- Simulated payment flow
- QR ticket generation
- Travel log management
- Booking and user management for admin
- Dynamic fare settings
- CSV export for bookings and travel logs
- Dashboard charts and travel stats

## Project Structure

- `index.php` - user login page
- `register.php` - new user registration
- `dashboard.php` - user travel dashboard
- `book_ticket.php` - ticket booking page
- `payment.php` - payment page
- `view_ticket.php` - QR ticket view
- `travel_logs.php` - user travel logs
- `admin_login.php` - admin login page
- `admin_dashboard.php` - admin dashboard
- `manage_users.php` - admin user management
- `manage_bookings.php` - admin booking management
- `admin_settings.php` - fare settings
- `config.php` - database and session setup

## Setup Instructions

1. Clone the repository to your local machine.
2. Import the `database.sql` file into MySQL.
3. Update the database credentials in `config.php` if needed.
4. Run the project in a local server like XAMPP or WAMP.
5. Open `index.php` in your browser.

## How It Works

- A user creates an account and logs in.
- The user books a metro ticket by selecting source and destination stations.
- The system calculates the fare automatically.
- After payment, the ticket is marked as paid and a QR ticket is generated.
- Users can view their ticket and travel history anytime.
- Admins can monitor bookings, users, and fare settings from the admin dashboard.

## Notes

- This is a demo-style project with simulated payment and ticket generation.
- It is designed for learning, practice, and basic metro booking workflow demonstration.
