<?php
// ============================================================
// customers.php — Customer CRUD Functions
// Marinduque Pasalubong Hub
// ============================================================

require_once 'db.php';

// ── GET ALL CUSTOMERS ─────────────────────────────────────
function getAllCustomers() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM customers ORDER BY last_order DESC, created_at DESC");
    return $stmt->fetchAll();
}

// ── GET SINGLE CUSTOMER ───────────────────────────────────
function getCustomerById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->execute([':id' => (int)$id]);
    $customer = $stmt->fetch();
    if ($customer) {
        // Attach order history
        $stmtO = $pdo->prepare("
            SELECT id, total, status, payment_method, created_at
            FROM orders
            WHERE customer_id = :cid
            ORDER BY created_at DESC
        ");
        $stmtO->execute([':cid' => $customer['id']]);
        $customer['orders'] = $stmtO->fetchAll();
    }
    return $customer;
}

// ── GET CUSTOMER BY EMAIL ─────────────────────────────────
function getCustomerByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email");
    $stmt->execute([':email' => trim($email)]);
    return $stmt->fetch();
}

// ── UPSERT CUSTOMER (used during order placement) ─────────
function upsertCustomer($data) {
    global $pdo;
    $existing = getCustomerByEmail($data['email']);
    if ($existing) {
        // Update contact info if changed
        $pdo->prepare("
            UPDATE customers
            SET name = :name, phone = :phone, address = :address
            WHERE id = :id
        ")->execute([
            ':name'    => trim($data['name']),
            ':phone'   => trim($data['phone']),
            ':address' => trim($data['address']),
            ':id'      => $existing['id'],
        ]);
        return $existing['id'];
    } else {
        $pdo->prepare("
            INSERT INTO customers (name, email, phone, address, orders_count, total_spent, last_order, created_at)
            VALUES (:name, :email, :phone, :address, 0, 0, NULL, NOW())
        ")->execute([
            ':name'    => trim($data['name']),
            ':email'   => trim($data['email']),
            ':phone'   => trim($data['phone']),
            ':address' => trim($data['address']),
        ]);
        return (int)$pdo->lastInsertId();
    }
}

// ── UPDATE CUSTOMER ───────────────────────────────────────
function updateCustomer($id, $data) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE customers
        SET name = :name, email = :email, phone = :phone, address = :address
        WHERE id = :id
    ");
    $stmt->execute([
        ':id'      => (int)$id,
        ':name'    => trim($data['name']),
        ':email'   => trim($data['email']),
        ':phone'   => trim($data['phone']),
        ':address' => trim($data['address']),
    ]);
    return $stmt->rowCount() > 0;
}

// ── DELETE CUSTOMER ───────────────────────────────────────
function deleteCustomer($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = :id");
    $stmt->execute([':id' => (int)$id]);
    return $stmt->rowCount();
}

// ── CUSTOMER STATS ────────────────────────────────────────
function getCustomerStats() {
    global $pdo;
    $stats = [];
    $stats['total']    = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $stats['new_this_month'] = (int)$pdo->query("
        SELECT COUNT(*) FROM customers
        WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())
    ")->fetchColumn();
    // Top spenders
    $stmt = $pdo->query("SELECT name, email, total_spent, orders_count FROM customers ORDER BY total_spent DESC LIMIT 5");
    $stats['top_spenders'] = $stmt->fetchAll();
    return $stats;
}
