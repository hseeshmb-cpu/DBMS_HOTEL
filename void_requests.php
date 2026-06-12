<?php
include "db.php";
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>Void Requests</title>

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

.box{
    width:400px;
    background:#1e1e1e;
    padding:20px;
    border-radius:10px;
}

input,
textarea,
select{
    width:100%;
    box-sizing:border-box;
    padding:10px;
    margin:10px 0;
    background:#2a2a2a;
    color:white;
    border:1px solid #444;
    border-radius:5px;
    font-size:14px;
}

textarea{
    resize:none;
    min-height:80px;
}

button{
    width:100%;
    padding:10px;
    background:#2d89ef;
    border:none;
    color:white;
    cursor:pointer;
    border-radius:5px;
}

table{
    width:100%;
    margin-top:20px;
    border-collapse:collapse;
}

th, td{
    border:1px solid #333;
    padding:8px;
}

th{
    background:#222;
}

.container{
    display:flex;
    justify-content:center;
    align-items:center;
    height:80vh;
}

select{
    width:100%;
    padding:8px;
    margin:12px 0;
    background:#2a2a2a;
    color:white;
    border:1px solid #444;
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
    <h2>Cancelation Request</h2>

    <a href="index.php">
        <button>Back to Home</button>
    </a>
</div>

<div class="container">

<div class="box">

<form method="POST">

	<label>Select Booking</label>

	<select style="cursor:pointer" name="booking_id" required>
	<?php

	$username = $_SESSION['user'];

	$user = $conn->query("
		SELECT User_ID
		FROM users
		WHERE username='$username'
	")->fetch_assoc();

	$user_id = $user['User_ID'];

	$res = $conn->query("
		SELECT booking_id
		FROM bookings
		WHERE user_id=$user_id
	");

while($row = $res->fetch_assoc()){
    echo "<option value='{$row['booking_id']}'>
            Booking #{$row['booking_id']}
          </option>";
}
?>
</select>
<input type="text" name="guest_name" placeholder="Guest Name" required>

<textarea name="reason" placeholder="Reason" required></textarea>

<button type="submit" name="submit">Submit Request</button>

</form>

</div>

</div>

<?php
if(isset($_POST['submit'])){

    $booking_id = $_POST['booking_id'];
    $guest_name = $_POST['guest_name'];
    $reason = $_POST['reason'];

    $sql = "INSERT INTO void_booking
    (booking_id, guest_name, reason, submitted_at, status)
    VALUES
    ('$booking_id','$guest_name','$reason',NOW(),'Pending')";

    if($conn->query($sql)){
    echo "
    <script>
        alert('Request submitted successfully!');
        window.location='index.php';
    </script>
    ";
    exit();
	}
}
?>

</table>

</body>
</html>