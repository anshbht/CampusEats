<?php

include 'includes/connect.php';
include 'includes/wallet.php';

if($_SESSION['customer_sid']==session_id())
{
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Support | CampusEats</title>

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
            <?php echo $name; ?>
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
                    <?php echo $name; ?>
                </div>

                <div class="user-role">
                    <?php echo $role; ?>
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

        <div class="tickets-page">


            <!-- PAGE HEADER -->

            <div class="tickets-header">

                <h1>Support</h1>

                <p>
                    Need help with your order or account? Create a support ticket.
                </p>

            </div>


            <!-- CREATE TICKET -->

            <div class="ticket-create-card">

                <h2>Open a ticket</h2>

                <p>
                    Tell us about your problem and our team will look into it.
                </p>


                <form
                    class="ticket-form"
                    id="formValidate"
                    method="post"
                    action="routers/add-ticket.php"
                    novalidate
                >

                    <div class="ticket-field">

                        <label for="subject">
                            Subject
                        </label>

                        <input
                            name="subject"
                            id="subject"
                            type="text"
                            placeholder="What do you need help with?"
                            data-error=".errorTxt1"
                        >

                        <div class="ticket-error errorTxt1"></div>

                    </div>


                    <div class="ticket-field">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            placeholder="Describe your problem in detail..."
                            data-error=".errorTxt2"
                        ></textarea>

                        <div class="ticket-error errorTxt2"></div>

                    </div>


                    <div class="ticket-field">

                        <label for="type">
                            Issue type
                        </label>

                        <select
                            name="type"
                            id="type"
                            data-error=".errorTxt3"
                        >

                            <option value="">Choose an issue type</option>

                            <option value="Support">
                                Support
                            </option>

                            <option value="Payment">
                                Payment
                            </option>

                            <option value="Complaint">
                                Complaint
                            </option>

                            <option value="Others">
                                Others
                            </option>

                        </select>

                        <div class="ticket-error errorTxt3"></div>

                    </div>


                    <input
                        type="hidden"
                        value="<?php echo $user_id; ?>"
                        name="id"
                    >


                    <button
                        type="submit"
                        name="action"
                        class="ticket-submit"
                    >
                        Submit Ticket
                    </button>

                </form>

            </div>


            <!-- TICKET LIST -->

            <div class="ticket-list-header">

                <h2>My Tickets</h2>

                <p>
                    View and manage your previous support requests.
                </p>

            </div>


            <?php

            /*
             * Status filter
             */

            if(isset($_GET['status']))
            {
                $status = $_GET['status'];
            }
            else
            {
                $status = '%';
            }


            /*
             * Get available ticket statuses
             */

            $statusQuery = mysqli_query(
                $con,
                "SELECT DISTINCT status
                 FROM tickets
                 WHERE poster_id = $user_id
                 AND not deleted;"
            );

            ?>

            <div class="ticket-filter">

                <a
                    href="tickets.php"
                    class="<?php echo !isset($_GET['status']) ? 'active' : ''; ?>"
                >
                    All Tickets
                </a>

                <?php

                while($statusRow = mysqli_fetch_array($statusQuery))
                {

                    $statusName = $statusRow['status'];

                    $activeClass =
                        isset($_GET['status']) &&
                        $_GET['status'] == $statusName
                        ? 'active'
                        : '';

                ?>

                    <a
                        href="tickets.php?status=<?php echo urlencode($statusName); ?>"
                        class="<?php echo $activeClass; ?>"
                    >
                        <?php echo htmlspecialchars($statusName); ?>
                    </a>

                <?php

                }

                ?>

            </div>


            <?php

            /*
             * Get tickets
             */

            $sql = mysqli_query(
                $con,
                "SELECT *
                 FROM tickets
                 WHERE poster_id = $user_id
                 AND status LIKE '$status'
                 AND not deleted;"
            );


            $ticketCount = mysqli_num_rows($sql);


            if($ticketCount > 0)
            {

                while($row = mysqli_fetch_array($sql))
                {

                    $ticketStatus = strtolower($row['status']);

                    $statusClass = '';

                    if(
                        strpos($ticketStatus, 'closed') !== false ||
                        strpos($ticketStatus, 'resolved') !== false
                    )
                    {
                        $statusClass = 'closed';
                    }
                    elseif(
                        strpos($ticketStatus, 'pending') !== false
                    )
                    {
                        $statusClass = 'pending';
                    }

            ?>

                    <a
                        href="view-ticket.php?id=<?php echo $row['id']; ?>"
                        class="ticket-card"
                        style="display:block; text-decoration:none;"
                    >

                        <div class="ticket-card-header">

                            <div>

                                <div class="ticket-number">
                                    <?php echo htmlspecialchars($row['subject']); ?>
                                </div>

                                <div class="ticket-date">
                                    <?php echo htmlspecialchars($row['date']); ?>
                                </div>

                            </div>


                            <div class="ticket-status <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </div>

                        </div>


                        <div class="ticket-info">

                            <div class="ticket-info-item">

                                <span class="ticket-info-label">
                                    Ticket ID
                                </span>

                                <span class="ticket-info-value">
                                    #<?php echo $row['id']; ?>
                                </span>

                            </div>


                            <div class="ticket-info-item">

                                <span class="ticket-info-label">
                                    Issue Type
                                </span>

                                <span class="ticket-info-value">
                                    <?php echo htmlspecialchars($row['type']); ?>
                                </span>

                            </div>

                        </div>

                    </a>

            <?php

                }

            }
            else
            {

            ?>

                <div class="no-tickets">

                    <h3>No tickets found</h3>

                    <p>
                        You don't have any support tickets yet.
                    </p>

                </div>

            <?php

            }

            ?>

        </div>

    </main>

</div>


<script src="js/plugins/jquery-1.11.2.min.js"></script>

<script src="js/plugins/jquery-validation/jquery.validate.min.js"></script>

<script>

$("#formValidate").validate({

    rules: {

        subject: {
            required: true,
            minlength: 5,
            maxlength: 100
        },

        description: {
            required: true,
            minlength: 20,
            maxlength: 300
        },

        type: {
            required: true
        }

    },

    messages: {

        subject: {
            required: "Provide a subject",
            minlength: "Minimum 5 characters are required.",
            maxlength: "Maximum 100 characters are allowed."
        },

        description: {
            required: "Provide a description of your problem",
            minlength: "Minimum 20 characters are required.",
            maxlength: "Maximum 300 characters are allowed."
        },

        type: {
            required: "Please specify the type of your problem"
        }

    },

    errorElement: "div",

    errorPlacement: function(error, element) {

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
        header("location:all-tickets.php");
    }
    else
    {
        header("location:login.php");
    }

}

?>