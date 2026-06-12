<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];

$user = $conn->query("
    SELECT User_ID
    FROM users
    WHERE username='$username'
")->fetch_assoc();

$user_id = $user['User_ID'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Booking Details</title>

<style>
body{
    font-family:Arial;
    background:#121212;
    color:white;
    padding:20px;
	background-image: url('hotel-bg.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

.box{
    background:#1e1e1e;
    padding:15px;
    border-radius:10px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid #333;
    padding:10px;
}

th{
    background:#222;
}

button{
    padding:8px 12px;
    background:#2d89ef;
    color:white;
    border:none;
    border-radius:5px;
}

	.section-header{
		background:#1e1e1e;
		padding:15px;
		border-radius:10px;
		margin-bottom:20px;

		display:flex;
		justify-content:space-between;
		align-items:center;
	}

</style>
</head>

<body>

<div class="section-header">
    <h2>Booking Details</h2>

    <a href="index.php">
        <button>Back to Home</button>
    </a>
	</div>

<br><br>

<div class="box">

<table>
<tr>
    <th>Booking ID</th>
    <th>Room ID</th>
    <th>Check In</th>
    <th>Check Out</th>
    <th>Status</th>
</tr>

<?php

$res = $conn->query("
    SELECT *
    FROM bookings
    WHERE user_id = $user_id
    ORDER BY booking_id DESC
");

while($row = $res->fetch_assoc()){

    echo "<tr>
        <td>{$row['booking_id']}</td>
        <td>{$row['room_id']}</td>
        <td>{$row['check_in_date']}</td>
        <td>{$row['check_out_date']}</td>
        <td>{$row['booking_status']}</td>
    </tr>";
}
?>

</table>

</div>

</body>
</html>