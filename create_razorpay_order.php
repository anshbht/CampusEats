
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

// Get delivery details
$address = trim($_POST['address'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($address === '' || strlen($address) > 300) {
    sendResponse(false, ['message' => 'Invalid delivery address.']);
}

// Calculate total using database prices
$cartItems = [];
$total = 0;

foreach ($_POST as $key => $value) {

    if (!ctype_digit((string)$key) || (int)$key <= 0) {
        continue;
    }

    if (!is_scalar($value) || !ctype_digit((string)$value)) {
        continue;
    }

    $itemId = (int)$key;
    $quantity = (int)$value;

    if ($quantity <= 0 || $quantity > 100) {
        continue;
    }

    $stmt = $con->prepare(
        "SELECT id, price FROM items WHERE id = ?"
    );

    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    $stmt->close();

    if (!$item) {
        continue;
    }

    $price = (float)$item['price'];

    if ($price < 0) {
        sendResponse(false, ['message' => 'Invalid item price.']);
    }

    $cartItems[] = [
        'id' => $itemId,
        'quantity' => $quantity
    ];

    $total += $price * $quantity;
}

if (empty($cartItems) || $total <= 0) {
    sendResponse(false, ['message' => 'Your cart is empty.']);
}

// Convert rupees to paise
$amount = (int)round($total * 100);

// Generate receipt
$receipt = 'CE-' . $user_id . '-' . bin2hex(random_bytes(5));

// Create Razorpay order
$payload = [
    'amount' => $amount,
    'currency' => 'INR',
    'receipt' => $receipt,
    'notes' => [
        'customer_id' => (string)$user_id,
        'system' => 'CampusEats'
    ]
];

$ch = curl_init('https://api.razorpay.com/v1/orders');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);

    error_log('Razorpay cURL error: ' . $error);

    sendResponse(false, [
        'message' => 'Unable to connect to the payment gateway.'
    ]);
}

curl_close($ch);

$result = json_decode($response, true);

if ($httpCode !== 200 || empty($result['id'])) {

    error_log('Razorpay API error: ' . $response);

    sendResponse(false, [
        'message' => 'Unable to create payment order.'
    ]);
}

// Save pending order information in the session.
// This will be used after Razorpay payment verification.

$_SESSION['razorpay_pending'] = $_SESSION['razorpay_pending'] ?? [];

$_SESSION['razorpay_pending'][$result['id']] = [
    'user_id' => $user_id,
    'total' => $total,
    'amount' => $amount,
    'address' => $address,
    'description' => $description,
    'cart_items' => $cartItems
];

// Return information to checkout JavaScript
sendResponse(true, [
    'order_id' => $result['id'],
    'amount' => $amount,
    'currency' => 'INR',
    'key_id' => RAZORPAY_KEY_ID
]);

?>
