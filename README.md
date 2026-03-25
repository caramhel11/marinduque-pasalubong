# Marinduque Pasalubong Hub
## Web-Based Souvenir Shop with Sales Monitoring and Customer Management

**Marinduque State University — College of Information and Computing Sciences**
Final Group Project: Web-Based System with API Integration

---

## 1. Project Title
**Marinduque Pasalubong Hub: Web-Based Souvenir Shop with Sales Monitoring and Customer Management**

---

## 2. Group Members
| Name | Role |
|------|------|
| _(Member 1)_ | Frontend Developer |
| _(Member 2)_ | Backend Developer / API |
| _(Member 3)_ | Database Designer |
| _(Member 4)_ | Integrator |
| _(Member 5)_ | Documentation & Testing |

---

## 3. Project Description
Marinduque Pasalubong Hub is a web-based e-commerce product listing system for authentic Marinduque souvenir products. Customers can browse products by category, add them to a cart, and place orders. Administrators manage products, monitor sales, and view customer records — all in one connected system.

---

## 4. File Structure
```
marinduque-pasalubong-hub/
├── index.html       ← Customer-facing shop (Hero, Products, Cart, Checkout, About)
├── admin.html       ← Admin panel (Dashboard, Products, Sales, Customers)
├── api.php          ← REST API router — all endpoints return JSON
├── db.php           ← PDO database connection
├── products.php     ← Product CRUD functions (used by api.php)
├── orders.php       ← Order CRUD + sales summary functions
├── customers.php    ← Customer CRUD functions
├── database.sql     ← MySQL schema + seed data (run first)
├── images/          ← Product images folder
└── README.md        ← This file
```

---

## 5. Functionalities

### Customer Side (index.html)
- Hero section with beach background and "View Products" CTA
- Browse all 16 products, filterable by category
- Sliding cart drawer with add/remove/quantity controls
- Checkout modal: collects name, email, phone, address, payment method
- Orders saved to database (via API) or localStorage fallback
- Customers auto-registered on first purchase

### Admin Side (admin.html)
- **Login**: admin / admin123
- **Dashboard**: Revenue, order count, pending orders, customer count; recent orders table; top products chart
- **Product Management**: Full CRUD — add, edit, delete products with image, stock, price, category
- **Sales Monitoring**: All orders with status filter; update status per order; view full order details; revenue-by-category and payment-method charts
- **Customer Management**: Auto-populated from orders; view each customer's contact info, purchase history, total spent

### API (api.php)
All endpoints return JSON `{ success: bool, message: string, data... }`:

| Method | Action | Description |
|--------|--------|-------------|
| GET | `get_products` | All products (optional `?category=`) |
| GET | `get_product` | Single product `?id=` |
| POST | `add_product` | Create product |
| POST | `update_product` | Update product |
| POST | `delete_product` | Delete product |
| GET | `get_orders` | All orders (optional `?status=`) |
| GET | `get_order` | Single order `?id=` |
| POST | `create_order` | Place new order |
| POST | `update_order_status` | Change order status |
| GET | `get_customers` | All customers |
| GET | `get_customer` | Single customer `?id=` |
| GET | `sales_summary` | Full sales analytics |

---

## 6. Technologies Used
- **Frontend**: HTML5, Bootstrap 5.3, JavaScript ES6 (Fetch API)
- **Backend**: PHP 8 (PDO, prepared statements)
- **Database**: MySQL (InnoDB, relational with foreign keys)
- **Fonts**: Google Fonts (Playfair Display, Dancing Script, Nunito)
- **Icons**: Font Awesome 6
- **Version Control**: GitHub

---

## 7. Setup Instructions

### Requirements
- XAMPP (Apache + MySQL + PHP 8)

### Steps
1. Clone repo into `C:/xampp/htdocs/marinduque-pasalubong-hub/`
2. Start Apache and MySQL in XAMPP Control Panel
3. Open **phpMyAdmin** → Import → select `database.sql`
4. Add product images to the `images/` folder
5. Open `http://localhost/marinduque-pasalubong-hub/index.html` (shop)
6. Open `http://localhost/marinduque-pasalubong-hub/admin.html` (admin)
   - Login: `admin` / `admin123`

> **Without MySQL**: The system works fully in the browser using localStorage — great for demo/presentation.

---

## 8. Rubric Coverage (100 pts)

| Criteria | How it's met |
|----------|--------------|
| Functionality / CRUD (25) | Full CRUD on products; create + read on orders & customers in admin |
| Database Integration / PDO (15) | `db.php` + PDO prepared statements in `products.php`, `orders.php`, `customers.php` |
| API Implementation (15) | `api.php` — 12 endpoints, all return JSON |
| JavaScript Fetch (10) | GET on page load (products); POST on order placement; admin uses Fetch for all CRUD |
| UI / Bootstrap (10) | Fully responsive Bootstrap 5.3, custom CSS, mobile-ready |
| GitHub / Version Control (10) | Repository with commits from all members, branches used |
| Documentation (10) | This README + inline code comments |
| Presentation (5) | Live/video demo of CRUD, API calls, GitHub |

---

*Gawang Marinduque, Gawang May Pagmamahal.*
