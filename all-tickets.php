<?php
include 'includes/connect.php';

if ($_SESSION['admin_sid'] == session_id()) {

    $status_filter = isset($_GET['status']) ? $_GET['status'] : '%';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Support Tickets | CampusEats</title>

    <link rel="stylesheet" href="css/custom/campus.css">
</head>

<body class="admin-page">

<header class="admin-header">

    <div class="admin-brand">
        <div class="admin-logo">C</div>

        <div>
            <h1>CampusEats</h1>
            <span>Admin Panel</span>
        </div>
    </div>

    <div class="admin-header-right">

        <span class="admin-user">
            <?php echo htmlspecialchars($name); ?>
        </span>

        <a href="routers/logout.php" class="admin-logout">
            Logout
        </a>

    </div>

</header>


<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">

        <div class="admin-profile">

            <div class="admin-avatar">
                <?php echo strtoupper(substr($name, 0, 1)); ?>
            </div>

            <div>
                <strong>
                    <?php echo htmlspecialchars($name); ?>
                </strong>

                <span>
                    <?php echo htmlspecialchars($role); ?>
                </span>
            </div>

        </div>


        <nav class="admin-nav">

            <a href="admin-page.php">
                Food Menu
            </a>


            <div class="admin-nav-group">

                <div class="admin-nav-title">
                    Orders
                </div>

                <a href="all-orders.php">
                    All Orders
                </a>

                <?php

                $order_status_sql = mysqli_query(
                    $con,
                    "SELECT DISTINCT status FROM orders;"
                );

                while ($order_status = mysqli_fetch_array($order_status_sql)) {

                ?>

                    <a href="all-orders.php?status=<?php echo urlencode($order_status['status']); ?>">
                        <?php echo htmlspecialchars($order_status['status']); ?>
                    </a>

                <?php
                }
                ?>

            </div>


            <div class="admin-nav-group">

                <div class="admin-nav-title active">
                    Support Tickets
                </div>

                <a
                    href="all-tickets.php"
                    class="<?php echo !isset($_GET['status']) ? 'active' : ''; ?>"
                >
                    All Tickets
                </a>

                <?php

                $ticket_status_sql = mysqli_query(
                    $con,
                    "SELECT DISTINCT status FROM tickets;"
                );

                while ($ticket_status = mysqli_fetch_array($ticket_status_sql)) {

                    $current_status = $ticket_status['status'];

                ?>

                    <a
                        href="all-tickets.php?status=<?php echo urlencode($current_status); ?>"
                        class="<?php echo (
                            isset($_GET['status']) &&
                            $_GET['status'] == $current_status
                        ) ? 'active' : ''; ?>"
                    >
                        <?php echo htmlspecialchars($current_status); ?>
                    </a>

                <?php
                }
                ?>

            </div>


            <a href="users.php">
                Users
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="admin-content">

        <div class="admin-page-heading">

            <div>
                <h2>Support Tickets</h2>

                <p>
                    Manage customer support requests and issues.
                </p>
            </div>

        </div>


        <!-- FILTERS -->
        <div class="order-filter-bar">

            <a
                href="all-tickets.php"
                class="order-filter <?php echo !isset($_GET['status']) ? 'active' : ''; ?>"
            >
                All
            </a>


            <?php

            $filter_sql = mysqli_query(
                $con,
                "SELECT DISTINCT status FROM tickets;"
            );

            while ($filter_row = mysqli_fetch_array($filter_sql)) {

            ?>

                <a
                    href="all-tickets.php?status=<?php echo urlencode($filter_row['status']); ?>"
                    class="order-filter <?php echo (
                        isset($_GET['status']) &&
                        $_GET['status'] == $filter_row['status']
                    ) ? 'active' : ''; ?>"
                >
                    <?php echo htmlspecialchars($filter_row['status']); ?>
                </a>

            <?php
            }
            ?>

        </div>


        <!-- TICKETS -->
        <div class="admin-tickets">

        <?php

        $sql = mysqli_query(
            $con,
            "SELECT * FROM tickets WHERE status LIKE '$status_filter';"
        );

        $ticket_count = mysqli_num_rows($sql);

        if ($ticket_count == 0) {

        ?>

            <div class="empty-admin-orders">

                <div class="empty-icon">
                    —
                </div>

                <h3>
                    No tickets found
                </h3>

                <p>
                    There are no support tickets matching the selected status.
                </p>

            </div>

        <?php

        }

        while ($row = mysqli_fetch_array($sql)) {

            $ticket_status = $row['status'];
            $ticket_type = $row['type'];
            $ticket_subject = $row['subject'];
            $ticket_date = $row['date'];

        ?>

            <a
                href="view-ticket-admin.php?id=<?php echo $row['id']; ?>"
                class="admin-ticket-card"
            >

                <div class="admin-ticket-main">

                    <div class="admin-ticket-icon">
                        ?
                    </div>


                    <div class="admin-ticket-info">

                        <div class="admin-ticket-title">
                            <?php echo htmlspecialchars($ticket_subject); ?>
                        </div>

                        <div class="admin-ticket-date">

                            Ticket #<?php echo $row['id']; ?>

                            <span>•</span>

                            <?php echo htmlspecialchars($ticket_date); ?>

                        </div>

                    </div>

                </div>


                <div class="admin-ticket-meta">

                    <span class="ticket-type-badge">
                        <?php echo htmlspecialchars($ticket_type); ?>
                    </span>


                    <span class="ticket-status-badge
                        <?php
                        if (strtolower($ticket_status) == 'open') {
                            echo 'ticket-open';
                        }
                        elseif (strtolower($ticket_status) == 'answered') {
                            echo 'ticket-answered';
                        }
                        else {
                            echo 'ticket-other';
                        }
                        ?>
                    ">
                        <?php echo htmlspecialchars($ticket_status); ?>
                    </span>

                    <span class="ticket-arrow">
                        →
                    </span>

                </div>

            </a>

        <?php
        }
        ?>

        </div>

    </main>

</div>


<footer class="admin-footer">
    CampusEats — Campus Canteen Food Ordering System
</footer>


</body>
</html>


<?php

}
else {

    if ($_SESSION['customer_sid'] == session_id()) {

        header("location:tickets.php");

    }
    else {

        header("location:login.php");

    }

}

?>