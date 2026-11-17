<?php

include 'includes/connect.php';

if($_SESSION['admin_sid']==session_id())
{
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | CampusEats</title>

    <link rel="stylesheet" href="css/custom/campus.css">

</head>

<body>

<header class="modern-header">

    <a href="admin-page.php" class="brand">
        Campus<span>Eats</span>
    </a>

    <div class="header-right">

        <div class="profile">
            <?php echo htmlspecialchars($name); ?>
        </div>

    </div>

</header>


<div class="modern-layout">


    <!-- ADMIN SIDEBAR -->

    <aside class="modern-sidebar">

        <div class="user-box">

            <div class="user-avatar">
                <?php echo strtoupper(substr($name, 0, 1)); ?>
            </div>

            <div>

                <div class="user-name">
                    <?php echo htmlspecialchars($name); ?>
                </div>

                <div class="user-role">
                    Administrator
                </div>

            </div>

        </div>


        <nav class="modern-nav">

            <a href="admin-page.php" class="active">
                <span>🍽️</span>
                Food Menu
            </a>

            <a href="all-orders.php">
                <span>📦</span>
                All Orders
            </a>

            <a href="all-tickets.php">
                <span>💬</span>
                Support Tickets
            </a>

            <a href="users.php">
                <span>👥</span>
                Users
            </a>

            <a href="routers/logout.php">
                <span>↪</span>
                Logout
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="modern-content">

        <div class="admin-page">


            <!-- PAGE HEADER -->

            <div class="admin-header">

                <h1>Food Menu</h1>

                <p>
                    Manage menu items, prices and availability.
                </p>

            </div>


            <!-- MANAGE EXISTING ITEMS -->

            <section class="admin-section">

                <div class="admin-section-header">

                    <div>

                        <h2>Menu Items</h2>

                        <p>
                            Edit existing items or change their availability.
                        </p>

                    </div>

                </div>


                <form
    id="formValidate1"
    method="post"
    action="routers/add-item.php"
    enctype="multipart/form-data"
    novalidate
>

                    <div class="admin-table-wrapper">

                        <table class="admin-table">

                            <thead>
<thead>
    <tr>
        <th>Item</th>
        <th>Price / Piece</th>
        <th>Availability</th>
        <th>Action</th>
    </tr>

                            </thead>


                            <tbody>

                            <?php

                            $result = mysqli_query(
                                $con,
                                "SELECT * FROM items"
                            );

                            while($row = mysqli_fetch_array($result))
                            {

?>

    <tr>

        <!-- ITEM -->
        <td>
            <input
                value="<?php echo htmlspecialchars($row['name']); ?>"
                id="<?php echo $row['id']; ?>_name"
                name="<?php echo $row['id']; ?>_name"
                type="text"
                data-error=".errorTxt<?php echo $row['id']; ?>"
            >

            <div class="admin-error errorTxt<?php echo $row['id']; ?>"></div>
        </td>


        <!-- PRICE -->
        <td>
            <input
                value="<?php echo htmlspecialchars($row['price']); ?>"
                id="<?php echo $row['id']; ?>_price"
                name="<?php echo $row['id']; ?>_price"
                type="text"
                data-error=".errorTxt<?php echo $row['id']; ?>"
            >
        </td>


        <!-- AVAILABILITY -->
        <td>
            <select
                name="<?php echo $row['id']; ?>_hide"
            >

                <?php

                if($row['deleted'] == 0)
                {
                    $text1 = 'selected';
                    $text2 = '';
                }
                else
                {
                    $text1 = '';
                    $text2 = 'selected';
                }

                ?>

                <option
                    value="1"
                    <?php echo $text1; ?>
                >
                    Available
                </option>

                <option
                    value="2"
                    <?php echo $text2; ?>
                >
                    Not Available
                </option>

            </select>
        </td>


        <!-- REMOVE -->
        <td>
    <button
        type="submit"
        name="item_id"
        value="<?php echo $row['id']; ?>"
        formaction="routers/delete-item.php"
        formmethod="POST"
        class="admin-remove-button"
        onclick="return confirm('Are you sure you want to permanently delete this item?');"
    >
        Remove
    </button>
</td>

    </tr>

<?php

}

?>}

                            ?>

                            </tbody>

                        </table>

                    </div>


                    <div class="admin-action-row">

                        <button
                            class="admin-button"
                            type="submit"
                            name="action"
                        >
                            Save Menu Changes
                        </button>

                    </div>

                </form>

            </section>


            <!-- ADD ITEM -->

            <section class="admin-section">

                <div class="admin-section-header">

                    <div>

                        <h2>Add Menu Item</h2>

                        <p>
                            Add a new food item to the campus canteen menu.
                        </p>

                    </div>

                </div>


                <form
    id="formValidate1"
    method="post"
    action="routers/add-item.php"
    enctype="multipart/form-data"
    novalidate
>

    <div class="admin-add-grid">

        <div class="admin-field">

            <label for="name">
                Item Name
            </label>

            <input
                id="name"
                name="name"
                type="text"
                placeholder="e.g. Veg Sandwich"
            >

        </div>


        <div class="admin-field">

            <label for="price">
                Price
            </label>

            <input
                id="price"
                name="price"
                type="text"
                placeholder="e.g. 50"
            >

        </div>


        <div class="admin-field">

            <label for="image">
                Food Image
            </label>

            <input
                id="image"
                name="image"
                type="file"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="admin-field-hint">
                JPG, PNG or WebP. Maximum 5 MB.
            </small>

        </div>

    </div>


    <div class="admin-add-actions">

        <button
            class="admin-button"
            type="submit"
            name="action"
        >
            Add Item
        </button>

    </div>

</form>

            </section>

        </div>

    </main>

</div>


<script src="js/plugins/jquery-1.11.2.min.js"></script>

<script src="js/plugins/jquery-validation/jquery.validate.min.js"></script>

<script src="js/plugins/jquery-validation/additional-methods.min.js"></script>


<script>

$("#formValidate").validate({

    rules: {

        <?php

        $result = mysqli_query(
            $con,
            "SELECT * FROM items"
        );

        while($row = mysqli_fetch_array($result))
        {

            echo $row["id"]."_name:{";
            echo "required:true,";
            echo "minlength:5,";
            echo "maxlength:20";
            echo "},";

            echo $row["id"]."_price:{";
            echo "required:true,";
            echo "min:0";
            echo "},";

        }

        ?>

    },

    messages: {

        <?php

        $result = mysqli_query(
            $con,
            "SELECT * FROM items"
        );

        while($row = mysqli_fetch_array($result))
        {

            echo $row["id"]."_name:{";
            echo 'required:"Enter item name",';
            echo 'minlength:"Minimum length is 5 characters",';
            echo 'maxlength:"Maximum length is 20 characters"';
            echo "},";

            echo $row["id"]."_price:{";
            echo 'required:"Enter price of item",';
            echo 'min:"Minimum item price is Rs. 0"';
            echo "},";

        }

        ?>

    },

    errorElement: "div",

    errorPlacement: function(error, element)
    {

        var placement = $(element).data("error");

        if(placement)
        {
            $(placement).append(error);
        }
        else
        {
            error.insertAfter(element);
        }

    }

});


$("#formValidate1").validate({

    rules: {

        name: {
            required: true,
            minlength: 5
        },

        price: {
            required: true,
            min: 0
        }

    },

    messages: {

        name: {
            required: "Enter item name",
            minlength: "Minimum length is 5 characters"
        },

        price: {
            required: "Enter item price",
            min: "Minimum item price is Rs. 0"
        }

    },

    errorElement: "div",

    errorPlacement: function(error, element)
    {

        var placement = $(element).data("error");

        if(placement)
        {
            $(placement).append(error);
        }
        else
        {
            error.insertAfter(element);
        }

    }

});

</script>


</body>

</html>

<?php

}

else
{

    if($_SESSION['customer_sid']==session_id())
    {
        header("location:index.php");
    }
    else
    {
        header("location:login.php");
    }

}

?>