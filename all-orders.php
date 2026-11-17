<?php

include 'includes/connect.php';

if ($_SESSION['admin_sid'] == session_id()) {

    $filter = isset($_GET['status']) ? $_GET['status'] : 'all';

    if ($filter == 'all') {
        $sql = mysqli_query($con, "SELECT * FROM orders ORDER BY id DESC");
    } else {
        $safe_filter = mysqli_real_escape_string($con, $filter);
        $sql = mysqli_query($con, "SELECT * FROM orders WHERE status = '$safe_filter' ORDER BY id DESC");
    }

    $orders = [];

    while ($row = mysqli_fetch_assoc($sql)) {

        $customer_id = (int)$row['customer_id'];

        $customer_query = mysqli_query(
            $con,
            "SELECT name, contact, email FROM users WHERE id = $customer_id"
        );

        $customer = mysqli_fetch_assoc($customer_query);

        $items_query = mysqli_query(
            $con,
            "SELECT od.*, i.name AS item_name
             FROM order_details od
             LEFT JOIN items i ON od.item_id = i.id
             WHERE od.order_id = " . (int)$row['id']
        );

        $items = [];

        while ($item = mysqli_fetch_assoc($items_query)) {
            $items[] = $item;
        }

        $row['customer_name'] = $customer['name'] ?? 'Unknown';
        $row['customer_contact'] = $customer['contact'] ?? '';
        $row['customer_email'] = $customer['email'] ?? '';
        $row['items'] = $items;

        $orders[] = $row;
    }

    /* Get available statuses for filter buttons */
    $status_query = mysqli_query(
        $con,
        "SELECT DISTINCT status FROM orders WHERE status IS NOT NULL AND status != '' ORDER BY status"
    );

    $statuses = [];

    while ($status_row = mysqli_fetch_assoc($status_query)) {
        $statuses[] = $status_row['status'];
    }

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>All Orders | CampusEats</title>

    <link rel="stylesheet" href="css/custom/campus.css">

</head>

<body>

<header class="modern-header">

    <div class="brand">
        Campus<span>Eats</span>
    </div>

    <div class="header-right">

        <div class="admin-label">
            Admin Panel
        </div>

        <a href="routers/logout.php" class="logout-link">
            Logout
        </a>

    </div>

</header>


<div class="modern-layout">

    <aside class="modern-sidebar">

        <div class="sidebar-title">
            Administration
        </div>

        <nav class="modern-nav">

            <a href="admin-page.php">
                Food Menu
            </a>

            <a href="all-orders.php" class="active">
                Orders
            </a>

            <a href="users.php">
                Users
            </a>

            <a href="all-tickets.php">
                Support Tickets
            </a>

        </nav>

    </aside>


    <main class="modern-content admin-page">

        <div class="page-heading">

            <div>
                <h1>All Orders</h1>
                <p>Manage customer orders and update their status.</p>
            </div>

            <div class="order-count">
                <?php echo count($orders); ?> orders
            </div>

        </div>


        <div class="admin-filter-bar">

            <a
                href="all-orders.php"
                class="filter-button <?php echo ($filter == 'all') ? 'active' : ''; ?>"
            >
                All
            </a>

            <?php foreach ($statuses as $status): ?>

                <a
                    href="all-orders.php?status=<?php echo urlencode($status); ?>"
                    class="filter-button <?php echo ($filter == $status) ? 'active' : ''; ?>"
                >
                    <?php echo htmlspecialchars($status); ?>
                </a>

            <?php endforeach; ?>

        </div>


        <?php if (count($orders) == 0): ?>

            <div class="empty-state">

                <div class="empty-icon">□</div>

                <h2>No orders found</h2>

                <p>
                    There are no orders matching this filter.
                </p>

            </div>

        <?php else: ?>


            <div class="admin-orders">

                <?php foreach ($orders as $order): ?>

                    <?php

                    $status = $order['status'];

                    $status_class = 'status-default';

                    if ($status == 'Yet to be delivered') {
                        $status_class = 'status-pending';
                    } elseif ($status == 'Delivered') {
                        $status_class = 'status-delivered';
                    } elseif ($status == 'Cancelled by Admin' || $status == 'Cancelled by Customer') {
                        $status_class = 'status-cancelled';
                    } elseif ($status == 'Paused') {
                        $status_class = 'status-paused';
                    }

                    ?>

                    <article class="admin-order-card">

                        <div class="admin-order-top">

                            <div>

                                <span class="order-number">
                                    Order #<?php echo $order['id']; ?>
                                </span>

                                <span class="order-date">
                                    <?php echo htmlspecialchars($order['date']); ?>
                                </span>

                            </div>

                            <span class="order-status <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>

                        </div>


                        <div class="admin-order-grid">


                            <section class="order-customer">

                                <h3>Customer</h3>

                                <p class="customer-name">
                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                </p>

                                <?php if ($order['customer_contact'] != ''): ?>

                                    <p>
                                        <?php echo htmlspecialchars($order['customer_contact']); ?>
                                    </p>

                                <?php endif; ?>


                                <?php if ($order['customer_email'] != ''): ?>

                                    <p>
                                        <?php echo htmlspecialchars($order['customer_email']); ?>
                                    </p>

                                <?php endif; ?>

                            </section>


                            <section class="order-delivery">

                                <h3>Delivery</h3>

                                <p>
                                    <?php echo htmlspecialchars($order['address']); ?>
                                </p>

                                <p>
                                    <strong>Payment:</strong>
                                    <?php echo htmlspecialchars($order['payment_type']); ?>
                                </p>

                            </section>


                            <section class="order-items">

                                <h3>Items</h3>

                                <?php foreach ($order['items'] as $item): ?>

                                    <div class="admin-order-item">

                                        <span>
                                            <?php echo htmlspecialchars($item['item_name']); ?>
                                        </span>

                                        <span>
                                            <?php echo (int)$item['quantity']; ?> ×
                                            ₹<?php echo htmlspecialchars($item['price']); ?>
                                        </span>

                                    </div>

                                <?php endforeach; ?>

                            </section>


                        </div>


                        <?php if (!empty($order['description'])): ?>

                            <div class="admin-order-note">

                                <strong>Customer note:</strong>

                                <?php echo htmlspecialchars($order['description']); ?>

                            </div>

                        <?php endif; ?>


                        <div class="admin-order-bottom">

                            <div class="order-total">

                                Total

                                <strong>
                                    ₹<?php echo htmlspecialchars($order['total']); ?>
                                </strong>

                            </div>


                            <?php if (!$order['deleted']): ?>

                                <form
                                    method="post"
                                    action="routers/edit-orders.php"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo $order['id']; ?>"
                                    >

                                    <select name="status">

                                        <option
                                            value="Yet to be delivered"
                                            <?php echo ($status == 'Yet to be delivered') ? 'selected' : ''; ?>
                                        >
                                            Yet to be delivered
                                        </option>

                                        <option
                                            value="Delivered"
                                            <?php echo ($status == 'Delivered') ? 'selected' : ''; ?>
                                        >
                                            Delivered
                                        </option>

                                        <option
                                            value="Cancelled by Admin"
                                            <?php echo ($status == 'Cancelled by Admin') ? 'selected' : ''; ?>
                                        >
                                            Cancelled by Admin
                                        </option>

                                        <option
                                            value="Paused"
                                            <?php echo ($status == 'Paused') ? 'selected' : ''; ?>
                                        >
                                            Paused
                                        </option>

                                    </select>

                                    <button type="submit" class="admin-button">
                                        Update Status
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>

</div>


<footer class="modern-footer">
    CampusEats — Campus Canteen Food Ordering System
</footer>

</body>

</html>

<?php

} else {

    if ($_SESSION['customer_sid'] == session_id()) {
        header("location:index.php");
    } else {
        header("location:login.php");
    }

}

?>