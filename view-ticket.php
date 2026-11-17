<?php

include 'includes/connect.php';
include 'includes/wallet.php';

$continue = 0;

if($_SESSION['customer_sid']==session_id())
{
    $ticket_id = $_GET['id'];

    $sql1 = "SELECT * FROM tickets
             WHERE poster_id = $user_id
             AND id = $ticket_id
             AND not deleted;";

    if(mysqli_num_rows(mysqli_query($con,$sql1)) > 0)
    {
        $row = $con->query($sql1)->fetch_assoc();

        $type = $row['type'];
        $subject = $row['subject'];
        $description = $row['description'];
        $date = $row['date'];
        $status = $row['status'];

        $continue = 1;
    }
    else
    {
        $continue = 0;
    }
}

if($continue)
{
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ticket #<?php echo $ticket_id; ?> | CampusEats</title>

    <link rel="stylesheet" href="css/custom/campus.css">

</head>

<body>

<header class="modern-header">

    <a href="index.php" class="brand">
        Campus<span>Eats</span>
    </a>

    <div class="header-right">

        <div class="balance">
            ₹<?php echo $balance; ?>
        </div>

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

            <a href="tickets.php" class="active">
                <span>💬</span>
                Support
            </a>

            <a href="details.php">
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

        <div class="ticket-view-page">


            <!-- PAGE HEADER -->

            <div class="ticket-view-header">

                <h1>Support Ticket</h1>

                <p>
                    View your ticket and continue the conversation with support.
                </p>

            </div>


            <!-- TICKET DETAILS -->

            <div class="ticket-main-card">

                <div class="ticket-main-top">

                    <div class="ticket-main-title">

                        <div>

                            <h2>
                                <?php echo htmlspecialchars($subject); ?>
                            </h2>

                            <p>
                                Ticket #<?php echo $ticket_id; ?>
                            </p>

                        </div>

                        <div class="ticket-view-status">
                            <?php echo htmlspecialchars($status); ?>
                        </div>

                    </div>


                    <div class="ticket-view-meta">

                        <span>
                            <strong>Type:</strong>
                            <?php echo htmlspecialchars($type); ?>
                        </span>

                        <span>
                            <strong>Created:</strong>
                            <?php echo htmlspecialchars($date); ?>
                        </span>

                    </div>

                </div>


                <?php if($status != 'Closed') { ?>

                <div class="ticket-main-actions">

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
                            value="Closed"
                        >

                        <button
                            class="ticket-action-button"
                            type="submit"
                            name="action"
                        >
                            Close Ticket
                        </button>

                    </form>

                </div>

                <?php } else { ?>

                <div class="ticket-main-actions">

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
                            value="Open"
                        >

                        <button
                            class="ticket-action-button"
                            type="submit"
                            name="action"
                        >
                            Reopen Ticket
                        </button>

                    </form>

                </div>

                <?php } ?>

            </div>


            <!-- CONVERSATION -->

            <div class="ticket-conversation">

                <?php

                $sql1 = mysqli_query(
                    $con,
                    "SELECT * FROM ticket_details
                     WHERE ticket_id = $ticket_id;"
                );

                while($row1 = mysqli_fetch_array($sql1))
                {

                    $sql2 = "SELECT * FROM users
                             WHERE id = ".$row1['user_id'].";";

                    if(mysqli_num_rows(mysqli_query($con,$sql2)) > 0)
                    {
                        $row2 = $con->query($sql2)->fetch_assoc();

                        $messageName = $row2['name'];
                        $role1 = $row2['role'];
                    }

                ?>

                    <div class="ticket-message-card">

                        <div class="ticket-message-header">

                            <div class="ticket-message-user">

                                <div class="ticket-message-avatar">

                                    <?php
                                    echo strtoupper(
                                        substr($messageName, 0, 1)
                                    );
                                    ?>

                                </div>

                                <div>

                                    <div class="ticket-message-user-name">
                                        <?php echo htmlspecialchars($messageName); ?>
                                    </div>

                                    <div class="ticket-message-role">
                                        <?php echo htmlspecialchars($role1); ?>
                                    </div>

                                </div>

                            </div>


                            <div class="ticket-message-date">
                                <?php echo htmlspecialchars($row1['date']); ?>
                            </div>

                        </div>


                        <div class="ticket-message-body">
                            <?php echo htmlspecialchars($row1['description']); ?>
                        </div>

                    </div>

                <?php

                }

                ?>

            </div>


            <!-- REPLY -->

            <?php if($status != 'Closed') { ?>

            <div class="ticket-reply-card">

                <h2>Reply</h2>

                <p>
                    Add a message to continue the conversation.
                </p>


                <form
                    class="ticket-reply-form"
                    id="formValidate"
                    method="post"
                    action="routers/ticket-message.php"
                    novalidate
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


                    <textarea
                        name="message"
                        id="message"
                        placeholder="Write your reply..."
                        data-error=".errorTxt1"
                    ></textarea>

                    <div class="ticket-reply-error errorTxt1"></div>


                    <button
                        class="ticket-reply-button"
                        type="submit"
                        name="action"
                    >
                        Send Reply
                    </button>

                </form>

            </div>

            <?php } ?>

        </div>

    </main>

</div>


<script src="js/plugins/jquery-1.11.2.min.js"></script>

<script src="js/plugins/jquery-validation/jquery.validate.min.js"></script>

<script src="js/plugins/jquery-validation/additional-methods.min.js"></script>


<script>

$("#formValidate").validate({

    rules: {

        message: {
            required: true,
            minlength: 5,
            maxlength: 300
        }

    },

    messages: {

        message: {
            required: "Please provide a reply.",
            minlength: "Minimum 5 characters are required.",
            maxlength: "Maximum 300 characters are allowed."
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
        header("location:view-ticket-admin.php?id=".$_GET['id']);
    }
    else
    {
        header("location:login.php");
    }

}

?>