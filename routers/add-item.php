<?php

include '../includes/connect.php';

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;

if ($name === '' || $price < 0) {
    die("Invalid item information.");
}


/*
 * Default image value.
 * If the admin doesn't upload an image,
 * the item will simply have no image.
 */
$image = null;


/*
 * Handle uploaded image.
 */
if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        die("There was an error uploading the image.");
    }


    /*
     * Maximum file size: 5 MB
     */
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        die("Image must be smaller than 5 MB.");
    }


    /*
     * Check that the uploaded file is actually
     * a supported image.
     */
    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $file_type = mime_content_type($_FILES['image']['tmp_name']);

    if (!isset($allowed_types[$file_type])) {
        die("Only JPG, PNG and WebP images are allowed.");
    }


    /*
     * Generate a unique filename so two items
     * can never accidentally overwrite each other.
     */
    $extension = $allowed_types[$file_type];

    $image = uniqid('food_', true) . '.' . $extension;


    /*
     * Food image directory.
     */
    $upload_directory = '../images/food/';


    /*
     * Make sure the directory exists.
     */
    if (!is_dir($upload_directory)) {
        mkdir($upload_directory, 0755, true);
    }


    /*
     * Move uploaded image into the food directory.
     */
    $upload_path = $upload_directory . $image;

    if (!move_uploaded_file(
        $_FILES['image']['tmp_name'],
        $upload_path
    )) {
        die("Could not save the uploaded image.");
    }
}


/*
 * Insert the new item.
 */
$name = mysqli_real_escape_string($con, $name);

if ($image !== null) {

    $image = mysqli_real_escape_string($con, $image);

    $sql = "INSERT INTO items
            (name, price, image)
            VALUES
            ('$name', $price, '$image')";

}
else {

    $sql = "INSERT INTO items
            (name, price, image)
            VALUES
            ('$name', $price, NULL)";
}


if (!$con->query($sql)) {
    die("Error adding item: " . $con->error);
}


header("location: ../admin-page.php");
exit();

?>