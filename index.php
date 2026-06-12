<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

include "db.php";

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Hotel Dashboard</title>

<style>
body{
    font-family:Arial;
    background:#121212;
    color:white;
    margin:0;
    padding:30px;
	background-image: url('hotel-bg.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

.header{
    background:#1e1e1e;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}

button{
    padding:10px 15px;
    margin:5px;
    background:#2d89ef;
    border:none;
    color:white;
    border-radius:5px;
    cursor:pointer;
}

button:hover{
    background:#1f6fd1;
}

.container{
    background:#1e1e1e;
    padding:20px;
    border-radius:10px;
}
</style>

</head>

<body>

<div class="header">
    <h2 style="font-family:Georgia, serif">COLOVE HOTEL</h2>
    <p>Welcome <?php echo $_SESSION['user'];?></p>
</div>

<div class="container">

    <a href="rooms.php"><button>Rooms</button></a>
    <a href="bookings.php"><button>Bookings</button></a>
	<a href="booking_details.php"><button>Booking Details</button></a>
    <a href="void_requests.php"><button>Booking Cancelation</button></a>

    <?php if($role == 'admin'){ ?>
        <a href="reception_staff.php"><button>Reception Staff</button></a>
    <?php } ?>

    <a href="logout.php"><button>Logout</button></a>

</div>

</body>
</html>