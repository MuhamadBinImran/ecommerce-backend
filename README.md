<!-- Stickers / Badges -->
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-brightgreen)](https://laravel.com/)
[![PHP Version](https://img.shields.io/badge/PHP-8.3-blue)](https://www.php.net/)
[![Deployment](https://img.shields.io/badge/Deployed-AWS-FF9900?logo=amazon-aws)](http://13.60.188.147/)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![GitHub stars](https://img.shields.io/github/stars/MuhamadBinImran/ecommerce-backend?style=social)](https://github.com/MuhamadBinImran/ecommerce-backend/stargazers)

# 🛒 ecommerce-backend

> A robust and modular backend for a **Laravel-powered e-commerce platform**, built with scalability, security, and flexibility in mind.  
> ✅ **Completed Project** · 🚀 **Live on AWS**

---

## ✨ Key Features

- 📦 **Product & Category Management** — Full CRUD support  
- 👤 **User Authentication & Roles** — Admin & Customer roles  
- 🔒 **Secure Access Control** — Middleware-based protection  
- 🛍 **Cart & Checkout APIs** — Manage shopping carts & orders  
- 💳 **Payment-Ready** — Extensible structure for payment gateways  
- 📊 **Order Management** — Track, update, and manage orders  
- 🔗 **RESTful API Design** — Optimized for frontend integration  

---

## 🌍 Live Deployment

The project is deployed on **AWS EC2** and accessible here:  
👉 [http://13.60.188.147/](http://13.60.188.147/)

---

## ⚙️ Prerequisites

Make sure you have the following installed locally:

- [PHP 8.3+](https://www.php.net/)  
- [Composer](https://getcomposer.org/)  
- [Node.js & NPM](https://nodejs.org/) (for frontend assets if required)  
- A database system: **MySQL** (recommended), PostgreSQL, or SQLite  

---

## 🚀 Installation & Setup

Follow these steps to run the project locally:

```bash
# 1. Clone the repository
git clone https://github.com/MuhamadBinImran/ecommerce-backend.git
cd ecommerce-backend

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Configure .env file
# Update DB credentials, APP_URL, etc.

# 5. Generate application key
php artisan key:generate

# 6. Run database migrations and seeders
php artisan migrate --seed

# 7. Install Node dependencies (if applicable)
npm install && npm run build

# 8. Start the development server
php artisan serve
````

The backend will now be running at:
👉 `http://127.0.0.1:8000`

---

## 📡 API Endpoints (Examples)

| Method | Endpoint          | Description                     |
| ------ | ----------------- | ------------------------------- |
| POST   | `/api/auth/login` | Authenticate & return JWT token |
| GET    | `/api/products`   | Fetch all products              |
| POST   | `/api/cart`       | Add product to cart             |
| GET    | `/api/orders`     | Get all customer orders         |

*(Check codebase for complete API documentation.)*
---

## 🛠 Tech Stack

* **Framework:** Laravel 12
* **Language:** PHP 8.3
* **Database:** MySQL
* **Deployment:** AWS EC2
* **Package Manager:** Composer / NPM

---

## 🤝 Contributing

This is a **completed project**, but contributions are welcome.

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/new-feature`)
3. Commit changes (`git commit -m "Add new feature"`)
4. Push to branch (`git push origin feature/new-feature`)
5. Create a Pull Request 🎉

---

## 📬 Contact

💻 GitHub: [MuhamadBinImran](https://github.com/MuhamadBinImran)
🌐 Deployment: [AWS Link](http://13.60.188.147/)

---

## 📄 License

Distributed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

🚀 **Done & Deployed — Happy Coding!** 🖤

```
