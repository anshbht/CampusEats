<?php

include 'includes/connect.php';
include 'includes/wallet.php';

if ($_SESSION['customer_sid'] == session_id()) {

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Campus Canteen</title>

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
                <a href="index.php" class="active">
                    Food Menu
                </a>
            </li>

            <li>
                <a href="orders.php">
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


    <!-- MAIN CONTENT -->

    <main class="modern-content">

        <div class="page-heading">

    <div class="heading-top">

        <div>
            <h1>Food Menu</h1>

            <p>
                Choose what you want to order from the campus canteen.
            </p>
        </div>

        <div class="menu-search">

            <input
                type="text"
                id="foodSearch"
                placeholder="Search food..."
                autocomplete="off"
            >

        </div>

    </div>

</div>


        <form
            method="post"
            action="place-order.php"
            id="orderForm"
        >


            <div class="menu-grid">

                <?php

                $result = mysqli_query(
                    $con,
                    "SELECT * FROM items WHERE NOT deleted;"
                );

                $counter = 1;

                while ($row = mysqli_fetch_array($result)) {
?>

<div
    class="food-card"
    data-food-name="<?php echo strtolower(htmlspecialchars($row["name"])); ?>"
>

    <?php
    $image_path = '';

    if (!empty($row['image'])) {
        $image_path = 'images/food/' . $row['image'];
    }

    $has_image = (
        $image_path !== '' &&
        file_exists(__DIR__ . '/' . $image_path)
    );
    ?>

    <div class="food-visual">

        <?php if ($has_image) { ?>

            <img
                src="<?php echo htmlspecialchars($image_path); ?>"
                alt="<?php echo htmlspecialchars($row['name']); ?>"
            >

        <?php } else { ?>

            <div class="food-placeholder">
                🍽️
            </div>

        <?php } ?>

    </div>

    <div class="food-category">
        CAMPUS CANTEEN
    </div>

    <div class="food-name">
        <?php echo htmlspecialchars($row["name"]); ?>
    </div>

    <div class="food-card-bottom">

        <div class="food-price">
            <strong>
                ₹<?php echo htmlspecialchars($row["price"]); ?>
            </strong>

            <span>
                / item
            </span>
        </div>

        <div class="quantity-wrapper">

            <label
                class="quantity-label"
                for="<?php echo $row["id"]; ?>"
            >
                Quantity
            </label>

            <input
                class="quantity-input"
                id="<?php echo $row["id"]; ?>"
                name="<?php echo $row["id"]; ?>"
                type="number"
                min="0"
                max="10"
                value="0"
            >

        </div>

    </div>

</div>

<?php

$counter++;

}

                ?>

            </div>


            <!-- ORDER NOTE -->

            <div class="order-note">

                <label for="description">
                    Special instructions
                </label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Any special instructions for your order..."
                ></textarea>

            </div>

            <!-- ORDER SUMMARY -->

<div class="order-summary">

    <div class="order-summary-header">

        <h2>Your Order</h2>

        <span class="cart-count" id="cartCount">
            0 items
        </span>

    </div>

    <div class="cart-items" id="cartItems">

        <div class="empty-cart">
            Your order is empty.
        </div>

    </div>

    <div class="cart-total">

        <span class="cart-total-label">
            Total
        </span>

        <span class="cart-total-price" id="cartTotal">
            ₹0
        </span>

    </div>

</div>

            <!-- ORDER BUTTON -->

            <div class="order-actions">

                <button
                    type="submit"
                    name="action"
                    class="order-button"
                >
                    Place Order
                </button>

            </div>


        </form>

    </main>

</div>


<footer class="modern-footer">

    CampusEats — Campus Canteen Food Ordering System

</footer>

<script>

const searchInput = document.getElementById("foodSearch");
const foodCards = document.querySelectorAll(".food-card");

searchInput.addEventListener("input", function () {

    const searchTerm = this.value.toLowerCase().trim();

    foodCards.forEach(function (card) {

        const foodName = card.dataset.foodName;

        if (foodName.includes(searchTerm)) {

            card.style.display = "";

        } else {

            card.style.display = "none";

        }

    });

});

</script>
<script>

const quantityInputs =
    document.querySelectorAll(".quantity-input");

const cartItems =
    document.getElementById("cartItems");

const cartCount =
    document.getElementById("cartCount");

const cartTotal =
    document.getElementById("cartTotal");


function updateCart() {

    cartItems.innerHTML = "";

    let totalItems = 0;
    let totalPrice = 0;

    quantityInputs.forEach(function(input) {

        const quantity = parseInt(input.value) || 0;

        if (quantity > 0) {

            const card = input.closest(".food-card");

            const name =
                card.querySelector(".food-name").textContent.trim();

            const priceText =
                card.querySelector(".food-price strong").textContent
                .replace("₹", "")
                .trim();

            const price =
                parseFloat(priceText) || 0;

            const itemTotal =
                price * quantity;

            totalItems += quantity;
            totalPrice += itemTotal;


            const item =
                document.createElement("div");

            item.className = "cart-item";

            item.innerHTML = `
                <div>
                    <div class="cart-item-name">
                        ${name}
                    </div>

                    <div class="cart-item-details">
                        ${quantity} × ₹${price}
                    </div>
                </div>

                <strong>
                    ₹${itemTotal}
                </strong>
            `;

            cartItems.appendChild(item);
        }

    });


    if (totalItems === 0) {

        cartItems.innerHTML =
            '<div class="empty-cart">Your order is empty.</div>';

    }


    cartCount.textContent =
        totalItems + (totalItems === 1 ? " item" : " items");

    cartTotal.textContent =
        "₹" + totalPrice;

}


quantityInputs.forEach(function(input) {

    input.addEventListener("input", updateCart);

});


updateCart();

</script>
</body>

</html>


<?php

}

else {

    if ($_SESSION['admin_sid'] == session_id()) {

        header("location:admin-page.php");

    }

    else {

        header("location:login.php");

    }

}

?>