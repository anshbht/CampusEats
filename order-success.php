<?php
include 'includes/connect.php';

if (!isset($_SESSION['customer_sid']) || $_SESSION['customer_sid'] != session_id()) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

if ($order_id <= 0 || $user_id <= 0) {
    header("Location: orders.php");
    exit();
}

$stmt = $con->prepare(
    "SELECT id FROM orders WHERE id = ? AND customer_id = ?"
);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: orders.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed | CampusEats</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
            background: #f8fafc;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        .success-card {
            width: 100%;
            max-width: 440px;
            padding: 35px 30px;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            background: #fff;
            text-align: center;
            box-shadow: 0 20px 65px rgba(15, 23, 42, 0.09);
            animation: cardEnter .65s cubic-bezier(.2, .8, .2, 1) both;
        }

        .animation-area {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 170px;
            height: 155px;
            margin: 0 auto 18px;
        }

        .food-circle {
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            border-radius: 32px;
            background: #eff6ff;
            box-shadow: 0 12px 30px rgba(37, 99, 235, .12);
            animation: foodPop .8s .15s both;
        }

        .food-emoji {
            font-size: 55px;
            line-height: 1;
        }

        .ring {
            position: absolute;
            border: 1px solid #bfdbfe;
            border-radius: 50%;
            animation: ringExpand 1s ease both;
        }

        .ring-one {
            width: 125px;
            height: 125px;
        }

        .ring-two {
            width: 155px;
            height: 155px;
            border-color: #dbeafe;
            animation-delay: .12s;
        }

        .particle {
            position: absolute;
            width: 9px;
            height: 9px;
            border-radius: 3px;
            opacity: 0;
            animation: particleFly .9s .2s ease-out both;
        }

        .p1 {
            top: 12px;
            left: 28px;
            background: #2563eb;
        }

        .p2 {
            top: 27px;
            right: 17px;
            background: #f59e0b;
            animation-delay: .3s;
        }

        .p3 {
            bottom: 12px;
            left: 22px;
            background: #16a34a;
            animation-delay: .4s;
        }

        .p4 {
            bottom: 25px;
            right: 18px;
            background: #f97316;
            animation-delay: .25s;
        }

        .status-label {
            margin-bottom: 11px;
            color: #16a34a;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2.5px;
            animation: textEnter .5s .4s both;
        }

        h1 {
            margin: 0 0 13px;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -1.4px;
            animation: textEnter .5s .5s both;
        }

        h1 span {
            color: #2563eb;
        }

        .message {
            max-width: 320px;
            margin: 0 auto 21px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
            animation: textEnter .5s .6s both;
        }

        .order-number {
            display: inline-block;
            margin-bottom: 23px;
            padding: 10px 15px;
            border: 1px dashed #bfdbfe;
            border-radius: 9px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
            animation: textEnter .5s .7s both;
        }

        .redirect-message {
            margin-top: 17px;
            color: #9ca3af;
            font-size: 12px;
            animation: textEnter .5s .8s both;
        }

        .redirect-track {
            width: 100%;
            height: 4px;
            margin-top: 12px;
            overflow: hidden;
            border-radius: 10px;
            background: #e5e7eb;
        }

        .redirect-progress {
            width: 100%;
            height: 100%;
            border-radius: inherit;
            background: #2563eb;
            transform-origin: left;
            animation: progressDrain 3.5s linear forwards;
        }

        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: translateY(25px) scale(.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes foodPop {
            0% {
                opacity: 0;
                transform: scale(.45) rotate(-12deg);
            }
            65% {
                opacity: 1;
                transform: scale(1.12) rotate(4deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0);
            }
        }

        @keyframes ringExpand {
            from {
                opacity: 0;
                transform: scale(.7);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes particleFly {
            0% {
                opacity: 0;
                transform: translateY(10px) scale(0);
            }
            55% {
                opacity: 1;
                transform: translateY(-7px) scale(1.2);
            }
            100% {
                opacity: .9;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes textEnter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes progressDrain {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }

        @media (max-width: 480px) {
            .success-card {
                padding: 28px 20px;
            }

            h1 {
                font-size: 31px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</head>

<body>
    <main class="success-card">
        <div class="animation-area">
            <div class="ring ring-one"></div>
            <div class="ring ring-two"></div>

            <div class="food-circle">
                <span class="food-emoji" aria-hidden="true">🍔</span>
            </div>

            <span class="particle p1"></span>
            <span class="particle p2"></span>
            <span class="particle p3"></span>
            <span class="particle p4"></span>
        </div>

        <div class="status-label">ORDER CONFIRMED</div>

        <h1>Order <span>Placed!</span></h1>

        <p class="message">
            Your order has been placed successfully.
            We’re getting your food ready.
        </p>

        <div class="order-number">
            Order #<?php echo $order_id; ?>
        </div>

        <div class="redirect-message">
            Taking you to your orders...
        </div>

        <div class="redirect-track">
            <div class="redirect-progress"></div>
        </div>
    </main>

    <script>
        window.setTimeout(function () {
            window.location.replace('orders.php');
        }, 3500);
    </script>
</body>
</html>
```