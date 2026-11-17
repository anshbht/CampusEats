```php
<?php
include 'includes/connect.php';
include 'includes/wallet.php';

if (!isset($_SESSION['customer_sid']) || $_SESSION['customer_sid'] != session_id()) {
    if (isset($_SESSION['admin_sid']) && $_SESSION['admin_sid'] == session_id()) {
        header("Location: admin-page.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$payment_type = isset($_POST['payment_type']) ? trim($_POST['payment_type']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if ($address === '' || !in_array($payment_type, ['Wallet', 'Cash On Delivery', 'Razorpay'], true)) {
    header("Location: index.php");
    exit();
}

$userResult = mysqli_query($con, "SELECT * FROM users WHERE id = $user_id");
$user = $userResult ? mysqli_fetch_assoc($userResult) : null;

if (!$user) {
    header("Location: login.php");
    exit();
}

$name = $user['name'];
$contact = $user['contact'];
$verified = $user['verified'];
$role = isset($role) ? $role : 'Customer';

/* Validate payment method and wallet credentials. */
if ($payment_type === 'Cash On Delivery' && !$verified) {
    header("Location: index.php");
    exit();
}

if ($payment_type === 'Wallet') {
    $cc_number = preg_replace('/[\s-]/', '', $_POST['cc_number'] ?? '');
    $cvv_number = preg_replace('/[\s-]/', '', $_POST['cvv_number'] ?? '');

    $walletResult = mysqli_query(
        $con,
        "SELECT number, cvv FROM wallet_details WHERE wallet_id = $wallet_id"
    );

    $walletCard = $walletResult ? mysqli_fetch_assoc($walletResult) : null;

    if (
        !$walletCard ||
        !ctype_digit($cc_number) ||
        !ctype_digit($cvv_number) ||
        (string)$walletCard['number'] !== $cc_number ||
        (string)$walletCard['cvv'] !== $cvv_number
    ) {
        header("Location: index.php");
        exit();
    }
}

/* Build the receipt from the posted cart quantities. */
$cartItems = [];
$total = 0;

foreach ($_POST as $key => $value) {
    if (!ctype_digit((string)$key) || (int)$key <= 0) {
        continue;
    }

    if (!is_scalar($value) || !ctype_digit((string)$value) || (int)$value <= 0) {
        continue;
    }

    $itemId = (int)$key;
    $quantity = (int)$value;

    $itemResult = mysqli_query(
        $con,
        "SELECT id, name, price FROM items WHERE id = $itemId"
    );

    if (!$itemResult || mysqli_num_rows($itemResult) === 0) {
        continue;
    }

    $item = mysqli_fetch_assoc($itemResult);
    $price = (float)$item['price'];
    $lineTotal = $price * $quantity;

    $cartItems[] = [
        'id' => $itemId,
        'name' => $item['name'],
        'price' => $price,
        'quantity' => $quantity,
        'line_total' => $lineTotal
    ];

    $total += $lineTotal;
}

if (empty($cartItems) || $total <= 0) {
    header("Location: index.php");
    exit();
}

$insufficientBalance = (
    $payment_type === 'Wallet' &&
    (float)$balance < $total
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Order | CampusEats</title>
    <link rel="stylesheet" href="css/custom/campus.css">

    <style>
        .confirm-page {
            max-width: 1180px;
            margin: 0 auto;
        }

        .confirm-heading {
            margin-bottom: 26px;
        }

        .confirm-heading h1 {
            margin: 0 0 8px;
            color: #111827;
            font-size: 30px;
            font-weight: 750;
        }

        .confirm-heading p {
            margin: 0;
            color: #6b7280;
        }

        .confirm-steps {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 22px 0 26px;
            color: #6b7280;
            font-size: 13px;
            flex-wrap: wrap;
        }

        .confirm-step {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .confirm-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 27px;
            height: 27px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #4b5563;
            font-weight: 700;
        }

        .confirm-step.completed .confirm-step-number {
            background: #dcfce7;
            color: #15803d;
        }

        .confirm-step.active {
            color: #2563eb;
            font-weight: 700;
        }

        .confirm-step.active .confirm-step-number {
            background: #2563eb;
            color: #fff;
        }

        .confirm-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, .85fr);
            gap: 24px;
            align-items: start;
        }

        .confirm-panel {
            min-width: 0;
            padding: 26px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .035);
        }

        .confirm-panel h2 {
            margin: 0 0 6px;
            color: #111827;
            font-size: 20px;
        }

        .confirm-subtitle {
            margin: 0 0 22px;
            color: #6b7280;
            font-size: 14px;
        }

        .confirm-info-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .confirm-info-row:last-child {
            border-bottom: 0;
        }

        .confirm-info-label {
            flex-shrink: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .confirm-info-value {
            color: #111827;
            font-size: 14px;
            font-weight: 600;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .confirm-payment-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 7px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
        }

        .confirm-item {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .confirm-item-name {
            color: #111827;
            font-size: 14px;
            font-weight: 600;
        }

        .confirm-item-detail {
            margin-top: 5px;
            color: #6b7280;
            font-size: 12px;
        }

        .confirm-item-price {
            flex-shrink: 0;
            color: #111827;
            font-size: 14px;
            font-weight: 600;
        }

        .confirm-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
        }

        .confirm-total span {
            color: #374151;
            font-weight: 600;
        }

        .confirm-total strong {
            color: #111827;
            font-size: 24px;
        }

        .confirm-note {
            margin-top: 20px;
            padding: 14px;
            border-radius: 10px;
            background: #f8fafc;
            color: #4b5563;
            font-size: 13px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .confirm-balance-box {
            margin-top: 22px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 11px;
        }

        .confirm-balance-row {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 7px 0;
            color: #4b5563;
            font-size: 13px;
        }

        .confirm-balance-row strong {
            color: #111827;
        }

        .confirm-balance-row.remaining {
            margin-top: 7px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
        }

        .confirm-balance-row.remaining strong {
            color: <?php echo $insufficientBalance ? '#dc2626' : '#15803d'; ?>;
        }

        .confirm-warning {
            margin: 20px 0;
            padding: 14px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.5;
        }

        .confirm-submit {
            width: 100%;
            min-height: 49px;
            padding: 13px 18px;
            border: 0;
            border-radius: 10px;
            background: #2563eb;
            color: #fff;
            font: inherit;
            font-weight: 650;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        .confirm-submit:hover:not(:disabled) {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .confirm-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .confirm-back {
            display: block;
            margin-top: 14px;
            color: #4b5563;
            text-align: center;
            font-size: 14px;
            text-decoration: none;
        }

        .confirm-back:hover {
            color: #2563eb;
        }

        .confirm-secure-note {
            margin-top: 18px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.6;
            text-align: center;
        }

        @media (max-width: 850px) {
            .confirm-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .confirm-panel {
                padding: 20px;
            }

            .confirm-heading h1 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>
<header id="header">
    <div class="modern-header">
        <a href="index.php" class="brand">
            Campus<span>Eats</span>
        </a>

        <div class="header-right">
            <div class="balance">
                Balance:
                <strong>₹<?php echo number_format((float)$balance, 2); ?></strong>
            </div>

            <div class="profile">
                <?php echo htmlspecialchars($name); ?>
            </div>
        </div>
    </div>
</header>

<div class="modern-layout">
    <aside class="modern-sidebar">
        <div class="user-box">
            <div class="user-name"><?php echo htmlspecialchars($name); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($role); ?></div>
        </div>

        <div class="nav-title">Menu</div>
        <ul class="modern-nav">
            <li><a href="index.php">Food Menu</a></li>
            <li><a href="orders.php">My Orders</a></li>
            <li><a href="tickets.php">Support</a></li>
            <li><a href="details.php">Profile</a></li>
        </ul>

        <div class="nav-title" style="margin-top: 30px;">Account</div>
        <ul class="modern-nav">
            <li><a href="routers/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="modern-content">
        <div class="confirm-page">
            <div class="confirm-heading">
                <h1>Review Your Order</h1>
                <p>Check your order details before placing it.</p>
            </div>

            <div class="confirm-steps">
                <div class="confirm-step completed">
                    <span class="confirm-step-number">1</span>
                    Checkout
                </div>
                <span>—</span>
                <div class="confirm-step active">
                    <span class="confirm-step-number">2</span>
                    Review
                </div>
                <span>—</span>
                <div class="confirm-step">
                    <span class="confirm-step-number">3</span>
                    Confirmation
                </div>
            </div>

            <div class="confirm-grid">
                <section class="confirm-panel">
                    <h2>Delivery Details</h2>
                    <p class="confirm-subtitle">Where and how your order will be delivered.</p>

                    <div class="confirm-info-row">
                        <span class="confirm-info-label">Customer</span>
                        <span class="confirm-info-value">
                            <?php echo htmlspecialchars($name); ?>
                        </span>
                    </div>

                    <div class="confirm-info-row">
                        <span class="confirm-info-label">Contact</span>
                        <span class="confirm-info-value">
                            <?php echo htmlspecialchars($contact); ?>
                        </span>
                    </div>

                    <div class="confirm-info-row">
                        <span class="confirm-info-label">Delivery address</span>
                        <span class="confirm-info-value">
                            <?php echo nl2br(htmlspecialchars($address)); ?>
                        </span>
                    </div>

                    <div class="confirm-info-row">
                        <span class="confirm-info-label">Payment method</span>
                        <span class="confirm-info-value">
                            <span class="confirm-payment-badge">
                                <?php echo htmlspecialchars($payment_type); ?>
                            </span>
                        </span>
                    </div>

                    <?php if ($description !== '') { ?>
                        <div class="confirm-note">
                            <strong>Order note</strong><br>
                            <?php echo nl2br(htmlspecialchars($description)); ?>
                        </div>
                    <?php } ?>

                    <?php if ($payment_type === 'Wallet') { ?>
                        <div class="confirm-balance-box">
                            <div class="confirm-balance-row">
                                <span>Current wallet balance</span>
                                <strong>₹<?php echo number_format((float)$balance, 2); ?></strong>
                            </div>

                            <div class="confirm-balance-row">
                                <span>Order total</span>
                                <strong>− ₹<?php echo number_format($total, 2); ?></strong>
                            </div>

                            <div class="confirm-balance-row remaining">
                                <span>Balance after purchase</span>
                                <strong>
                                    ₹<?php echo number_format((float)$balance - $total, 2); ?>
                                </strong>
                            </div>
                        </div>
                    <?php } ?>
                </section>

                <aside class="confirm-panel">
                    <h2>Order Summary</h2>
                    <p class="confirm-subtitle">Your selected food items.</p>

                    <?php foreach ($cartItems as $item) { ?>
                        <div class="confirm-item">
                            <div>
                                <div class="confirm-item-name">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </div>

                                <div class="confirm-item-detail">
                                    <?php echo (int)$item['quantity']; ?> ×
                                    ₹<?php echo number_format($item['price'], 2); ?>
                                </div>
                            </div>

                            <div class="confirm-item-price">
                                ₹<?php echo number_format($item['line_total'], 2); ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="confirm-total">
                        <span>Total payable</span>
                        <strong>₹<?php echo number_format($total, 2); ?></strong>
                    </div>

                    <?php if ($insufficientBalance) { ?>
                        <div class="confirm-warning">
                            <strong>Insufficient wallet balance.</strong><br>
                            Add funds to your wallet or return to checkout and choose another available payment method.
                        </div>
                    <?php } ?>

                    <form action="routers/order-router.php" method="post" id="confirmOrderForm">
                        <?php foreach ($cartItems as $item) { ?>
                            <input
                                type="hidden"
                                name="<?php echo (int)$item['id']; ?>"
                                value="<?php echo (int)$item['quantity']; ?>"
                            >
                        <?php } ?>

                        <input
                            type="hidden"
                            name="payment_type"
                            value="<?php echo htmlspecialchars($payment_type, ENT_QUOTES, 'UTF-8'); ?>"
                        >

                        <input
                            type="hidden"
                            name="address"
                            value="<?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?>"
                        >

                        <input
                            type="hidden"
                            name="total"
                            value="<?php echo htmlspecialchars((string)$total, ENT_QUOTES, 'UTF-8'); ?>"
                        >

                        <?php if ($description !== '') { ?>
                            <input
                                type="hidden"
                                name="description"
                                value="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        <?php } ?>

                        <button
                            type="submit"
                            class="confirm-submit"
                            <?php echo $insufficientBalance ? 'disabled' : ''; ?>
                        >
                            <?php echo $insufficientBalance ? 'Insufficient Balance' : 'Confirm & Place Order'; ?>
                        </button>
                    </form>

                    <a href="index.php" class="confirm-back">
                        ← Return to Food Menu
                    </a>

                    <div class="confirm-secure-note">
                        Please review your address and items carefully before confirming.
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>

<footer class="modern-footer">
    CampusEats — Campus Canteen Food Ordering System
</footer>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>

document.getElementById('confirmOrderForm').addEventListener('submit', async function (event) {

    const form = this;
    const button = form.querySelector('button[type="submit"]');
    const paymentType = form.querySelector('[name="payment_type"]').value;

    if (button.disabled) {
        event.preventDefault();
        return;
    }

    // Existing Wallet and COD flow
    if (paymentType !== 'Razorpay') {
        button.disabled = true;
        button.textContent = 'Placing Order...';
        return;
    }

    // Razorpay flow
    event.preventDefault();

    button.disabled = true;
    button.textContent = 'Connecting to Razorpay...';

    try {

        // Create Razorpay order on the server
        const formData = new FormData(form);

        const createResponse = await fetch('create_razorpay_order.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const orderData = await createResponse.json();

        if (!createResponse.ok || !orderData.success) {
            throw new Error(orderData.message || 'Could not create payment order.');
        }

        // Open Razorpay Checkout
        const options = {
            key: orderData.key_id,
            amount: orderData.amount,
            currency: orderData.currency,
            name: 'CampusEats',
            description: 'CampusEats Food Order',
            order_id: orderData.order_id,
            
            prefill: {
                name: <?php echo json_encode($name); ?>,
                contact: <?php echo json_encode($contact); ?>
            },

            theme: {
                color: '#2563eb'
            },

            handler: async function (response) {

                button.textContent = 'Verifying Payment...';

                try {

                    const verifyData = new FormData();

                    verifyData.append('razorpay_payment_id', response.razorpay_payment_id);
                    verifyData.append('razorpay_order_id', response.razorpay_order_id);
                    verifyData.append('razorpay_signature', response.razorpay_signature);

                    const verifyResponse = await fetch('verify_razorpay_payment.php', {
                        method: 'POST',
                        body: verifyData,
                        credentials: 'same-origin'
                    });

                    const result = await verifyResponse.json();

                    if (!verifyResponse.ok || !result.success) {
                        throw new Error(result.message || 'Payment verification failed.');
                    }

                    window.location.href = 'order-success.php?order_id=' +
                        encodeURIComponent(result.order_id);

                } catch (error) {

                    alert(error.message);
                    button.disabled = false;
                    button.textContent = 'Confirm & Place Order';

                }
            },

            modal: {
                ondismiss: function () {
                    button.disabled = false;
                    button.textContent = 'Confirm & Place Order';
                }
            }
        };

        const razorpay = new Razorpay(options);

        razorpay.on('payment.failed', function (response) {
            alert(
                response.error.description ||
                'Payment failed. Please try again.'
            );

            button.disabled = false;
            button.textContent = 'Confirm & Place Order';
        });

        razorpay.open();

    } catch (error) {

        alert(error.message);

        button.disabled = false;
        button.textContent = 'Confirm & Place Order';

    }
});

</script>
</body>
</html>
```
