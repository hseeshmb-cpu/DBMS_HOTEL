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
		margin-bottom:20px;
	}
	
	input, select{
        width:10%;
        padding:8px;
        margin:5px 0;
        background:#2a2a2a;
        color:white;
        border:1px solid #444;
        border-radius:5px;
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

	<form method="GET" style="margin-bottom:15px;">
    <label>Sort By: </label>
    <select name="sort" onchange="this.form.submit()">
        <option value="">Default</option>
        <option value="cheap" <?= (isset($_GET['sort']) && $_GET['sort']=='cheap') ? 'selected' : '' ?>>
            Cheapest First
        </option>
        <option value="expensive" <?= (isset($_GET['sort']) && $_GET['sort']=='expensive') ? 'selected' : '' ?>>
            Most Expensive
        </option>
        <option value="low_capacity" <?= (isset($_GET['sort']) && $_GET['sort']=='low_capacity') ? 'selected' : '' ?>>
            Low Capacity
        </option>
        <option value="high_capacity" <?= (isset($_GET['sort']) && $_GET['sort']=='high_capacity') ? 'selected' : '' ?>>
            High Capacity
        </option>
        <option value="available" <?= (isset($_GET['sort']) && $_GET['sort']=='available') ? 'selected' : '' ?>>
            Most Available
        </option>
    </select>
	</form>

	<table>
	<tr>
		<th>Room Type</th>
		<th>Price</th>
		<th>Capacity</th>
		<th>Available</th>
	</tr>
	

		<?php

		$sort = $_GET['sort'] ?? '';

		$query = "
			SELECT 
				r.room_type,
				MIN(r.price) AS price,
				MIN(r.capacity) AS capacity,
				COUNT(r.room_id) AS total_rooms,

				(
					COUNT(r.room_id) - 
					COALESCE((
						SELECT COUNT(*)
						FROM bookings b
						JOIN rooms r2 ON b.room_id = r2.room_id
						WHERE r2.room_type = r.room_type
						AND b.booking_status IN ('Confirmed', 'Checked In')
					), 0)
				) AS available_rooms

			FROM rooms r
			GROUP BY r.room_type
			";

			if ($sort == "cheap") {
				$query .= " ORDER BY price ASC";
			}
			elseif ($sort == "expensive") {
				$query .= " ORDER BY price DESC";
			}
			elseif ($sort == "low_capacity") {
				$query .= " ORDER BY capacity ASC";
			}
			elseif ($sort == "high_capacity") {
				$query .= " ORDER BY capacity DESC";
			}
			elseif ($sort == "available") {
				$query .= " ORDER BY available_rooms DESC";
			}

			$res = $conn->query($query);

			while($row = $res->fetch_assoc()){

			$availability = ($row['available_rooms'] > 0)
				? $row['available_rooms']
				: "Fully Booked";

			echo "
			<tr>
				<td>{$row['room_type']}</td>
				<td>₱{$row['price']}</td>
				<td>{$row['capacity']}</td>
				<td>{$availability}</td>
			</tr>";
		}
		?>

	</table>
	</div>

	</body>
	</html>
