<?php
include "db.php";
session_start();

if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
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
}

.header{
    background:#1e1e1e;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}

.container{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
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
<h3>Add Room</h3>

<form method="POST">

<select name="room_type" required>
    <option value="">Select Room Type</option>
    <option>Standard Single</option>
    <option>Standard Double</option>
    <option>Deluxe Room</option>
    <option>Family Room</option>
    <option>Suite</option>
</select>

<input type="number" name="price" placeholder="Price" required>
<input type="number" name="capacity" placeholder="Capacity" required>

<button name="add_room">Add Room</button>
</form>

<?php
if(isset($_POST['add_room'])){
    $type = $_POST['room_type'];
    $price = $_POST['price'];
    $cap = $_POST['capacity'];

    $conn->query("INSERT INTO rooms(room_type,price,capacity,available)
    VALUES('$type','$price','$cap',1)");

    echo "<script>alert('Room Added');window.location='admin_dashboard.php';</script>";
}
?>
</div>


<div class="box">
<h3>Reports</h3>

<?php
$totalRooms = $conn->query("SELECT COUNT(*) as c FROM rooms")->fetch_assoc()['c'];
$totalBookings = $conn->query("SELECT COUNT(*) as c FROM bookings")->fetch_assoc()['c'];
$pendingBookings = $conn->query("SELECT COUNT(*) as c FROM bookings WHERE booking_status='Pending'")->fetch_assoc()['c'];
?>

<p>Total Rooms: <b><?= $totalRooms ?></b></p>
<p>Total Bookings: <b><?= $totalBookings ?></b></p>
<p>Pending Bookings: <b><?= $pendingBookings ?></b></p>

</div>


<div class="box">
<h3>Manage Rooms</h3>

<table>
<tr>
<th>ID</th>
<th>Type</th>
<th>Price</th>
<th>Capacity</th>
<th>Action</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM rooms");

while($row=$res->fetch_assoc()){
    echo "<tr>
        <td>{$row['room_id']}</td>
        <td>{$row['room_type']}</td>
        <td>₱{$row['price']}</td>
        <td>{$row['capacity']}</td>
        <td>
            <a href='admin_dashboard.php?edit={$row['room_id']}'><button>Edit</button></a>
            <a href='admin_dashboard.php?delete_room={$row['room_id']}'><button>Delete</button></a>
        </td>
    </tr>";
}
?>
</table>

<?php

if(isset($_GET['delete_room'])){

    $id = (int) $_GET['delete_room'];

    $check = $conn->query("SELECT 1 FROM bookings WHERE room_id=$id LIMIT 1");

    if($check->num_rows > 0){
        echo "<script>
        alert('Cannot delete room. It is currently used in bookings.');
        window.location='admin_dashboard.php';
        </script>";
        exit();
    }

    $conn->query("DELETE FROM rooms WHERE room_id=$id");

    echo "<script>
    alert('Room deleted successfully.');
    window.location='admin_dashboard.php';
    </script>";
    exit();
}
?>

<?php

$edit = null;

if(isset($_GET['edit'])){
    $id = (int) $_GET['edit'];
    $edit = $conn->query("SELECT * FROM rooms WHERE room_id=$id")->fetch_assoc();
}
?>

<?php if($edit){ ?>
<div class="box" id="edit">
<h3>Edit Room Price</h3>

<form method="POST">

    <input type="hidden" name="room_id" value="<?= $edit['room_id'] ?>">

    <label>Room Type</label>
    <input type="text" value="<?= $edit['room_type'] ?>" disabled>

    <label>Price</label>
    <input type="number" name="price" value="<?= $edit['price'] ?>" required>

    <button type="submit" name="update_price">
        Save Changes
    </button>

    <a href="admin_dashboard.php">
        <button type="button">Cancel</button>
    </a>

</form>
</div>
<?php } ?>

<?php
if(isset($_POST['update_price'])){

    $id = (int) $_POST['room_id'];
    $price = $_POST['price'];

    $conn->query("
        UPDATE rooms 
        SET price='$price'
        WHERE room_id=$id
    ");

    echo "<script>
        alert('Price updated successfully');
        window.location='admin_dashboard.php';
    </script>";
    exit();
}
?>

</div>


<div class="box">
<h3>Bookings</h3>

<table>
<tr>
<th>ID</th>
<th>User</th>
<th>Room</th>
<th>Status</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM bookings");

while($row=$res->fetch_assoc()){
    echo "<tr>
        <td>{$row['booking_id']}</td>
        <td>{$row['user_id']}</td>
        <td>{$row['room_id']}</td>
        <td>{$row['booking_status']}</td>
    </tr>";
}
?>
</table>
</div>


<div class="box">
<h3>Void Requests</h3>

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
        <td>
            <a href='admin_dashboard.php?approve={$row['Void_ID']}'><button>Approve</button></a>
            <a href='admin_dashboard.php?reject={$row['Void_ID']}'><button>Reject</button></a>
        </td>
    </tr>";
}
?>
</table>

<?php
if(isset($_GET['approve'])){
    $id = $_GET['approve'];
    $conn->query("UPDATE void_booking SET status='Approved' WHERE Void_ID=$id");
	$booking = $conn->query("
    SELECT room_id
    FROM bookings
    WHERE booking_id = (
        SELECT booking_id
        FROM void_booking
        WHERE Void_ID=$id
    )
")->fetch_assoc();

$room_id = $booking['room_id'];

$conn->query("
    UPDATE rooms
    SET available = available + 1
    WHERE room_id=$room_id
");
    echo "<script>window.location='admin_dashboard.php';</script>";
}

if(isset($_GET['reject'])){
    $id = $_GET['reject'];
    $conn->query("UPDATE void_booking SET status='Rejected' WHERE Void_ID=$id");
    echo "<script>window.location='admin_dashboard.php';</script>";
}
?>

</div>


<div class="box">
<h3>Add Reception Staff</h3>

<form method="POST">
<input type="text" name="first_name" placeholder="First Name" required>
<input type="text" name="last_name" placeholder="Last Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>

<button name="add_staff">Add Staff</button>
</form>

<?php
if(isset($_POST['add_staff'])){
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn->query("INSERT INTO users(first_name,last_name,email,username,password,role)
    VALUES('$fname','$lname','$email','$username','$password','receptionist')");

    echo "<script>
    alert('Reception staff added');
    window.location='admin_dashboard.php';
    </script>";
}
?>
</div>

</div>

</body>
</html>