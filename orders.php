<?php

include 'includes/connect.php';
include 'includes/wallet.php';

if ($_SESSION['customer_sid'] == session_id()) {

    if (isset($_GET['status'])) {
        $filterStatus = $_GET['status'];
    } else {
        $filterStatus = '%';
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders | CampusEats</title>

    <link rel="stylesheet" href="css/custom/campus.css">

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
                <strong>₹<?php echo $balance; ?></strong>
            </div>

            <div class="profile">
                <?php echo $name; ?>
            </div>

        </div>

    </div>

</header>


<div class="modern-layout">


    <!-- SIDEBAR -->

    <aside class="modern-sidebar">

        <div class="user-box">

            <div class="user-name">
                <?php echo $name; ?>
            </div>

            <div class="user-role">
                <?php echo $role; ?>
            </div>

        </div>


        <div class="nav-title">
            Menu
        </div>

        <ul class="modern-nav">

            <li>
                <a href="index.php">
                    Food Menu
                </a>
            </li>

            <li>
                <a href="orders.php" class="active">
                    My Orders
                </a>
            </li>

            <li>
                <a href="tickets.php">
                    Support
                </a>
            </li>

            <li>
                <a href="details.php">
                    Profile
                </a>
            </li>

        </ul>


        <div class="nav-title" style="margin-top: 30px;">
            Account
        </div>

        <ul class="modern-nav">

            <li>
                <a href="routers/logout.php">
                    Logout
                </a>
            </li>

        </ul>

    </aside>


    <!-- CONTENT -->

    <main class="modern-content">

        <div class="orders-page">


            <div class="orders-header">

                <h1>My Orders</h1>

                <p>
                    View and manage your previous food orders.
                </p>

            </div>


            <!-- FILTERS -->

            <div class="order-filter">

                <a
                    href="orders.php"
                    class="<?php echo !isset($_GET['status']) ? 'active' : ''; ?>"
                >
                    All
                </a>


                <?php

                $statusQuery = mysqli_query(
                    $con,
                    "SELECT DISTINCT status
                     FROM orders
                     WHERE customer_id = $user_id;"
                );

                while ($statusRow = mysqli_fetch_array($statusQuery)) {

                    $currentStatus = $statusRow['status'];

                ?>

                    <a
                        href="orders.php?status=<?php echo urlencode($currentStatus); ?>"
                        class="<?php echo (isset($_GET['status']) && $_GET['status'] == $currentStatus) ? 'active' : ''; ?>"
                    >
                        <?php echo htmlspecialchars($currentStatus); ?>
                    </a>

                <?php

                }

                ?>

            </div>


            <!-- ORDERS -->

            <?php

            $sql = mysqli_query(
    $con,
    "SELECT * FROM orders
    WHERE customer_id = $user_id
    AND status LIKE '$filterStatus'
    ORDER BY id DESC;"
);

            $hasOrders = false;

            while ($row = mysqli_fetch_array($sql)) {

                $hasOrders = true;

                $status = $row['status'];

                $statusClass = '';

                if ($status == 'Delivered') {
                    $statusClass = 'delivered';
                }
                elseif ($status == 'Paused') {
                    $statusClass = 'paused';
                }
                elseif (preg_match('/^Cancelled/', $status)) {
                    $statusClass = 'cancelled';
                }

            ?>

                <div class="order-card">


                    <!-- ORDER HEADER -->

                    <div class="order-card-header">

                        <div>

                            <div class="order-number">
                                Order #<?php echo $row['id']; ?>
                            </div>

                            <div class="order-date">
                                <?php echo htmlspecialchars($row['date']); ?>
                            </div>

                        </div>


                        <div class="order-status <?php echo $statusClass; ?>">

                            <?php echo htmlspecialchars($status); ?>

                        </div>

                    </div>


                    <!-- ORDER INFORMATION -->

                    <div class="order-info">

                        <div class="order-info-item">

                            <span>
                                Payment
                            </span>

                            <strong>
                                <?php echo htmlspecialchars($row['payment_type']); ?>
                            </strong>

                        </div>


                        <div class="order-info-item">

                            <span>
                                Address
                            </span>

                            <strong>
                                <?php echo htmlspecialchars($row['address']); ?>
                            </strong>

                        </div>

                    </div>


                    <!-- ITEMS -->

                    <div class="order-items">

                        <?php

                        $order_id = $row['id'];

                        $sql1 = mysqli_query(
                            $con,
                            "SELECT * FROM order_details
                             WHERE order_id = $order_id;"
                        );

                        while ($row1 = mysqli_fetch_array($sql1)) {

                            $item_id = $row1['item_id'];

                            $sql2 = mysqli_query(
                                $con,
                                "SELECT * FROM items
                                 WHERE id = $item_id;"
                            );

                            $item_name = '';

                            while ($row2 = mysqli_fetch_array($sql2)) {

                                $item_name = $row2['name'];

                            }

                        ?>

                            <div class="order-item">

                                <div>

                                    <div class="order-item-name">

                                        <span class="order-item-id">
                                            #<?php echo $item_id; ?>
                                        </span>

                                        <?php echo htmlspecialchars($item_name); ?>

                                    </div>

                                    <div class="order-item-details">
                                        <?php echo $row1['quantity']; ?> pieces
                                    </div>

                                </div>


                                <div class="order-item-price">

                                    ₹<?php echo $row1['price']; ?>

                                </div>

                            </div>

                        <?php

                        }

                        ?>

                    </div>


                    <!-- NOTE -->

                    <?php if (!empty($row['description'])) { ?>

                        <div class="order-note">

                            <strong>
                                Note:
                            </strong>

                            <?php echo htmlspecialchars($row['description']); ?>

                        </div>

                    <?php } ?>


                    <!-- FOOTER -->

                    <div class="order-card-footer">

                        <div>

                            <span class="order-total-label">
                                Total
                            </span>

                            <span class="order-total">
                                ₹<?php echo $row['total']; ?>
                            </span>

                        </div>


                        <?php

                        if (!preg_match('/^Cancelled/', $status)) {

                            if ($status != 'Delivered') {

                        ?>

                                <form
                                    action="routers/cancel-order.php"
                                    method="post"
                                >

                                    <input
                                        type="hidden"
                                        value="<?php echo $row['id']; ?>"
                                        name="id"
                                    >

                                    <input
                                        type="hidden"
                                        value="Cancelled by Customer"
                                        name="status"
                                    >

                                    <input
                                        type="hidden"
                                        value="<?php echo $row['payment_type']; ?>"
                                        name="payment_type"
                                    >

                                    <button
                                        class="cancel-order-button"
                                        type="submit"
                                        name="action"
                                    >
                                        Cancel Order
                                    </button>

                                </form>

                        <?php

                            }

                        }

                        ?>

                    </div>


                </div>

            <?php

            }


            if (!$hasOrders) {

            ?>

                <div class="no-orders">

                    No orders found.

                </div>

            <?php

            }

            ?>


        </div>

    </main>

</div>


<footer class="modern-footer">

    CampusEats — Campus Canteen Food Ordering System

</footer>

```php
<?php
$orderPlaced = (
    isset($_GET['success']) &&
    $_GET['success'] === '1' &&
    isset($_GET['order_id']) &&
    ctype_digit((string)$_GET['order_id'])
);

if ($orderPlaced) {
    $successOrderId = (int)$_GET['order_id'];

    // Verify that this order belongs to the logged-in customer.
    $verifyOrder = mysqli_query(
        $con,
        "SELECT id FROM orders
         WHERE id = $successOrderId
         AND customer_id = $user_id"
    );

    $orderPlaced = $verifyOrder && mysqli_num_rows($verifyOrder) > 0;
}
?>

<?php if ($orderPlaced) { ?>
<div class="order-success-overlay" id="orderSuccessOverlay">
    <div class="order-success-modal" role="dialog"
         aria-modal="true" aria-labelledby="successTitle">

        <div class="success-animation">
            <div class="success-orbit orbit-one"></div>
            <div class="success-orbit orbit-two"></div>

            <div class="success-food-icon">
                <span class="food-emoji">🍔</span>
            </div>

            <span class="success-particle particle-one"></span>
            <span class="success-particle particle-two"></span>
            <span class="success-particle particle-three"></span>
            <span class="success-particle particle-four"></span>
            <span class="success-particle particle-five"></span>
            <span class="success-particle particle-six"></span>
        </div>

        <div class="success-label">ORDER CONFIRMED</div>

        <h2 id="successTitle">
            Order <span>Placed!</span>
        </h2>

        <p>
            Your order has been placed successfully.
            Sit back while we get your food ready.
        </p>

        <div class="success-order-number">
            Order #<?php echo $successOrderId; ?>
        </div>

        <a href="orders.php" class="order-success-button">
            Track My Order <span aria-hidden="true">→</span>
        </a>
    </div>
</div>

<script>
(function () {
    const overlay = document.getElementById('orderSuccessOverlay');

    // Remove the query string to prevent the animation from replaying
    // when the customer refreshes the page.
    if (window.history.replaceState) {
        window.history.replaceState(
            {},
            document.title,
            window.location.pathname
        );
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && overlay) {
            overlay.remove();
        }
    });
})();
</script>
<?php } ?>
```
</body>

</html>


<?php

}

else {

    if ($_SESSION['admin_sid'] == session_id()) {

        header("location:all-orders.php");

    }

    else {

        header("location:login.php");

    }

}

?>