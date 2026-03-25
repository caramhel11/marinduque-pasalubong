<?php
// ============================================================
// orders.php — Order CRUD Functions
// Marinduque Pasalubong Hub
// ============================================================

require_once 'db.php';
require_once 'customers.php';

// ── GET ALL ORDERS ────────────────────────────────────────
function getAllOrders() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT o.*, c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
    foreach ($orders as &$order) {
        $order['items'] = getOrderItems($order['id']);
    }
    return $orders;
}

// ── GET ORDERS BY STATUS ──────────────────────────────────
function getOrdersByStatus($status) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT o.*, c.name AS customer_name, c.email AS customer_email
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.status = :status
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([':status' => $status]);
    $orders = $stmt->fetchAll();
    foreach ($orders as &$order) {
        $order['items'] = getOrderItems($order['id']);
    }
    return $orders;
}

// ── GET SINGLE ORDER ──────────────────────────────────────
function getOrderById($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT o.*, c.name AS customer_name, c.email AS customer_email,
               c.phone AS customer_phone, c.address AS customer_address
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.id = :id
    ");
    $stmt->execute([':id' => (int)$id]);
    $order = $stmt->fetch();
    if ($order) {
        $order['items'] = getOrderItems($id);
    }
    return $order;
}

// ── GET ORDER ITEMS ───────────────────────────────────────
function getOrderItems($orderId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT oi.*, p.image
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute([':order_id' => (int)$orderId]);
    return $stmt->fetchAll();
}

// ── CREATE ORDER ──────────────────────────────────────────
function createOrder($customerData, $items, $paymentMethod) {
    global $pdo;

    $pdo->beginTransaction();
    try {
        // Upsert customer
        $customerId = upsertCustomer($customerData);

        // Calculate total
        $total = 0;
        foreach ($items as $item) {
            $total += (float)$item['price'] * (int)$item['qty'];
        }

        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (customer_id, total, payment_method, status, created_at)
            VALUES (:cid, :total, :pay, 'Pending', NOW())
        ");
        $stmt->execute([
            ':cid'   => $customerId,
            ':total' => $total,
            ':pay'   => $paymentMethod,
        ]);
        $orderId = (int)$pdo->lastInsertId();

        // Insert order items
        $stmtItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, quantity, price)
            VALUES (:oid, :pid, :pname, :qty, :price)
        ");
        foreach ($items as $item) {
            $stmtItem->execute([
                ':oid'   => $orderId,
                ':pid'   => (int)$item['id'],
                ':pname' => $item['name'],
                ':qty'   => (int)$item['qty'],
                ':price' => (float)$item['price'],
            ]);
        }

        // Update customer totals
        $pdo->prepare("
            UPDATE customers
            SET orders_count = orders_count + 1,
                total_spent  = total_spent + :total,
                last_order   = NOW()
            WHERE id = :id
        ")->execute([':total' => $total, ':id' => $customerId]);

        $pdo->commit();
        return $orderId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// ── UPDATE ORDER STATUS ───────────────────────────────────
function updateOrderStatus($id, $status) {
    global $pdo;
    $allowed = ['Pending', 'Processing', 'Completed', 'Cancelled'];
    if (!in_array($status, $allowed)) return false;
    $stmt = $pdo->prepare("UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => (int)$id]);
    return $stmt->rowCount() > 0;
}

// ── DELETE ORDER ──────────────────────────────────────────
function deleteOrder($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = :id");
    $stmt->execute([':id' => (int)$id]);
    return $stmt->rowCount();
}

// ── SALES SUMMARY ─────────────────────────────────────────
function getSalesSummary() {
    global $pdo;
    $s = [];

    $s['total_revenue']    = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='Completed'")->fetchColumn();
    $s['total_orders']     = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $s['pending_orders']   = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn();
    $s['completed_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='Completed'")->fetchColumn();
    $s['cancelled_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='Cancelled'")->fetchColumn();
    $s['total_customers']  = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $s['items_sold']       = (int)$pdo->query("SELECT COALESCE(SUM(quantity),0) FROM order_items")->fetchColumn();
    $s['avg_order']        = $s['total_orders'] > 0
        ? (float)$pdo->query("SELECT AVG(total) FROM orders")->fetchColumn()
        : 0;

    // Revenue by category
    $stmt = $pdo->query("
        SELECT p.category, SUM(oi.price * oi.quantity) AS revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        GROUP BY p.category
    ");
    $s['revenue_by_category'] = $stmt->fetchAll();

    // Payment breakdown
    $stmt = $pdo->query("SELECT payment_method, COUNT(*) AS count FROM orders GROUP BY payment_method");
    $s['payment_breakdown'] = $stmt->fetchAll();

    // Top products
    $stmt = $pdo->query("
        SELECT product_name, SUM(quantity) AS total_qty, SUM(price * quantity) AS revenue
        FROM order_items
        GROUP BY product_name
        ORDER BY total_qty DESC
        LIMIT 6
    ");
    $s['top_products'] = $stmt->fetchAll();

    // Monthly revenue (last 6 months)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b %Y') AS month, SUM(total) AS revenue
        FROM orders
        WHERE status = 'Completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY MIN(created_at) ASC
    ");
    $s['monthly_revenue'] = $stmt->fetchAll();

    return $s;
}
