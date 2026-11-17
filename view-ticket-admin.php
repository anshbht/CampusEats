<?php
include 'includes/connect.php';
include 'includes/wallet.php';

$continue = 0;

if ($_SESSION['admin_sid'] == session_id()) {

    $ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $sql1 = "SELECT * FROM tickets WHERE id = $ticket_id;";

    if (mysqli_num_rows(mysqli_query($con, $sql1)) > 0) {

        $row = $con->query($sql1)->fetch_assoc();

        $type = $row['type'];
        $subject = $row['subject'];
        $description = $row['description'];
        $date = $row['date'];
        $status = $row['status'];

        $continue = 1;
    }
    else {
        $continue = 0;
    }
}


if ($continue) {
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Ticket No. <?php echo $ticket_id . ' - ' . htmlspecialchars($type); ?>
    </title>

    <link
        rel="stylesheet"
        href="css/custom/campus.css"
    >

</head>


<body class="admin-page">

<header class="admin-header">

    <div class="admin-brand">

        <div class="admin-logo">
            C
        </div>

        <div>
            <h1>CampusEats</h1>
            <span>Admin Panel</span>
        </div>

    </div>


    <div class="admin-header-right">

        <span class="admin-user">
            <?php echo htmlspecialchars($name); ?>
        </span>

        <a
            href="routers/logout.php"
            class="admin-logout"
        >
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

                    <a
                        href="all-orders.php?status=<?php echo urlencode($order_status['status']); ?>"
                    >
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
                    class="active"
                >
                    All Tickets
                </a>

                <?php

                $ticket_status_sql = mysqli_query(
                    $con,
                    "SELECT DISTINCT status FROM tickets;"
                );

                while ($ticket_status = mysqli_fetch_array($ticket_status_sql)) {

                ?>

                    <a
                        href="all-tickets.php?status=<?php echo urlencode($ticket_status['status']); ?>"
                    >
                        <?php echo htmlspecialchars($ticket_status['status']); ?>
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

        <div class="ticket-admin-header">

            <div>

                <a
                    href="all-tickets.php"
                    class="ticket-back-link"
                >
                    ← Back to Tickets
                </a>

                <h2>
                    Ticket #<?php echo $ticket_id; ?>
                </h2>

                <p>
                    View and manage this customer support request.
                </p>

            </div>

        </div>


        <!-- TICKET SUMMARY -->
        <section class="ticket-admin-summary">

            <div class="ticket-summary-top">

                <div class="ticket-summary-icon">
                    ?
                </div>

                <div class="ticket-summary-title">

                    <h3>
                        <?php echo htmlspecialchars($subject); ?>
                    </h3>

                    <span>
                        Created <?php echo htmlspecialchars($date); ?>
                    </span>

                </div>

            </div>


            <div class="ticket-summary-meta">

                <div class="ticket-meta-item">

                    <span class="ticket-meta-label">
                        Type
                    </span>

                    <strong>
                        <?php echo htmlspecialchars($type); ?>
                    </strong>

                </div>


                <div class="ticket-meta-item">

                    <span class="ticket-meta-label">
                        Status
                    </span>

                    <span
                        class="ticket-status-badge
                        <?php
                        if (strtolower($status) == 'open') {
                            echo 'ticket-open';
                        }
                        elseif (strtolower($status) == 'answered') {
                            echo 'ticket-answered';
                        }
                        else {
                            echo 'ticket-other';
                        }
                        ?>"
                    >
                        <?php echo htmlspecialchars($status); ?>
                    </span>

                </div>

            </div>


            <div class="ticket-summary-actions">

                <form
                    method="post"
                    action="routers/ticket-status.php"
                >

                    <input
                        type="hidden"
                        name="ticket_id"
                        value="<?php echo $ticket_id; ?>"
                    >

                    <input
                        type="hidden"
                        name="status"
                        value="<?php echo ($status != 'Closed' ? 'Closed' : 'Open'); ?>"
                    >

                    <button
                        class="ticket-action-button
                        <?php echo $status != 'Closed'
                            ? 'ticket-close-button'
                            : 'ticket-reopen-button'; ?>"
                        type="submit"
                        name="action"
                    >
                        <?php
                        echo $status != 'Closed'
                            ? 'Close Ticket'
                            : 'Reopen Ticket';
                        ?>
                    </button>

                </form>

            </div>

        </section>


        <!-- ORIGINAL DESCRIPTION -->
        <section class="ticket-admin-description">

            <div class="ticket-section-heading">
                <div>
                    <h3>Issue Description</h3>
                    <p>Original customer support request.</p>
                </div>
            </div>

            <div class="ticket-description-content">
                <?php echo nl2br(htmlspecialchars($description)); ?>
            </div>

        </section>


        <!-- CONVERSATION -->
        <section class="ticket-admin-conversation">

            <div class="ticket-section-heading">

                <div>
                    <h3>Conversation</h3>

                    <p>
                        Ticket replies and updates.
                    </p>
                </div>

            </div>


            <div class="ticket-messages">

            <?php

            $sql1 = mysqli_query(
                $con,
                "SELECT * FROM ticket_details WHERE ticket_id = $ticket_id;"
            );

            while ($row1 = mysqli_fetch_array($sql1)) {

                $sql2 = "SELECT * FROM users WHERE id = " . $row1['user_id'] . ";";

                if (mysqli_num_rows(mysqli_query($con, $sql2)) > 0) {

                    $row2 = $con->query($sql2)->fetch_assoc();

                    $message_name = $row2['name'];
                    $role1 = $row2['role'];

                }
                else {

                    $message_name = 'Unknown User';
                    $role1 = 'Customer';

                }

            ?>

                <article class="ticket-message">

                    <div class="ticket-message-header">

                        <div class="ticket-message-user">

                            <div class="ticket-message-avatar">
                                <?php
                                echo strtoupper(
                                    substr($message_name, 0, 1)
                                );
                                ?>
                            </div>

                            <div>

                                <strong>
                                    <?php echo htmlspecialchars($message_name); ?>
                                </strong>

                                <span>
                                    <?php echo htmlspecialchars($role1); ?>
                                </span>

                            </div>

                        </div>


                        <span class="ticket-message-date">
                            <?php echo htmlspecialchars($row1['date']); ?>
                        </span>

                    </div>


                    <div class="ticket-message-content">

                        <?php
                        echo nl2br(
                            htmlspecialchars($row1['description'])
                        );
                        ?>

                    </div>

                </article>

            <?php
            }

            ?>

            </div>

        </section>


        <!-- REPLY -->
        <?php
        if ($status != 'Closed') {
        ?>

        <section class="ticket-admin-reply">

            <div class="ticket-section-heading">

                <div>
                    <h3>Reply to Ticket</h3>

                    <p>
                        Send a response to the customer.
                    </p>
                </div>

            </div>


            <form
                method="post"
                action="routers/ticket-message.php"
                class="ticket-reply-form"
            >

                <input
                    type="hidden"
                    name="role"
                    value="<?php echo htmlspecialchars($role); ?>"
                >

                <input
                    type="hidden"
                    name="ticket_id"
                    value="<?php echo $ticket_id; ?>"
                >


                <div class="ticket-reply-field">

                    <label for="message">
                        Message
                    </label>

                    <textarea
                        name="message"
                        id="message"
                        minlength="5"
                        maxlength="300"
                        required
                        placeholder="Write your reply..."
                    ></textarea>

                    <span class="ticket-reply-hint">
                        Minimum 5 characters.
                    </span>

                </div>


                <div class="ticket-reply-footer">

                    <button
                        class="admin-button"
                        type="submit"
                        name="action"
                    >
                        Send Reply
                    </button>

                </div>

            </form>

        </section>

        <?php
        }
        else {
        ?>

        <div class="ticket-closed-notice">

            <div class="ticket-closed-icon">
                ✓
            </div>

            <div>
                <strong>
                    This ticket is closed
                </strong>

                <p>
                    Reopen the ticket to continue the conversation.
                </p>
            </div>

        </div>

        <?php
        }
        ?>

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