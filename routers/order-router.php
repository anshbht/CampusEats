<?php

include '../includes/connect.php';
include '../includes/wallet.php';

if (!isset($_SESSION['customer_sid']) || $_SESSION['customer_sid'] != session_id()) {
    header("location: ../login.php");
    exit();
}

$address = isset($_POST['address']) ? $_POST['address'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$payment_type = isset($_POST['payment_type']) ? $_POST['payment_type'] : '';
$total = isset($_POST['total']) ? (float)$_POST['total'] : 0;

if ($address === '' || $payment_type === '' || $total <= 0) {
    die("Invalid order information.");
}

$sql = "INSERT INTO orders 
        (customer_id, payment_type, address, total, description) 
        VALUES 
        ($user_id, '$payment_type', '$address', $total, '$description')";

if ($con->query($sql) === TRUE) {

    $order_id = $con->insert_id;

    foreach ($_POST as $key => $value) {

        if (is_numeric($key) && (int)$value > 0) {

            $item_id = (int)$key;
            $quantity = (int)$value;

            $result = mysqli_query(
                $con,
                "SELECT * FROM items WHERE id = $item_id"
            );

            if (!$result || mysqli_num_rows($result) == 0) {
                continue;
            }

            $row = mysqli_fetch_assoc($result);
            $price = (float)$row['price'] * $quantity;

            $item_sql = "INSERT INTO order_details
                         (order_id, item_id, quantity, price)
                         VALUES
                         ($order_id, $item_id, $quantity, $price)";

            if (!$con->query($item_sql)) {
                die("Error adding order item: " . $con->error);
            }
        }
    }

    if ($payment_type == 'Wallet') {

        $balance = $balance - $total;

        $wallet_sql = "UPDATE wallet_details
                       SET balance = $balance
                       WHERE wallet_id = $wallet_id";

        if (!$con->query($wallet_sql)) {
            die("Error updating wallet: " . $con->error);
        }
    }

    header("Location: ../order-success.php?order_id=" . $order_id);
    exit();

} else {

    die("Error creating order: " . $con->error);
}

?>