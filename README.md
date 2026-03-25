# API-IBS Backend

A RESTful backend service built with Laravel for managing application data, authentication, and business logic.
Designed with scalability, clean architecture, and secure API communication in mind.

## 🚀 Features

* RESTful API for managing core resources
* JWT-based authentication system
* Database interaction using Eloquent ORM
* Structured and maintainable backend architecture
* Secure API endpoints with token-based access control

## 🧱 Tech Stack

* Backend: Laravel (PHP)
* Database: MySQL
* ORM: Eloquent ORM
* Authentication: JWT (JSON Web Token)

## 🏗️ Architecture

* Laravel follows MVC pattern (Model-View-Controller)
* Eloquent ORM is used for database abstraction and relationships
* JWT is used for stateless authentication between client and server
* API routes are structured and versioned for scalability

## ⚙️ Installation

```bash
git clone https://github.com/HOCKIIE/api-ibs.git
cd api-ibs
composer install
cp .env.example .env
php artisan key:generate
```

## ▶️ Run

```bash
php artisan serve
```

## 🔐 Environment Variables

Update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=username
DB_PASSWORD=your_password

JWT_SECRET=your_jwt_secret
```

## 📌 API Overview

Example endpoints:

* `POST /api/login` – Authenticate user and return JWT
* `GET /api/user` – Get authenticated user data
* `GET /api/resources` – Fetch resource list

## 📊 Highlights

* Implemented secure authentication using JWT
* Clean separation of concerns using Laravel MVC
* Efficient database queries using Eloquent relationships

## 👨‍💻 Author

Suphawat Kongson
