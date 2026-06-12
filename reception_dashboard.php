<?php
session_start();
include "db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != 'receptionist'){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];

$resUser = $conn->query("
    SELECT ReceptionStaff_ID 
    FROM reception_staff 
    WHERE username='$username'
");

$userData = $resUser->fetch_assoc();
$receptionist_id = $userData['ReceptionStaff_ID'];

if(isset($_GET['pay'])){

    $id = intval($_GET['pay']);

    $conn->query("
        UPDATE payment
        SET payment_status='Paid'
        WHERE Booking_ID=$id
    ");

    echo "<script>
        alert('Successfully Paid');
        window.location='reception_dashboard.php';
    </script>";
    exit();
}

if(isset($_GET['unpay'])){

    $id = intval($_GET['unpay']);

    $conn->query("
        UPDATE bookings
        SET payment_status='Unpaid'
        WHERE booking_id=$id
    ");

    header("Location: reception_dashboard.php");
    exit();
}

if(isset($_GET['confirm'])){

    $booking_id = intval($_GET['confirm']);

    $booking = $conn->query("
        SELECT * FROM bookings WHERE booking_id=$booking_id
    ")->fetch_assoc();
	
		if($booking['booking_status'] == 'Confirmed'){
		echo "<script>
			alert('Booking already confirmed.');
			window.location='reception_dashboard.php';
		</script>";
		exit();
	}
	
	$user_id = $booking['user_id'];
	
	$guest = $conn->query("
			SELECT Guest_ID 
			FROM guest 
			WHERE User_ID = $user_id
		")->fetch_assoc();

		$Guest_ID = $guest['Guest_ID'];

		$conn->query("
			UPDATE bookings 
			SET Guest_ID = $Guest_ID 
			WHERE booking_id = $booking_id
		");
		if(!$guest){
			$conn->query("
				INSERT INTO guest (User_ID, first_name, last_name, phone_number, birthdate, email)
				VALUES (
					{$user['User_ID']},
					'{$user['first_name']}',
					'{$user['last_name']}',
					'{$user['phone_number']}',
					'{$user['birthdate']}',
					'{$user['email']}'
				)
			");
		}

    $user_id = $booking['user_id'];

    $user = $conn->query("
        SELECT * FROM users WHERE User_ID=$user_id
    ")->fetch_assoc();


     $conn->query("
        UPDATE bookings
        SET booking_status='Confirmed',
		processed_by = $receptionist_id
        WHERE booking_id=$booking_id
    ");
	
	$conn->query("
		UPDATE rooms
		SET room_status='Reserved'
		WHERE room_id={$booking['room_id']}
	");

    $check = $conn->query("
        SELECT * FROM guest WHERE User_ID=$user_id
    ");

    if($check->num_rows == 0){
        $conn->query("
            INSERT INTO guest
            (User_ID, first_name, last_name, phone_number, birthdate, email)
            VALUES
            (
                {$user['User_ID']},
                '{$user['first_name']}',
                '{$user['last_name']}',
                '{$user['phone_number']}',
                '{$user['birthdate']}',
                '{$user['email']}'
            )
        ");
    }

    header("Location: reception_dashboard.php");
    exit();
}

if(isset($_GET['checkin'])){
    $id = intval($_GET['checkin']);
	
    $conn->query("
    UPDATE bookings
    SET booking_status='Checked In'
    WHERE booking_id=$id
	");

	$conn->query("
		UPDATE rooms
		SET room_status='Occupied'
		WHERE room_id=(	
			FROM bookings
			WHERE booking_id=$id
		)
	");

    echo "<script>
        alert('Guest checked in successfully');
        window.location='reception_dashboard.php';
    </script>";

    exit();
}

if(isset($_GET['checkout'])){

    $id = intval($_GET['checkout']);

    $conn->query("
		UPDATE rooms
		SET room_status='Available'
		WHERE room_id=(
			SELECT room_id
			FROM bookings
			WHERE booking_id=$id
		)
	");

    echo "<script>
        alert('Guest checked out successfully');
        window.location='reception_dashboard.php';
    </script>";

    exit();
}

if(isset($_GET['remove'])){

    $id = intval($_GET['remove']);

    $conn->query("
        DELETE FROM bookings
        WHERE booking_id=$id
    ");

    echo "<script>
        alert('Booking permanently deleted');
        window.location='reception_dashboard.php';
    </script>";

    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>RECEPTIONIST DASHBOARD</title>

<style>
body {
    font-family: Arial;
    background: #121212;
    color: white;
    margin: 0;
    padding: 30px;
	background-image: url('hotel-bg.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

.header {
    background: #1e1e1e;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
	
	
}

.container{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.box {
    background: #1e1e1e;
    padding: 25px;
    border-radius: 10px;

}

.box h3 {
    margin: 0 0 18px 0;
    font-size: 16px;
    color: #ccc;
    border-bottom: 1px solid #333;
    padding-bottom: 12px;
}

.bookings-box{
    grid-column:1 / span 2;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    border: 1px solid #333;
    padding: 10px 14px;
    text-align: left;
}

th {
    background: #222;
    font-size: 13px;
    color: #aaa;
}

td {
    font-size: 14px;
}

button {
    padding: 6px 12px;
    background: #2d89ef;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background: #1a6fcc;
}

.action-btn {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
</style>
</head>

<body>

<div class="header">
    <h2 style="margin:0;">RECEPTIONIST DASHBOARD</h2>
    <a href="logout.php"><button>Logout</button></a>
</div>

<div class="container">

<div class="box bookings-box">
    <h3>Bookings Management</h3>
    <table>
    <tr>
        <th>Booking ID</th>
        <th>Room</th>
        <th>User ID</th>
        <th>Guest ID</th>
        <th>Status</th>
        <th>Payment ID</th>
        <th>Payment</th>
        <th>Payment Status</th>
        <th>Actions</th>
    </tr>

			<?php
			$res = $conn->query("
				SELECT 
					b.*,
					g.Guest_ID,
					p.Payment_ID,
					p.amount_paid,
					p.payment_status
				FROM bookings b
				LEFT JOIN guest g ON g.User_ID = b.user_id
				LEFT JOIN payment p ON b.booking_id = p.Booking_ID
			");

		 while($row = $res->fetch_assoc()){ ?>

		<tr>
			<td><?= $row['booking_id'] ?></td>
			<td><?= $row['room_id'] ?></td>
			<td><?= $row['user_id'] ?></td>
			<td><?= $row['Guest_ID'] ?? 'N/A' ?></td>
			<td><?= $row['booking_status'] ?? 'Pending' ?></td>
			<td><?= $row['Payment_ID'] ?? 'N/A' ?></td>
			<td><?= $row['amount_paid'] ?? 0 ?></td>
			<td><?= $row['payment_status'] ?? 'Unpaid' ?></td>

			<td>

				<div class="action-btn">

				<?php $isCancelled = ($row['booking_status'] == 'Cancelled'); ?>

				<?php if($isCancelled){ ?>

					<button disabled style="background:#555; opacity:0.6;">Confirm</button>
					<button disabled style="background:#555; opacity:0.6;">Check-in</button>
					<button disabled style="background:#555; opacity:0.6;">Check-out</button>
					<button disabled style="background:#555; opacity:0.6;">Pay</button>

				<?php } else { ?>

					<?php if($row['booking_status'] == 'Pending'){ ?>
						<a href="?confirm=<?= $row['booking_id'] ?>">
							<button>Confirm</button>
						</a>
					<?php } else { ?>
						<button disabled style="background:#555; opacity:0.6;">Confirmed</button>
					<?php } ?>

					<a href="?checkin=<?= $row['booking_id'] ?>">
						<button>Check-in</button>
					</a>

					<a href="?checkout=<?= $row['booking_id'] ?>">
						<button>Check-out</button>
					</a>

					<?php if($row['payment_status'] == 'Paid'){ ?>
						<button disabled style="background:#555; opacity:0.6;">Paid</button>
					<?php } else { ?>
						<a href="?pay=<?= $row['booking_id'] ?>">
							<button>Pay</button>
						</a>
					<?php } ?>

				<?php } ?>
				
			<?php } ?>	

				</div>

			</td>
		</tr>		
	</table>		
</div>

<div class="box">
    <h3>Payment Records</h3>
    <table>
        <tr>
			<th>Payment ID</th>
            <th>Booking ID</th>
            <th>Room ID</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
       <?php
       $res = $conn->query("
			SELECT 
				b.booking_id,
				b.room_id,
				b.user_id,
				b.guest_id,
				b.booking_status,
				p.Payment_ID,
				p.amount_paid,
				p.payment_status
			FROM bookings b
			LEFT JOIN payment p ON b.booking_id = p.Booking_ID
		");
        while($row = $res->fetch_assoc()){
		$status = strtolower(trim($row['payment_status']));
		$confirmed = ($row['payment_status'] == 'Confirmed');
		echo "<tr>
			<td>{$row['Payment_ID']}</td>
			<td>{$row['booking_id']}</td>
			<td>{$row['room_id']}</td>
			<td>₱{$row['amount_paid']}</td>
			<td>{$row['payment_status']}</td>
		</tr>";
	}
        ?>
    </table>
</div>

    <div class="box">
    <h3>Room Availability</h3>

    <table>
        <tr>
            <th>Room Type</th>
            <th>Price</th>
            <th>Capacity</th>
            <th>Available</th>
        </tr>

        <?php

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

<div class="box">
    <h3>Guest Records</h3>
    <table>
        <tr>
			<th>Guest ID</th>
            <th>User ID</th>
            <th>Name</th>
			<th>Phone No.</th>
            <th>Email</th>
        </tr>
        <?php
		$res = $conn->query("
			SELECT
				g.Guest_ID,
				g.User_ID,
				g.first_name,
				g.last_name,
				g.phone_number,
				g.email
			FROM guest g
		");
        while($row = $res->fetch_assoc()){
			echo "<tr>
				<td>{$row['Guest_ID']}</td>
				<td>{$row['User_ID']}</td>
				<td>{$row['first_name']} {$row['last_name']}</td>
				<td>{$row['phone_number']}</td>
				<td>{$row['email']}</td>
			</tr>";
        }
		
        ?>
    </table>
</div>

<div class="box">
<h3>Registration Records</h3>

<table>
<tr>
    <th>User ID</th>
    <th>Username</th>
    <th>Password</th>
    <th>Role</th>
    <th>Date Registered</th>
</tr>

<?php
$res = $conn->query("
    SELECT User_ID, username, password, role, created_at
    FROM users
    WHERE role='guest'
");

while($row = $res->fetch_assoc()){
    echo "<tr>
        <td>{$row['User_ID']}</td>
        <td>{$row['username']}</td>
        <td>********</td>
        <td>{$row['role']}</td>
        <td>{$row['created_at']}</td>
    </tr>";
}
?>

</table>
</div>

</body>
</html>