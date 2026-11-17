<?php

session_start();

if(isset($_SESSION['admin_sid']) || isset($_SESSION['customer_sid']))
{
    header("location:index.php");
}
else
{
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | CampusEats</title>

    <link rel="stylesheet" href="css/custom/campus.css">

</head>

<body>

<div class="login-page">

    <div class="login-container">


        <!-- BRAND -->

        <div class="login-brand">

            <a href="login.php">
                Campus<span>Eats</span>
            </a>

            <div class="login-subtitle">
                Campus Canteen Food Ordering System
            </div>

        </div>


        <!-- REGISTER CARD -->

        <div class="login-card">

            <h1>Create your account</h1>

            <p class="login-card-description">
                Register to start ordering from the campus canteen.
            </p>


            <form
                class="formValidate"
                id="formValidate"
                method="post"
                action="routers/register-router.php"
                novalidate
            >


                <!-- USERNAME -->

                <div class="login-field">

                    <label for="username">
                        Username
                    </label>

                    <input
                        name="username"
                        id="username"
                        type="text"
                        placeholder="Choose a username"
                        data-error=".errorTxt1"
                    >

                    <div class="errorTxt1"></div>

                </div>


                <!-- NAME -->

                <div class="login-field">

                    <label for="name">
                        Full name
                    </label>

                    <input
                        name="name"
                        id="name"
                        type="text"
                        placeholder="Enter your full name"
                        data-error=".errorTxt2"
                    >

                    <div class="errorTxt2"></div>

                </div>


                <!-- PASSWORD -->

                <div class="login-field">

                    <label for="password">
                        Password
                    </label>

                    <input
                        name="password"
                        id="password"
                        type="password"
                        placeholder="Create a password"
                        data-error=".errorTxt3"
                    >

                    <div class="errorTxt3"></div>

                </div>


                <!-- PHONE -->

                <div class="login-field">

                    <label for="phone">
                        Phone number
                    </label>

                    <input
                        name="phone"
                        id="phone"
                        type="tel"
                        placeholder="Enter your phone number"
                        data-error=".errorTxt4"
                    >

                    <div class="errorTxt4"></div>

                </div>


                <!-- REGISTER BUTTON -->

                <button
                    type="submit"
                    class="login-button"
                >
                    Create account
                </button>


            </form>


            <!-- LOGIN -->

            <p class="login-register">

                Already have an account?

                <a href="login.php">
                    Sign in
                </a>

            </p>

        </div>


        <div class="login-footer">

            CampusEats · Campus Canteen

        </div>


    </div>

</div>


<!-- jQuery -->

<script src="js/plugins/jquery-1.11.2.min.js"></script>

<!-- Validation -->

<script src="js/plugins/jquery-validation/jquery.validate.min.js"></script>

<script src="js/plugins/jquery-validation/additional-methods.min.js"></script>


<script>

$("#formValidate").validate({

    rules: {

        username: {
            required: true,
            minlength: 5
        },

        name: {
            required: true,
            minlength: 5
        },

        password: {
            required: true,
            minlength: 5
        },

        phone: {
            required: true,
            minlength: 4
        }

    },


    messages: {

        username: {
            required: "Enter username",
            minlength: "Minimum 5 characters are required."
        },

        name: {
            required: "Enter name",
            minlength: "Minimum 5 characters are required."
        },

        password: {
            required: "Enter password",
            minlength: "Minimum 5 characters are required."
        },

        phone: {
            required: "Specify contact number.",
            minlength: "Minimum 4 characters are required."
        }

    },


    errorElement: "div",

    errorPlacement: function(error, element) {

        var placement = $(element).data("error");

        if (placement) {

            $(placement).append(error);

        }
        else {

            error.insertAfter(element);

        }

    }

});

</script>

</body>

</html>

<?php
}
?>