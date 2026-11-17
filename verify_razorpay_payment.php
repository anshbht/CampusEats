
<?php

include 'includes/connect.php';
include 'includes/wallet.php';
include 'razorpay_config.php';

header('Content-Type: application/json');

function sendResponse($success, $data = []) {
    echo json_encode(array_merge([
        'success' => $success
    ], $data));
    exit();
}

// Check login
if (
    !isset($_SESSION['customer_sid']) ||
    $_SESSION['customer_sid'] != session_id()
) {
    http_response_code(401);
    sendResponse(false, ['message' => 'Please log in again.']);
}

// Accept POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, ['message' => 'Invalid request method.']);
}

// Get Razorpay response
$payment_id = $_POST['razorpay_payment_id'] ?? '';
$order_id = $_POST['razorpay_order_id'] ?? '';
$signature = $_POST['razorpay_signature'] ?? '';

if ($payment_id === '' || $order_id === '' || $signature === '') {
    sendResponse(false, ['message' => 'Incomplete payment details.']);
}

// Check pending order in session
if (
    !isset($_SESSION['razorpay_pending'][$order_id]) ||
    $_SESSION['razorpay_pending'][$order_id]['user_id'] != $user_id
) {
    sendResponse(false, ['message' => 'Invalid or expired payment session.']);
}

$pending = $_SESSION['razorpay_pending'][$order_id];

// Verify Razorpay signature
$expected_signature = hash_hmac(
    'sha256',
    $order_id . '|' . $payment_id,
    RAZORPAY_KEY_SECRET
);

if (!hash_equals($expected_signature, $signature)) {
    sendResponse(false, ['message' => 'Payment signature verification failed.']);
}

// Fetch payment details from Razorpay
$ch = curl_init(
    'https://api.razorpay.com/v1/payments/' . rawurlencode($payment_id)
);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    error_log('Razorpay payment fetch error: ' . $error);
    sendResponse(false, ['message' => 'Unable to verify payment with Razorpay.']);
}

curl_close($ch);

$payment = json_decode($response, true);

if (
    $httpCode !== 200 ||
    !is_array($payment) ||
    ($payment['status'] ?? '') !== 'captured' ||
    ($payment['order_id'] ?? '') !== $order_id ||
    ($payment['currency'] ?? '') !== 'INR' ||
    (int)($payment['amount'] ?? 0) !== (int)$pending['amount']
) {
    sendResponse(false, [
        'message' => 'Payment has not been confirmed as captured by Razorpay.'
    ]);
}

// Recalculate total using current database prices
$total = 0;
$cartItems = $pending['cart_items'];

foreach ($cartItems as $cartItem) {

    $itemId = (int)$cartItem['id'];
    $quantity = (int)$cartItem['quantity'];

    $stmt = $con->prepare("SELECT price FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    $stmt->close();

    if (!$item || $quantity <= 0) {
        sendResponse(false, ['message' => 'An item in your cart is no longer available.']);
    }

    $total += (float)$item['price'] * $quantity;
}

// Check that the database total still matches the paid amount
if ((int)round($total * 100) !== (int)$pending['amount']) {
    sendResponse(false, [
        'message' => 'Cart amount has changed. Please contact the administrator.'
    ]);
}

// Create order and order details in a transaction
try {

    $con->begin_transaction();

    $payment_type = 'Razorpay';
    $payment_status = 'paid';
    $address = $pending['address'];
    $description = $pending['description'];
    $total = (int)round($total);

    // Save order
    $stmt = $con->prepare("
        INSERT INTO orders
        (
            customer_id,
            payment_type,
            address,
            total,
            description,
            payment_status,
            razorpay_order_id,
            razorpay_payment_id
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ississss",
        $user_id,
        $payment_type,
        $address,
        $total,
        $description,
        $payment_status,
        $order_id,
        $payment_id
    );

    $stmt->execute();
    $campus_order_id = $con->insert_id;
    $stmt->close();

    // Save individual order items
    foreach ($cartItems as $cartItem) {

        $itemId = (int)$cartItem['id'];
        $quantity = (int)$cartItem['quantity'];

        $stmt = $con->prepare("SELECT price FROM items WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();

        $result = $stmt->get_result();
        $item = $result->fetch_assoc();

        $stmt->close();

        if (!$item) {
            throw new Exception('An item could not be found.');
        }

        $price = (float)$item['price'] * $quantity;

        $stmt = $con->prepare("
            INSERT INTO order_details
            (order_id, item_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiid",
            $campus_order_id,
            $itemId,
            $quantity,
            $price
        );

        $stmt->execute();
        $stmt->close();
    }

    $con->commit();

    // Remove completed pending payment
    unset($_SESSION['razorpay_pending'][$order_id]);

    sendResponse(true, [
        'message' => 'Payment successful and order created.',
        'order_id' => $campus_order_id
    ]);

} catch (Throwable $e) {

    $con->rollback();

    error_log('CampusEats Razorpay order error: ' . $e->getMessage());

    sendResponse(false, [
        'message' => 'Payment was received, but the order could not be created. Please contact the administrator with your payment ID.'
    ]);
}

?>
