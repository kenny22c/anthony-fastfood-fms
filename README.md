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

database/fastfood_klc.sql

---

## 📁 Project Structure

anthony-fastfood-fms/

├── src/ # PHP application source code

├── database/ # SQL schema and sample data

├── screenshots/ # Application and database screenshots

└── README.md

---

## ▶️ Running the Project Locally (Optional)
This project was developed and tested locally using **XAMPP**.

If you would like to run the system locally:

1. Install **XAMPP**
2. Copy the project into:
xampp/htdocs/anthony-fastfood-fms

3. Start **Apache** and **MySQL**
4. Create a database named:
fastfood_klc

5. Import:
database/fastfood_klc.sql

6. Open in your browser:
http://localhost/anthony-fastfood-fms/src/login.php


This step is **not required** to understand the project but is provided for technical completeness.

---

## 🧪 Technologies Used
- PHP
- MySQL
- SQL
- HTML / CSS
- XAMPP (local development environment)

---

## 👤 Author
**Kenny Colliard**  
Junior Data Analyst | IT & Data Systems  

This project is part of my professional portfolio and focuses on **clear design decisions, realistic data modeling, and practical backend development**.

---

## ⚠️ Disclaimer
This project was developed for educational and portfolio purposes.  
All users and data included are **sample/dummy data**.
