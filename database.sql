-- ================================================================
-- Marinduque Pasalubong Hub — Database Setup
-- Run this in phpMyAdmin → SQL tab, OR via:
--   mysql -u root -p < database.sql
-- ================================================================

CREATE DATABASE IF NOT EXISTS marinduque_pasalubong
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE marinduque_pasalubong;

-- ────────────────────────────────────────────────────────────────
-- TABLE: products
-- ────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS products;

CREATE TABLE products (
  id          INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(255)    NOT NULL,
  category    VARCHAR(50)     NOT NULL,
  price       DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  stock       INT             NOT NULL DEFAULT 0,
  image       VARCHAR(500)    NOT NULL DEFAULT '',
  description TEXT,
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category (category)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────
-- TABLE: customers
-- ────────────────────────────────────────────────────────────────
CREATE TABLE customers (
  id           INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(255)    NOT NULL,
  email        VARCHAR(255)    NOT NULL UNIQUE,
  phone        VARCHAR(30)     NOT NULL DEFAULT '',
  address      TEXT,
  orders_count INT UNSIGNED    NOT NULL DEFAULT 0,
  total_spent  DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  last_order   DATETIME                 DEFAULT NULL,
  created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────
-- TABLE: orders
-- ────────────────────────────────────────────────────────────────
CREATE TABLE orders (
  id             INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
  customer_id    INT UNSIGNED    NOT NULL,
  total          DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  payment_method VARCHAR(50)     NOT NULL DEFAULT 'cod',
  status         ENUM('Pending','Processing','Completed','Cancelled')
                                 NOT NULL DEFAULT 'Pending',
  created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  INDEX idx_status (status),
  INDEX idx_customer (customer_id)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────
-- TABLE: order_items
-- ────────────────────────────────────────────────────────────────
CREATE TABLE order_items (
  id           INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_id     INT UNSIGNED    NOT NULL,
  product_id   INT UNSIGNED             DEFAULT NULL,
  product_name VARCHAR(255)    NOT NULL,
  quantity     INT UNSIGNED    NOT NULL DEFAULT 1,
  price        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────
-- SEED: Products
-- ────────────────────────────────────────────────────────────────
INSERT INTO products (name, category, price, stock, image, description) VALUES
  ("Rejano's Premium Arrowroot Cookie",      'cookies', 480.00, 50,  'images/arrowroot-premium.jpg',       'Premium quality uraro cookies from Marinduque.'),
  ("Rejano's Arrowroot Cookie With Pinipig", 'cookies', 320.00, 60,  'images/arrowroot-pinipig.jpg',       'Arrowroot cookies topped with crispy pinipig.'),
  ("Rejano's Arrowroot Cookie Original",     'cookies', 300.00, 80,  'images/arrowroot-original.jpg',      'Classic original flavor uraro cookies.'),
  ("Rejano's Polvoron De Uraro",             'cookies', 250.00, 70,  'images/polvoron.jpg',                'Soft and crumbly polvoron made from uraro flour.'),
  ('Buri Bag',                               'buri',    350.00, 30,  'images/buri-bag.jpg',                'Handwoven buri palm bag, locally made in Marinduque.'),
  ('Buri Hat',                               'buri',    100.00, 45,  'images/buri-hat.jpg',                'Woven buri palm hat, perfect for the beach.'),
  ('Buri Basket',                            'buri',    100.00, 40,  'images/buri-basket.jpg',             'Small handwoven buri basket, multi-purpose.'),
  ('Buri Mats',                              'buri',    150.00, 35,  'images/buri-mats.jpg',               'Traditional buri palm woven mats.'),
  ('Morion Keychain',                        'crafts',   50.00, 100, 'images/morion-keychain.jpg',         'Miniature Morion helmet keychain souvenir.'),
  ('Morion Fridge Magnet',                   'crafts',   30.00, 120, 'images/morion-magnet.jpg',           'Colorful Morion-themed refrigerator magnet.'),
  ('Morion Figures',                         'crafts',  120.00, 60,  'images/morion-figures.jpg',          'Handcrafted Morion warrior figurine.'),
  ('Morion Mask',                            'crafts',  200.00, 25,  'images/morion-mask.jpg',             'Authentic replica Morion mask, festival souvenir.'),
  ('I Love Marinduque T-Shirt',              'tshirts', 200.00, 55,  'images/tshirt-ilovemarinduque.jpg',  'Soft cotton I Love Marinduque shirt.'),
  ('Property of Marinduque T-Shirt',         'tshirts', 200.00, 55,  'images/tshirt-property.jpg',         '"Property of Marinduque" printed cotton shirt.'),
  ('Marinduque Island T-Shirt',              'tshirts', 200.00, 55,  'images/tshirt-island.jpg',           'Island map graphic Marinduque shirt.'),
  ('Moriones Festival T-Shirt',              'tshirts', 200.00, 55,  'images/tshirt-moriones.jpg',         'Moriones Festival commemorative shirt.');
