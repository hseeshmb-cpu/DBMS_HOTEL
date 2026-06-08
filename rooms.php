	<?php
	session_start();
	include "db.php";

	if(!isset($_SESSION['user'])){
		header("Location: login.php");
		exit();
	}
	?>

	<!DOCTYPE html>
	<html>
	<head>
	<title>Rooms & Void Requests</title>

	<style>
	body{
		font-family:Arial;
		background:#121212;
		color:white;
		padding:20px;
	}

	.box{
		background:#1e1e1e;
		padding:15px;
		border-radius:10px;
		margin-bottom:20px;
	}

	table{
		width:100%;
		border-collapse:collapse;
		margin-top:10px;
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
		cursor:pointer;
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

	a{text-decoration:none;}
	</style>

	</head>

	<body>
	
	<div class="section-header">
    <h2>Rooms</h2>

    <a href="index.php">
        <button>Back to Home</button>
    </a>
	</div>

	<div class="box">
	<h3>Available Rooms</h3>

	<table>
	<tr>
		<th>ID</th>
		<th>Room Type</th>
		<th>Price</th>
		<th>Capacity</th>
		<th>Available</th>
	</tr>
	

	<?php
	$res = $conn->query("SELECT * FROM rooms");

	while($row = $res->fetch_assoc()){
		  $availability = ($row['available'] > 0)
        ? $row['available']
        : "Fully Booked";

    echo "<tr>
        <td>{$row['room_id']}</td>
        <td>{$row['room_type']}</td>
        <td>₱{$row['price']}</td>
        <td>{$row['capacity']}</td>
        <td>$availability</td>
    </tr>";
	
	}
	?>

	</table>
	</div>

	</body>
	</html>
