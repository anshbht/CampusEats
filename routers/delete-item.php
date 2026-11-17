<?php

include '../includes/connect.php';

if (!isset($_SESSION['admin_sid']) || $_SESSION['admin_sid'] != session_id()) {
    header("location: ../login.php");
    exit();
}

$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

if ($item_id <= 0) {
    header("location: ../admin-page.php");
    exit();
}

// Get image before deleting the item
$result = $con->query("SELECT image FROM items WHERE id = $item_id");

if (!$result || $result->num_rows == 0) {
    header("location: ../admin-page.php");
    exit();
}

$item = $result->fetch_assoc();

try {

    // Start transaction
    $con->begin_transaction();

    // Delete all order details containing this item
    $con->query(
        "DELETE FROM order_details WHERE item_id = $item_id"
    );

    // Permanently delete the item
    $con->query(
        "DELETE FROM items WHERE id = $item_id"
    );

    // Commit database changes
    $con->commit();

    // Delete the food image from the server
    if (!empty($item['image'])) {

        $image_path = "../images/food/" . $item['image'];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

} catch (mysqli_sql_exception $e) {

    // Undo database changes if something fails
    $con->rollback();

    die("Error deleting item: " . $e->getMessage());
}

header("location: ../admin-page.php");
exit();

?>