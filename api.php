<?php
// ============================================================
// api.php — REST-like JSON API
// Marinduque Pasalubong Hub
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'products.php';
require_once 'orders.php';
require_once 'customers.php';

// ── Parse request ─────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = [];

if ($method === 'POST') {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
    // Also allow POST action from body
    if (empty($action) && isset($input['action'])) {
        $action = $input['action'];
    }
}

// ── Route ─────────────────────────────────────────────────
try {
    switch ($action) {

        // ── PRODUCTS ──────────────────────────────────────
        case 'get_products':
            $cat = $_GET['category'] ?? '';
            $products = $cat ? getProductsByCategory($cat) : getAllProducts();
            respond(true, 'OK', ['products' => $products]);
            break;

        case 'get_product':
            $id = $_GET['id'] ?? ($input['id'] ?? 0);
            $product = getProductById($id);
            if (!$product) respond(false, 'Product not found');
            respond(true, 'OK', ['product' => $product]);
            break;

        case 'add_product':
            validate($input, ['name','category','price']);
            $id = createProduct($input);
            respond(true, 'Product added', ['id' => $id]);
            break;

        case 'update_product':
            validate($input, ['id','name','category','price']);
            $rows = updateProduct($input['id'], $input);
            respond(true, 'Product updated', ['rows' => $rows]);
            break;

        case 'delete_product':
            $id = $input['id'] ?? $_GET['id'] ?? 0;
            if (!$id) respond(false, 'ID required');
            $rows = deleteProduct($id);
            respond(true, 'Product deleted', ['rows' => $rows]);
            break;

        case 'get_product_stats':
            respond(true, 'OK', ['stats' => getProductStats()]);
            break;

        // ── ORDERS ────────────────────────────────────────
        case 'get_orders':
            $status = $_GET['status'] ?? '';
            $orders = $status ? getOrdersByStatus($status) : getAllOrders();
            respond(true, 'OK', ['orders' => $orders]);
            break;

        case 'get_order':
            $id = $_GET['id'] ?? ($input['id'] ?? 0);
            $order = getOrderById($id);
            if (!$order) respond(false, 'Order not found');
            respond(true, 'OK', ['order' => $order]);
            break;

        case 'create_order':
            validate($input, ['customer','items','payment_method']);
            validate($input['customer'], ['name','email','phone','address']);
            if (empty($input['items'])) respond(false, 'Cart is empty');
            $orderId = createOrder($input['customer'], $input['items'], $input['payment_method']);
            respond(true, 'Order placed!', ['order_id' => $orderId]);
            break;

        case 'update_order_status':
            validate($input, ['id','status']);
            $ok = updateOrderStatus($input['id'], $input['status']);
            respond($ok, $ok ? 'Status updated' : 'Failed to update status');
            break;

        case 'delete_order':
            $id = $input['id'] ?? $_GET['id'] ?? 0;
            if (!$id) respond(false, 'ID required');
            $rows = deleteOrder($id);
            respond(true, 'Order deleted', ['rows' => $rows]);
            break;

        case 'sales_summary':
            respond(true, 'OK', ['summary' => getSalesSummary()]);
            break;

        // ── CUSTOMERS ─────────────────────────────────────
        case 'get_customers':
            respond(true, 'OK', ['customers' => getAllCustomers()]);
            break;

        case 'get_customer':
            $id = $_GET['id'] ?? ($input['id'] ?? 0);
            $customer = getCustomerById($id);
            if (!$customer) respond(false, 'Customer not found');
            respond(true, 'OK', ['customer' => $customer]);
            break;

        case 'update_customer':
            validate($input, ['id','name','email']);
            $ok = updateCustomer($input['id'], $input);
            respond($ok, $ok ? 'Customer updated' : 'Failed');
            break;

        case 'delete_customer':
            $id = $input['id'] ?? $_GET['id'] ?? 0;
            if (!$id) respond(false, 'ID required');
            $rows = deleteCustomer($id);
            respond(true, 'Customer deleted', ['rows' => $rows]);
            break;

        case 'get_customer_stats':
            respond(true, 'OK', ['stats' => getCustomerStats()]);
            break;

        default:
            respond(false, 'Unknown action. Use: get_products, add_product, update_product, delete_product, get_orders, create_order, update_order_status, get_customers, sales_summary');
    }
} catch (PDOException $e) {
    respond(false, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}

// ── Helpers ───────────────────────────────────────────────
function respond($success, $message, $data = []) {
    echo json_encode(array_merge(
        ['success' => (bool)$success, 'message' => $message],
        $data
    ), JSON_UNESCAPED_UNICODE);
    exit();
}

function validate($data, $fields) {
    foreach ($fields as $f) {
        if (!isset($data[$f]) || (is_string($data[$f]) && trim($data[$f]) === '')) {
            respond(false, "Missing required field: $f");
        }
    }
}
