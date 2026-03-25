<?php
// ============================================================
// products.php — Product CRUD Functions
// Marinduque Pasalubong Hub
// ============================================================

require_once 'db.php';

// ── GET ALL PRODUCTS ──────────────────────────────────────
function getAllProducts() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products ORDER BY category ASC, name ASC");
    return $stmt->fetchAll();
}

// ── GET SINGLE PRODUCT ────────────────────────────────────
function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => (int)$id]);
    return $stmt->fetch();
}

// ── GET PRODUCTS BY CATEGORY ──────────────────────────────
function getProductsByCategory($category) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = :cat ORDER BY name ASC");
    $stmt->execute([':cat' => $category]);
    return $stmt->fetchAll();
}

// ── CREATE PRODUCT ────────────────────────────────────────
function createProduct($data) {
    global $pdo;
    $sql = "INSERT INTO products (name, category, price, stock, image, description)
            VALUES (:name, :category, :price, :stock, :image, :description)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'        => trim($data['name']),
        ':category'    => trim($data['category']),
        ':price'       => (float)$data['price'],
        ':stock'       => (int)($data['stock'] ?? 0),
        ':image'       => trim($data['image'] ?? ''),
        ':description' => trim($data['description'] ?? ''),
    ]);
    return (int)$pdo->lastInsertId();
}

// ── UPDATE PRODUCT ────────────────────────────────────────
function updateProduct($id, $data) {
    global $pdo;
    $sql = "UPDATE products
            SET name = :name,
                category = :category,
                price = :price,
                stock = :stock,
                image = :image,
                description = :description,
                updated_at = NOW()
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id'          => (int)$id,
        ':name'        => trim($data['name']),
        ':category'    => trim($data['category']),
        ':price'       => (float)$data['price'],
        ':stock'       => (int)($data['stock'] ?? 0),
        ':image'       => trim($data['image'] ?? ''),
        ':description' => trim($data['description'] ?? ''),
    ]);
    return $stmt->rowCount();
}

// ── DELETE PRODUCT ────────────────────────────────────────
function deleteProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => (int)$id]);
    return $stmt->rowCount();
}

// ── GET PRODUCT STATS ─────────────────────────────────────
function getProductStats() {
    global $pdo;
    $stats = [];
    $stats['total']       = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['low_stock']   = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 10")->fetchColumn();
    $stats['out_of_stock']= (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0")->fetchColumn();

    // Top selling
    $stmt = $pdo->query("
        SELECT p.name, SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        GROUP BY oi.product_id, p.name
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $stats['top_selling'] = $stmt->fetchAll();

    return $stats;
}
