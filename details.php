<?php

include 'includes/connect.php';

$user_id = $_SESSION['user_id'];

$result = mysqli_query($con, "SELECT * FROM users where id = $user_id");

while($row = mysqli_fetch_array($result))
{
    $name = $row['name'];
    $address = $row['address'];
    $contact = $row['contact'];
    $email = $row['email'];
    $username = $row['username'];
}

if($_SESSION['customer_sid']==session_id())
{
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profile | CampusEats</title>

    <link rel="stylesheet" href="css/custom/campus.css">

</head>

<body>

<header class="modern-header">

    <a href="index.php" class="brand">
        Campus<span>Eats</span>
    </a>

    <div class="header-right">

        <div class="profile">
            <?php echo htmlspecialchars($name); ?>
        </div>

    </div>

</header>


<div class="modern-layout">


    <!-- SIDEBAR -->

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
                    <?php echo htmlspecialchars($role); ?>
                </div>

            </div>

        </div>


        <nav class="modern-nav">

            <a href="index.php">
                <span>🍽️</span>
                Food Menu
            </a>

            <a href="orders.php">
                <span>📦</span>
                My Orders
            </a>

            <a href="tickets.php">
                <span>💬</span>
                Support
            </a>

            <a href="details.php" class="active">
                <span>👤</span>
                Profile
            </a>

            <a href="routers/logout.php">
                <span>↪</span>
                Logout
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="modern-content">

        <div class="profile-page">


            <!-- PAGE HEADER -->

            <div class="profile-header">

                <h1>Profile</h1>

                <p>
                    Update your personal and delivery information.
                </p>

            </div>


            <!-- PROFILE CARD -->

            <div class="profile-card">

                <div class="profile-card-top">

                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($name, 0, 1)); ?>
                    </div>

                    <div>

                        <h2>
                            Account Details
                        </h2>

                        <p>
                            Keep your information up to date for orders and contact.
                        </p>

                    </div>

                </div>


                <form
                    class="profile-form"
                    id="formValidate"
                    method="post"
                    action="routers/details-router.php"
                    novalidate
                >


                    <!-- USERNAME -->

                    <div class="profile-field">

                        <label for="username">
                            Username
                        </label>

                        <input
                            name="username"
                            id="username"
                            type="text"
                            value="<?php echo htmlspecialchars($username); ?>"
                            data-error=".errorTxt1"
                        >

                        <div class="profile-error errorTxt1"></div>

                    </div>


                    <!-- NAME -->

                    <div class="profile-field">

                        <label for="name">
                            Full Name
                        </label>

                        <input
                            name="name"
                            id="name"
                            type="text"
                            value="<?php echo htmlspecialchars($name); ?>"
                            data-error=".errorTxt2"
                        >

                        <div class="profile-error errorTxt2"></div>

                    </div>


                    <!-- EMAIL -->

                    <div class="profile-field">

                        <label for="email">
                            Email
                        </label>

                        <input
                            name="email"
                            id="email"
                            type="email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            data-error=".errorTxt3"
                        >

                        <div class="profile-error errorTxt3"></div>

                    </div>


                    <!-- PASSWORD -->

                    <div class="profile-field">

                        <label for="password">
                            Password
                        </label>

                        <input
                            name="password"
                            id="password"
                            type="password"
                            placeholder="Enter your password"
                            data-error=".errorTxt4"
                        >

                        <div class="profile-error errorTxt4"></div>

                    </div>


                    <!-- PHONE -->

                    <div class="profile-field">

                        <label for="phone">
                            Contact Number
                        </label>

                        <input
                            name="phone"
                            id="phone"
                            type="number"
                            value="<?php echo htmlspecialchars($contact); ?>"
                            data-error=".errorTxt5"
                        >

                        <div class="profile-error errorTxt5"></div>

                    </div>


                    <!-- ADDRESS -->

                    <div class="profile-field">

                        <label for="address">
                            Delivery Address
                        </label>

                        <textarea
                            name="address"
                            id="address"
                            data-error=".errorTxt6"
                        ><?php echo htmlspecialchars($address); ?></textarea>

                        <div class="profile-error errorTxt6"></div>

                    </div>


                    <button
                        type="submit"
                        name="action"
                        class="profile-submit"
                    >
                        Save Changes
                    </button>

                </form>

            </div>

        </div>

    </main>

</div>


<script src="js/plugins/jquery-1.11.2.min.js"></script>

<script src="js/plugins/jquery-validation/jquery.validate.min.js"></script>

<script src="js/plugins/jquery-validation/additional-methods.min.js"></script>


<script>

$("#formValidate").validate({

    rules: {

        username: {
            required: true,
            minlength: 5,
            maxlength: 10
        },

        name: {
            required: true,
            minlength: 5,
            maxlength: 15
        },

        email: {
            required: true,
            maxlength: 35
        },

        password: {
            required: true,
            minlength: 5,
            maxlength: 16
        },

        phone: {
            required: true,
            minlength: 4,
            maxlength: 11
        },

        address: {
            required: true,
            minlength: 10,
            maxlength: 300
        }

    },

    messages: {

        username: {
            required: "Enter username",
            minlength: "Minimum 5 characters are required.",
            maxlength: "Maximum 10 characters are required."
        },

        name: {
            required: "Enter name",
            minlength: "Minimum 5 characters are required.",
            maxlength: "Maximum 15 characters are required."
        },

        email: {
            required: "Enter email",
            maxlength: "Maximum 35 characters are required."
        },

        password: {
            required: "Enter password",
            minlength: "Minimum 5 characters are required.",
            maxlength: "Maximum 16 characters are required."
        },

        phone: {
            required: "Specify contact number.",
            minlength: "Minimum 4 characters are required.",
            maxlength: "Maximum 11 digits are accepted."
        },

        address: {
            required: "Specify address",
            minlength: "Minimum 10 characters are required.",
            maxlength: "Maximum 300 characters are accepted."
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

    if($_SESSION['admin_sid']==session_id())
    {
        header("location:admin-page.php");
    }
    else
    {
        header("location:login.php");
    }

}

?>