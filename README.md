# Anthony Fastfood FMS  
**Full Stack Staff Management System (PHP & MySQL)**

A full-stack staff management system developed as part of the **Diploma of Information Technology (Database Management)**.  
This project focuses on **database design, data integrity, and practical backend development**, rather than UI polish.

---

## 📌 Project Overview
Anthony Fastfood FMS is a database-driven web application designed to manage **staff records, roles, rosters, and staff availability** in a small business environment.

The goal of this project was to design and implement a **realistic relational database** and connect it to a working backend system using PHP and MySQL.

---

## 🧠 What This Project Demonstrates
- Relational database design and normalization
- Use of **primary keys, foreign keys, and constraints**
- Data integrity with **ON DELETE CASCADE**
- Role-based logic and structured backend development
- Practical PHP–MySQL integration
- Clear technical documentation

---

## 🖥️ Application Preview
Screenshots of the system are available in the `screenshots/` folder, including:
- Login screen
- Dashboard
- Staff management interface
- Availability selection
- Database schema (phpMyAdmin)

These screenshots provide a quick overview of the system without requiring setup.

---

## 🗄️ Database Design
The database was designed following relational database best practices.

### Core Tables
- `staff`
- `role`
- `roster`
- `rosterrole`
- `availability`

### Key Design Decisions
- **AUTO_INCREMENT primary keys**
- **Foreign keys with cascading rules** to prevent orphan records
- **Unique constraints**, including:
  - Unique staff email
  - Unique staff availability per roster
  - Unique role per roster
- Passwords stored as **hashed values**

The full database schema and sample data are included in:


## Author
Kenny Luis Colliard
