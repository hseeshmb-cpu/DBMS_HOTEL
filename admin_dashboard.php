<?php
include "db.php";
session_start();

if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$editRoom = null;

if(isset($_GET['edit'])){
    $id = intval($_GET['edit']);
    $editRoom = $conn->query("SELECT * FROM rooms WHERE room_id=$id")->fetch_assoc();
}

if(isset($_GET['maintenance'])){

    $id = intval($_GET['maintenance']);

    $conn->query("
        UPDATE rooms
        SET room_status='Under Maintenance'
        WHERE room_id=$id AND room_status!='Occupied'
    ");

    header("Location: admin_dashboard.php");
    exit();
}

if(isset($_POST['update_price'])){

    $room_id = intval($_POST['room_id']);
    $price = $_POST['price'];

    $conn->query("
        UPDATE rooms
        SET price='$price'
        WHERE room_id=$room_id
    ");

    header("Location: admin_dashboard.php");
    exit();
}

if(isset($_GET['cancel_maintenance'])){

    $id = intval($_GET['cancel_maintenance']);

    $conn->query("
        UPDATE rooms
        SET room_status='Available'
        WHERE room_id=$id AND room_status='Under Maintenance'
    ");

    header("Location: admin_dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>
body{
    font-family:Arial;
    background:#121212;
    color:white;
    margin:0;
    padding:20px;
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
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.container{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.booknreg-box{
    grid-column:1 / span 2;
}

.box{
    background:#1e1e1e;
    padding:15px;
    border-radius:10px;
}

.box form{
    display:flex;
    flex-direction:column;
}

.box select,
.box input{
	padding:8px;
    margin:5px 0;
    width:35%;
	background:#2a2a2a;
    color:white;
    border:1px solid #444;
    border-radius:5px;
}

.box button{
    width:auto;      
    padding:8px 14px;
    margin-top:10px;
    align-self:flex-start;
}

button{
    padding:8px 12px;
    background:#2d89ef;
    color:white;
    border:none;
    border-radius:5px;
	cursor:pointer;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th,td{
    border:1px solid #333;
    padding:8px;
}

th{
    background:#222;
}

.action-btn{
    display:flex;
    gap:5px;
}
</style>
</head>

<body>

<div class="header">
    <h2>ADMIN DASHBOARD</h2>
    <a href="logout.php"><button>Logout</button></a>
</div>

<div class="container">


<div class="box">
<h3>Reports</h3>

<?php
$totalConfirmed = $conn->query("
    SELECT COUNT(*) as total 
    FROM bookings 
    WHERE booking_status='Confirmed'
")->fetch_assoc()['total'];

$totalCancelled = $conn->query("
    SELECT COUNT(*) as total 
    FROM bookings 
    WHERE booking_status='Cancelled'
")->fetch_assoc()['total'];

$totalPending = $conn->query("
    SELECT COUNT(*) as total 
    FROM bookings 
    WHERE booking_status='Pending'
")->fetch_assoc()['total'];

$totalBookings = $conn->query("
    SELECT COUNT(*) as total 
    FROM bookings
")->fetch_assoc()['total'];
?>

 <table>
        <tr>
            <th>Report Type</th>
            <th>Count</th>
        </tr>

        <tr>
            <td>Total Bookings</td>
            <td><?= $totalBookings ?></td>
        </tr>

        <tr>
            <td>Confirmed Bookings</td>
            <td><?= $totalConfirmed ?></td>
        </tr>

        <tr>
            <td>Pending Bookings</td>
            <td><?= $totalPending ?></td>
        </tr>

        <tr>
            <td>Cancelled Bookings</td>
            <td><?= $totalCancelled ?></td>
        </tr>
    </table>

</div>

<div class="box">
<h3>Room Overview</h3>

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

<div class="box booknreg-box">
<h3>Bookings</h3>

<table>
<tr>
<th>Booking ID</th>
<th>Guest ID</th>
<th>No. Guests</th>
<th>Check In</th>
<th>Check Out</th>
<th>Room ID</th>
<th>Room No.</th>
<th>Processed By</th>
<th>Status</th>
</tr>

<?php
$res = $conn->query("
    SELECT
        b.*,
        rs.first_name,
        rs.last_name
    FROM bookings b
    LEFT JOIN reception_staff rs
        ON b.processed_by = rs.ReceptionStaff_ID
");

while($row = $res->fetch_assoc()){

    $staffName = ($row['first_name'])
        ? $row['first_name'] . " " . $row['last_name']
        : "N/A";
		
    echo "<tr>
        <td>{$row['booking_id']}</td>
        <td>{$row['Guest_ID']}</td>
        <td>{$row['num_guests']}</td>
        <td>{$row['check_in_date']}</td>
        <td>{$row['check_out_date']}</td>
        <td>{$row['room_id']}</td>
        <td>{$row['room_number']}</td>
        <td>{$staffName}</td>
        <td>{$row['booking_status']}</td>
    </tr>";
}
?>
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
			<th>Processed By</th>
            <th>Status</th>
        </tr>
       <?php
      $res = $conn->query("
			SELECT
				b.booking_id,
				b.room_id,
				p.Payment_ID,
				p.amount_paid,
				p.payment_status,
				rs.first_name,
				rs.last_name
			FROM bookings b
			LEFT JOIN payment p
				ON b.booking_id = p.Booking_ID
			LEFT JOIN reception_staff rs
				ON p.ReceptionStaff_ID = rs.ReceptionStaff_ID
		");
        while($row = $res->fetch_assoc()){
		$status = strtolower(trim($row['payment_status']));
		$confirmed = ($row['payment_status'] == 'Confirmed');
		$staffName = ($row['first_name'])
		? $row['first_name'] . " " . $row['last_name']
			: "N/A";
		echo "<tr>
			<td>{$row['Payment_ID']}</td>
			<td>{$row['booking_id']}</td>
			<td>{$row['room_id']}</td>
			<td>₱{$row['amount_paid']}</td>
			<td>{$staffName}</td>
			<td>{$row['payment_status']}</td>
		</tr>";
		}
        ?>		
    </table>
</div>	

<div class="box">
<h3>Cancelation Requests</h3>

<table>
<tr>
<th>ID</th>
<th>Booking</th>
<th>Guest</th>
<th>Reason</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM void_booking");

while($row=$res->fetch_assoc()){
    echo "<tr>
    <td>{$row['Void_ID']}</td>
    <td>{$row['booking_id']}</td>
    <td>{$row['guest_name']}</td>
    <td>{$row['reason']}</td>
    <td>{$row['status']}</td>
    <td>";

if($row['status'] == 'Pending'){

    echo "
    <a href='admin_dashboard.php?approve={$row['Void_ID']}'><button>Approve</button></a>

    <a href='admin_dashboard.php?reject={$row['Void_ID']}'><button>Reject</button></a>
    ";
}

echo "
    <a href='admin_dashboard.php?remove={$row['Void_ID']}'
       onclick='return confirm(\"WARNING: This will permanently remove the void request. Are you sure you want to Continue?\")'>
       <button>Remove</button>
    </a>

    </td>
</tr>";
}
?>
</table>

<?php

if(isset($_GET['approve'])){

    $id = intval($_GET['approve']);

    $void = $conn->query("
        SELECT booking_id
        FROM void_booking
        WHERE Void_ID = $id
    ");

    if(!$void || $void->num_rows == 0){
        die("ERROR: Void request not found or invalid ID");
    }

    $void = $void->fetch_assoc();
    $booking_id = (int)$void['booking_id'];

    if($booking_id <= 0){
        die("ERROR: Invalid booking ID inside void request");
    }

    $conn->query("
        UPDATE bookings
        SET booking_status='Cancelled'
        WHERE booking_id=$booking_id
    ");
	
	$conn->query("
		UPDATE rooms
		SET room_status='Available'
		WHERE room_id=(
			SELECT room_id
			FROM bookings
			WHERE booking_id=$booking_id
		)
	");
	
	$username = $_SESSION['user'];

	$admin = $conn->query("
		SELECT User_ID
		FROM users
		WHERE username='$username'
	")->fetch_assoc();

	$admin_id = $admin['User_ID'];

    $conn->query("
        UPDATE void_booking
        SET status='Approved',
			processedby_admin=$admin_id
        WHERE Void_ID=$id
    ");

    echo "<script>
        alert('Void request approved.');
        window.location='admin_dashboard.php';
    </script>";
    exit();
}
if(isset($_GET['reject'])){

    $id = intval($_GET['reject']);

    $username = $_SESSION['user'];

    $admin = $conn->query("
        SELECT User_ID
        FROM users
        WHERE username='$username'
    ")->fetch_assoc();

    $admin_id = $admin['User_ID'];

    $conn->query("
        UPDATE void_booking
        SET status='Rejected',
            processedby_admin=$admin_id
        WHERE Void_ID=$id
    ");

    echo "<script>
        alert('Void request rejected.');
        window.location='admin_dashboard.php';
    </script>";
    exit();
}

if(isset($_GET['remove'])){

    $id = (int)$_GET['remove'];

    $conn->query("
        DELETE FROM void_booking
        WHERE Void_ID=$id
    ");

    echo "
    <script>
        alert('Void request removed successfully.');
        window.location='admin_dashboard.php';
    </script>
    ";
    exit();
}
?>

</div>


<div class="box">
<h3>Manage Rooms</h3>

<?php
$types = $conn->query("
    SELECT DISTINCT room_type 
    FROM rooms
    ORDER BY 
        CASE room_type
            WHEN 'Standard Single' THEN 1
            WHEN 'Standard Double' THEN 2
            WHEN 'Deluxe Room' THEN 3
            WHEN 'Family Room' THEN 4
            WHEN 'Suite' THEN 5
        END
");
?>

<?php while($type = $types->fetch_assoc()) { 
    $roomType = $type['room_type'];
	
?>

<?php $edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0; ?>
    
<div style="margin-bottom:15px; background:#1e1e1e; padding:10px; border-radius:10px;">

    <details>
        <summary style="cursor:pointer; font-weight:bold;">
            <?= $roomType ?>
        </summary>

        <table style="margin-top:10px;">
            <tr>
                <th>Room No.</th>
                <th>Price</th>
                <th>Capacity</th>
				<th>Status</th>
                <th>Action</th>
            </tr>

            <?php
            $rooms = $conn->query("
                SELECT * FROM rooms 
                WHERE room_type='$roomType'
                ORDER BY room_number ASC
            ");
			
				while($row = $rooms->fetch_assoc()){ ?>
				<tr>
					<?php if(isset($_GET['edit']) && $_GET['edit'] == $row['room_id']) { ?>
					<tr>
						<td colspan="5">
							<div style="background:#2a2a2a; padding:10px; border-radius:8px;" id="editBox">

								<form method="POST">

									<input type="hidden" name="room_id" value="<?= $row['room_id'] ?>">

									<label>Room Price:</label><br>

									<input type="number" name="price" value="<?= $row['price'] ?>" required>

									<button type="submit" name="update_price">
										Save Price
									</button>

									<a href="admin_dashboard.php">
										<button type="button" style="background:2a2a2a;">
											Cancel
										</button>
									</a>

								</form>

							</div>
						</td>
					</tr>
					<?php } ?>
				
					<td><?= $row['room_number'] ?></td>
					<td>₱<?= $row['price'] ?></td>
					<td><?= $row['capacity'] ?></td>
					<td><?= $row['room_status'] ?></td>
						<td>
						
						<div class="action-btn">
							<a href='admin_dashboard.php?edit=<?= $row['room_id'] ?>#editBox'>
								<button>Edit</button>
							</a>
					
						<?php if($row['room_status'] != 'Occupied'){ ?>

							<?php if($row['room_status'] == 'Under Maintenance'){ ?>

								<a href='admin_dashboard.php?cancel_maintenance=<?= $row['room_id'] ?>'>
									<button>Cancel</button>
								</a>

							<?php } else { ?>

								<a href='admin_dashboard.php?maintenance=<?= $row['room_id'] ?>'>
									<button>Maintenance</button>
								</a>

							<?php } ?>

						<?php } else { ?>

							<button disabled style="background:#555; opacity:0.6;">Occupied</button>

						<?php } ?>
							
					</td>			
				</tr>
			<?php } ?>
        </table>

    </details>

</div>

<?php } ?>

</div>



<div class="box">
<h3>Add Reception Staff</h3>

<form method="POST">
<input type="text" name="first_name" placeholder="First Name" required>
<input type="text" name="last_name" placeholder="Last Name" required>
<input type="date" name="birthdate" required>
<input type="email" name="email" placeholder="Email" required>
<input type="text" name="phone_number" placeholder="Phone Number" required>
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>

<button name="add_staff">Add Staff</button>
</form>

<?php
if(isset($_POST['add_staff'])){

    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $birthdate = $_POST['birthdate'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn->query("
        INSERT INTO users
        (
            first_name,
            last_name,
            email,
            phone_number,
            birthdate,
            username,
            password,
            role
        )
        VALUES
        (
            '$fname',
            '$lname',
            '$email',
            '$phone',
            '$birthdate',
            '$username',
            '$password',
            'receptionist'
        )
    ");
		$conn->query("
		INSERT INTO reception_staff
		(User_ID, first_name, last_name, phone_number, birthdate, username)
		VALUES
		(LAST_INSERT_ID(),'$fname','$lname','$phone','$birthdate','$username')
	");

    echo "<script>
        alert('Reception staff added');
        window.location='admin_dashboard.php';
    </script>";
}
?>
</div>

<div class="box booknreg-box">
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
    WHERE role IN ('receptionist', 'guest')
    ORDER BY 
        CASE 
            WHEN role = 'receptionist' THEN 1
            WHEN role = 'guest' THEN 2
        END,
        created_at DESC
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

</div>

</body>
</html>