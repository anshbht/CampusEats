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

    <title>Login | CampusEats</title>

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


        <!-- LOGIN CARD -->

        <div class="login-card">

            <h1>Welcome back</h1>

            <p class="login-card-description">
                Sign in to continue to your account.
            </p>


            <form
                method="post"
                action="routers/router.php"
                id="form"
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
                        placeholder="Enter your username"
                        required
                    >

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
                        placeholder="Enter your password"
                        required
                    >

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    name="action"
                    class="login-button"
                >
                    Sign in
                </button>


            </form>


            <!-- REGISTER -->

            <p class="login-register">

                Don't have an account?

                <a href="register.php">
                    Create an account
                </a>

            </p>

        </div>


        <div class="login-footer">

            CampusEats · Campus Canteen

        </div>


    </div>

</div>

</body>

</html>

<?php
}
?>