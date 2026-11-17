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

$userResult = mysqli_query($con, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($userResult);

if (!$user) {
    header("Location: login.php");
    exit();
}

$name = $user['name'];
$address = $user['address'];
$contact = $user['contact'];
$verified = $user['verified'];
$role = isset($role) ? $role : 'Customer';

$cartItems = [];
$total = 0;

foreach ($_POST as $key => $value) {
    if (is_numeric($key) && (int)$value > 0) {
        $itemId = (int)$key;
        $quantity = (int)$value;

        $itemResult = mysqli_query(
            $con,
            "SELECT id, name, price FROM items WHERE id = $itemId"
        );

        if ($itemResult && mysqli_num_rows($itemResult) > 0) {
            $item = mysqli_fetch_assoc($itemResult);
            $lineTotal = (float)$item['price'] * $quantity;

            $cartItems[] = [
                'id' => $itemId,
                'name' => $item['name'],
                'price' => (float)$item['price'],
                'quantity' => $quantity,
                'line_total' => $lineTotal
            ];

            $total += $lineTotal;
        }
    }
}

if (empty($cartItems) || $total <= 0) {
    header("Location: index.php");
    exit();
}

$description = isset($_POST['description']) ? $_POST['description'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | CampusEats</title>
    <link rel="stylesheet" href="css/custom/campus.css">

    <style>
        .checkout-page {
            max-width: 1180px;
            margin: 0 auto;
        }

        .checkout-heading {
            margin-bottom: 26px;
        }

        .checkout-heading h1 {
            margin: 0 0 8px;
            color: #111827;
            font-size: 30px;
            font-weight: 750;
        }

        .checkout-heading p {
            margin: 0;
            color: #6b7280;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, .85fr);
            gap: 24px;
            align-items: start;
        }

        .checkout-panel {
            min-width: 0;
            padding: 26px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .035);
        }

        .checkout-panel h2 {
            margin: 0 0 6px;
            color: #111827;
            font-size: 20px;
        }

        .checkout-subtitle {
            margin: 0 0 24px;
            color: #6b7280;
            font-size: 14px;
        }

        .checkout-field {
            margin-bottom: 20px;
        }

        .checkout-field label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
        }

        .checkout-field input,
        .checkout-field textarea,
        .checkout-field select {
            box-sizing: border-box;
            width: 100%;
            min-height: 46px;
            padding: 12px 13px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            background: #fff;
            color: #111827;
            font: inherit;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .checkout-field textarea {
            min-height: 100px;
            resize: vertical;
        }

        .checkout-field input:focus,
        .checkout-field textarea:focus,
        .checkout-field select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }

        .checkout-field input:disabled {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .checkout-hint {
            margin-top: 7px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.5;
        }

        .checkout-payment-note {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 22px;
            padding: 14px;
            border-radius: 10px;
            background: #eff6ff;
            color: #1e40af;
            font-size: 13px;
            line-height: 1.5;
        }

        .checkout-payment-note strong {
            display: block;
            margin-bottom: 2px;
        }

        .checkout-submit {
            width: 100%;
            min-height: 48px;
            padding: 13px 18px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: #fff;
            font: inherit;
            font-weight: 650;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        .checkout-submit:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .checkout-back {
            display: block;
            margin-top: 14px;
            color: #4b5563;
            text-align: center;
            font-size: 14px;
            text-decoration: none;
        }

        .checkout-back:hover {
            color: #2563eb;
        }

        .receipt-customer {
            margin: 18px 0;
            padding: 14px;
            border-radius: 10px;
            background: #f8fafc;
        }

        .receipt-customer span {
            display: block;
            margin-bottom: 4px;
            color: #6b7280;
            font-size: 12px;
        }

        .receipt-customer strong {
            display: block;
            overflow-wrap: anywhere;
            color: #111827;
            font-size: 14px;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .receipt-item-name {
            color: #111827;
            font-size: 14px;
            font-weight: 600;
        }

        .receipt-item-detail {
            margin-top: 5px;
            color: #6b7280;
            font-size: 12px;
        }

        .receipt-item-price {
            flex-shrink: 0;
            color: #111827;
            font-size: 14px;
            font-weight: 600;
        }

        .receipt-total {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
        }

        .receipt-total span:first-child {
            color: #374151;
            font-weight: 600;
        }

        .receipt-total strong {
            color: #111827;
            font-size: 23px;
        }

        .receipt-note {
            margin-top: 18px;
            padding: 13px;
            border-radius: 9px;
            background: #f8fafc;
            color: #4b5563;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .checkout-balance {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .checkout-balance span {
            color: #6b7280;
            font-size: 13px;
        }

        .checkout-balance strong {
            color: #111827;
        }

        @media (max-width: 850px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .checkout-panel {
                padding: 20px;
            }

            .checkout-heading h1 {
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
                Balance: <strong>₹<?php echo number_format((float)$balance, 2); ?></strong>
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
        <div class="checkout-page">
            <div class="checkout-heading">
                <h1>Checkout</h1>
                <p>Confirm your delivery details and payment method.</p>
            </div>

            <div class="checkout-grid">
                <section class="checkout-panel">
                    <h2>Delivery & Payment</h2>
                    <p class="checkout-subtitle">Enter the details needed to place your order.</p>

                    <form id="checkoutForm" method="post" action="confirm-order.php">
                        <div class="checkout-field">
                            <label for="payment_type">Payment method</label>
                            <select id="payment_type" name="payment_type" required>
    <option value="Wallet" selected>CampusEats Wallet</option>

    <option value="Razorpay">Pay Online (Razorpay)</option>

    <option value="Cash On Delivery" <?php if (!$verified) echo 'disabled'; ?>>
        Cash on Delivery<?php if (!$verified) echo ' (verification required)'; ?>
    </option>
</select>
                            <?php if (!$verified) { ?>
                                <div class="checkout-hint">
                                    Cash on Delivery is available only to verified accounts.
                                </div>
                            <?php } ?>
                        </div>

                        <div class="checkout-field">
                            <label for="address">Delivery address</label>
                            <textarea
                                id="address"
                                name="address"
                                minlength="5"
                                required
                            ><?php echo htmlspecialchars($address); ?></textarea>
                        </div>

                        <div class="checkout-payment-note">
                            <div>
                                <strong>Wallet payment details</strong>
                                Enter your registered wallet card number and CVV when paying with your wallet.
                            </div>
                        </div>

                        <div class="checkout-field">
                            <label for="cc_number">Wallet card number</label>
                            <input
                                name="cc_number"
                                id="cc_number"
                                type="text"
                                inputmode="numeric"
                                autocomplete="cc-number"
                                placeholder="1234-5678-9012-3456"
                                minlength="16"
                                maxlength="19"
                                required
                            >
                        </div>

                        <div class="checkout-field">
                            <label for="cvv_number">CVV number</label>
                            <input
                                name="cvv_number"
                                id="cvv_number"
                                type="password"
                                inputmode="numeric"
                                autocomplete="off"
                                placeholder="3-digit CVV"
                                minlength="3"
                                maxlength="5"
                                required
                            >
                        </div>

                        <?php
                        // Preserve cart quantities and other checkout data posted from index.php.
                        foreach ($_POST as $key => $value) {
                            if ($key === 'action' || $key === 'payment_type' ||
                                $key === 'address' || $key === 'cc_number' ||
                                $key === 'cvv_number') {
                                continue;
                            }

                            if (is_array($value)) {
                                continue;
                            }

                            echo '<input type="hidden" name="' .
                                htmlspecialchars($key, ENT_QUOTES, 'UTF-8') .
                                '" value="' .
                                htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') .
                                '">';
                        }
                        ?>

                        <button type="submit" class="checkout-submit">
                            Confirm Order · ₹<?php echo number_format($total, 2); ?>
                        </button>

                        <a href="index.php" class="checkout-back">← Back to food menu</a>
                    </form>
                </section>

                <aside class="checkout-panel">
                    <h2>Order Summary</h2>
                    <p class="checkout-subtitle">Review your items before confirming.</p>

                    <div class="receipt-customer">
                        <span>Ordering as</span>
                        <strong><?php echo htmlspecialchars($name); ?></strong>
                        <span style="margin-top: 12px;">Contact number</span>
                        <strong><?php echo htmlspecialchars($contact); ?></strong>
                    </div>

                    <?php foreach ($cartItems as $item) { ?>
                        <div class="receipt-item">
                            <div>
                                <div class="receipt-item-name">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </div>
                                <div class="receipt-item-detail">
                                    <?php echo (int)$item['quantity']; ?> ×
                                    ₹<?php echo number_format($item['price'], 2); ?>
                                </div>
                            </div>
                            <div class="receipt-item-price">
                                ₹<?php echo number_format($item['line_total'], 2); ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="receipt-total">
                        <span>Total payable</span>
                        <strong>₹<?php echo number_format($total, 2); ?></strong>
                    </div>

                    <?php if ($description !== '') { ?>
                        <div class="receipt-note">
                            <strong>Order note:</strong><br>
                            <?php echo htmlspecialchars($description); ?>
                        </div>
                    <?php } ?>

                    <div class="checkout-balance" style="margin-top: 22px;">
                        <span>Current wallet balance</span>
                        <strong>₹<?php echo number_format((float)$balance, 2); ?></strong>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>

<footer class="modern-footer">
    CampusEats — Campus Canteen Food Ordering System
</footer>

<script>
(function () {
    const paymentSelect = document.getElementById('payment_type');
    const cardInput = document.getElementById('cc_number');
    const cvvInput = document.getElementById('cvv_number');
    const form = document.getElementById('checkoutForm');

    function updatePaymentFields() {
        const walletSelected = paymentSelect.value === 'Wallet';

        cardInput.disabled = !walletSelected;
        cvvInput.disabled = !walletSelected;

        cardInput.required = walletSelected;
        cvvInput.required = walletSelected;
    }

    paymentSelect.addEventListener('change', updatePaymentFields);

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
        }
    });

    updatePaymentFields();
})();
</script>
</body>
</html>
```
