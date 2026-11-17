<?php
include 'includes/connect.php';

if ($_SESSION['admin_sid'] == session_id()) {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | CampusEats</title>
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
                <strong><?php echo htmlspecialchars($name); ?></strong>
                <span><?php echo htmlspecialchars($role); ?></span>
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
                <div class="admin-nav-title">
                    Support Tickets
                </div>

                <a href="all-tickets.php">
                    All Tickets
                </a>

                <?php
                $ticket_status_sql = mysqli_query(
                    $con,
                    "SELECT DISTINCT status FROM tickets;"
                );

                while ($ticket_status = mysqli_fetch_array($ticket_status_sql)) {
                ?>
                    <a href="all-tickets.php?status=<?php echo urlencode($ticket_status['status']); ?>">
                        <?php echo htmlspecialchars($ticket_status['status']); ?>
                    </a>
                <?php
                }
                ?>
            </div>

            <a href="users.php" class="active">
                Users
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="admin-content">

        <div class="admin-page-heading">
            <div>
                <h2>Users</h2>
                <p>
                    Manage user accounts, access, verification and wallet balances.
                </p>
            </div>
        </div>


        <!-- EXISTING USERS -->
        <section class="users-admin-section">

            <div class="users-section-header">
                <div>
                    <h3>User Management</h3>
                    <p>Modify existing user accounts.</p>
                </div>

                <span class="users-count">
                    <?php
                    $count_result = mysqli_query(
                        $con,
                        "SELECT COUNT(*) AS total FROM users"
                    );

                    $count_row = mysqli_fetch_array($count_result);

                    echo $count_row['total'];
                    ?>
                    Users
                </span>
            </div>


            <form
                method="post"
                action="routers/user-router.php"
                class="users-form"
            >

                <div class="users-table-wrapper">

                    <table class="users-table">

                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Address</th>
                                <th>Role</th>
                                <th>Verification</th>
                                <th>Access</th>
                                <th>Wallet</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php

                        $result = mysqli_query(
                            $con,
                            "SELECT * FROM users"
                        );

                        while ($row = mysqli_fetch_array($result)) {

                            $key = $row['id'];

                            /*
                             * Get wallet balance for this user.
                             */
                            $balance = 0;

                            $wallet_sql = mysqli_query(
                                $con,
                                "SELECT * FROM wallet WHERE customer_id = $key;"
                            );

                            if ($wallet_row = mysqli_fetch_array($wallet_sql)) {

                                $wallet_id = $wallet_row['id'];

                                $wallet_details_sql = mysqli_query(
                                    $con,
                                    "SELECT * FROM wallet_details WHERE wallet_id = $wallet_id;"
                                );

                                if ($wallet_details = mysqli_fetch_array($wallet_details_sql)) {
                                    $balance = $wallet_details['balance'];
                                }
                            }

                        ?>

                            <tr>

                                <!-- USER -->
                                <td>
                                    <div class="user-cell">

                                        <div class="user-table-avatar">
                                            <?php
                                            echo strtoupper(
                                                substr($row['name'], 0, 1)
                                            );
                                            ?>
                                        </div>

                                        <div>
                                            <strong>
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </strong>

                                            <span>
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </span>
                                        </div>

                                    </div>
                                </td>


                                <!-- CONTACT -->
                                <td>
                                    <span class="user-table-text">
                                        <?php
                                        echo $row['contact'] != ''
                                            ? htmlspecialchars($row['contact'])
                                            : '—';
                                        ?>
                                    </span>
                                </td>


                                <!-- ADDRESS -->
                                <td>
                                    <span class="user-table-text">
                                        <?php
                                        echo $row['address'] != ''
                                            ? htmlspecialchars($row['address'])
                                            : '—';
                                        ?>
                                    </span>
                                </td>


                                <!-- ROLE -->
                                <td>
                                    <select
                                        name="<?php echo $row['id']; ?>_role"
                                        class="user-select"
                                    >
                                        <option
                                            value="Administrator"
                                            <?php
                                            echo $row['role'] == 'Administrator'
                                                ? 'selected'
                                                : '';
                                            ?>
                                        >
                                            Administrator
                                        </option>

                                        <option
                                            value="Customer"
                                            <?php
                                            echo $row['role'] == 'Customer'
                                                ? 'selected'
                                                : '';
                                            ?>
                                        >
                                            Customer
                                        </option>
                                    </select>
                                </td>


                                <!-- VERIFICATION -->
                                <td>
                                    <select
                                        name="<?php echo $row['id']; ?>_verified"
                                        class="user-select"
                                    >
                                        <option
                                            value="1"
                                            <?php
                                            echo $row['verified']
                                                ? 'selected'
                                                : '';
                                            ?>
                                        >
                                            Verified
                                        </option>

                                        <option
                                            value="0"
                                            <?php
                                            echo !$row['verified']
                                                ? 'selected'
                                                : '';
                                            ?>
                                        >
                                            Not Verified
                                        </option>
                                    </select>
                                </td>


                                <!-- ACCESS -->
                                <td>
                                    <select
                                        name="<?php echo $row['id']; ?>_deleted"
                                        class="user-select"
                                    >
                                        <option
                                            value="0"
                                            <?php
                                            echo !$row['deleted']
                                                ? 'selected'
                                                : '';
                                            ?>
                                        >
                                            Enabled
                                        </option>

                                        <option
                                            value="1"
                                            <?php
                                            echo $row['deleted']
                                                ? 'selected'
                                                : '';
                                            ?>
                                        >
                                            Disabled
                                        </option>
                                    </select>
                                </td>


                                <!-- WALLET -->
                                <td>
                                    <div class="wallet-input">
                                        <span>Rs.</span>

                                        <input
                                            name="<?php echo $row['id']; ?>_balance"
                                            value="<?php echo htmlspecialchars($balance); ?>"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                        >
                                    </div>
                                </td>

                            </tr>

                        <?php
                        }
                        ?>

                        </tbody>

                    </table>

                </div>


                <div class="users-form-footer">
                    <button
                        type="submit"
                        name="action"
                        class="admin-button"
                    >
                        Save Changes
                    </button>
                </div>

            </form>

        </section>


        <!-- ADD USER -->
        <section class="users-admin-section add-user-section">

            <div class="users-section-header">
                <div>
                    <h3>Add User</h3>
                    <p>Create a new CampusEats account.</p>
                </div>
            </div>


            <form
                method="post"
                action="routers/add-users.php"
                class="add-user-form"
            >

                <div class="add-user-grid">

                    <div class="admin-field">
                        <label for="username">
                            Username
                        </label>

                        <input
                            id="username"
                            name="username"
                            type="text"
                            minlength="5"
                            required
                        >
                    </div>


                    <div class="admin-field">
                        <label for="password">
                            Password
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            minlength="5"
                            required
                        >
                    </div>


                    <div class="admin-field">
                        <label for="name">
                            Name
                        </label>

                        <input
                            id="name"
                            name="name"
                            type="text"
                            minlength="5"
                            required
                        >
                    </div>


                    <div class="admin-field">
                        <label for="email">
                            Email
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                        >
                    </div>


                    <div class="admin-field">
                        <label for="contact">
                            Phone Number
                        </label>

                        <input
                            id="contact"
                            name="contact"
                            type="number"
                            minlength="4"
                            required
                        >
                    </div>


                    <div class="admin-field admin-field-wide">
                        <label for="address">
                            Address
                        </label>

                        <input
                            id="address"
                            name="address"
                            type="text"
                            minlength="10"
                        >
                    </div>


                    <div class="admin-field">
                        <label for="new-role">
                            Role
                        </label>

                        <select
                            id="new-role"
                            name="role"
                        >
                            <option value="Customer" selected>
                                Customer
                            </option>

                            <option value="Administrator">
                                Administrator
                            </option>
                        </select>
                    </div>


                    <div class="admin-field">
                        <label for="new-verified">
                            Verification
                        </label>

                        <select
                            id="new-verified"
                            name="verified"
                        >
                            <option value="0" selected>
                                Not Verified
                            </option>

                            <option value="1">
                                Verified
                            </option>
                        </select>
                    </div>


                    <div class="admin-field">
                        <label for="new-deleted">
                            Account Status
                        </label>

                        <select
                            id="new-deleted"
                            name="deleted"
                        >
                            <option value="0" selected>
                                Enabled
                            </option>

                            <option value="1">
                                Disabled
                            </option>
                        </select>
                    </div>

                </div>


                <div class="users-form-footer">
                    <button
                        type="submit"
                        name="action"
                        class="admin-button"
                    >
                        Add User
                    </button>
                </div>

            </form>

        </section>

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
        header("location:index.php");
    }
    else {
        header("location:login.php");
    }

}

?>