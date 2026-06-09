<?php
session_start();
include "db.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bookings</title>
	
	<style>
	body{
		font-family:Arial;
		background:#121212;
		color:white;
		padding:10px;
	}
	
	.form-container{
		display:flex;
		justify-content:center;
		margin-top:30px;
	}
	
	.form-box{
		width:400px;
		padding:30px;
		background:#1e1e1e;
		border-radius:10px;
	}
	
	.form-box input,
	.form-box select,
	.form-box textarea{
		width:100%;
		box-sizing:border-box;
		padding:10px;
		margin:8px 0 12px 0;
		background:#2a2a2a;
		color:white;
		border:1px solid #444;
		border-radius:5px;
		outline:none;
		font-size:14px;
	}
	
	#room_price{
		background:#1a1a1a;
		color:#00ff99;
		font-weight:bold;
	}

	.box{
		background:#1e1e1e;
		padding:10px;
		border-radius:10px;
		margin-bottom:20px;
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

	a{text-decoration:none;}
	</style>

</head>

<body style="margin:0; font-family:Arial; background:#121212; color:white;">

<div class="section-header">
    <h2>Bookings</h2>

    <a href="index.php">
        <button>Back to Home</button>
    </a>
	</div>

<div class="form-container">

<div class="form-box">

<form method="POST">

    <label>Room ID</label>

	<select name="room_id" id="room_id" required onchange="getPrice()">

    <option value="" disabled selected>Select Room</option>

    <?php
    $rooms = $conn->query("
        SELECT room_id, room_type, available
        FROM rooms
        WHERE available > 0
    ");

    while($room = $rooms->fetch_assoc()){
        echo "
        <<option value='{$room['room_id']}'>
		Room {$room['room_id']} - {$room['room_type']}
		({$room['available']} Available)
		</option>";
    }
    ?>

    <label>Room Price</label>
    <input type="text" id="room_price" readonly

    <label>Number of Guests</label>
    <input type="number" name="num_guests" min="1" required>

    <label>Check In</label>
	<input type="date" name="in" id="checkin" required onchange="limitCheckout()">

	<label>Check Out</label>
	<input type="date" name="out" id="checkout" required>

    <label>Payment Method</label>
    <select name="payment_method" required>
        <option>Bank Transfer</option>
        <option>Credit Card</option>
        <option>GCash</option>
		<option>Cash</option>
    </select>

    <label>Amount Paid</label>
    <input type="number" name="amount_paid" required>

    <button type="submit" name="book">Book Now</button>

</form>

<?php
if(isset($_POST['book'])){

    $user = $_SESSION['user'];
    $room = $_POST['room_id'];
    $in = $_POST['in'];
    $out = $_POST['out'];
	$start = new DateTime($in);
	$end = new DateTime($out);

	$diff = $start->diff($end)->days;

	if($diff < 1 || $diff > 7){
		echo "<script>
			alert('You can only book between 1 to 7 days.');
			window.location='bookings.php';
		</script>";
    exit();
}
    $num_guests = $_POST['num_guests'];
    $payment_method = $_POST['payment_method'];
    $amount_paid = $_POST['amount_paid'];
	
		$checkRoom = $conn->query("
		SELECT available
		FROM rooms
		WHERE room_id='$room'
	");

	$roomInfo = $checkRoom->fetch_assoc();

	if($roomInfo['available'] <= 0){

		echo "<script>
		alert('This room is fully booked.');
		window.location='bookings.php';
		</script>";

		exit();
	}

    // GET ROOM PRICE FIRST
    $res = $conn->query("SELECT price FROM rooms WHERE room_id='$room'");
    $roomData = $res->fetch_assoc();
    $price = $roomData['price'];

    $conn->query("
        INSERT INTO bookings
        (user_id, room_id, check_in_date, check_out_date, num_guests, total_price, booking_status)
        VALUES
        (
            (SELECT User_ID FROM users WHERE username='$user'),
            '$room',
            '$in',
            '$out',
            '$num_guests',
            '$price',
            'Pending'

        )	
		
    ");
	$booking_id = $conn->insert_id;

	$conn->query("
		INSERT INTO payment
		(Booking_ID, payment_method, amount_paid, payment_status, transaction_date)
		VALUES
		($booking_id, '$payment_method', '$amount_paid', 'Unpaid', NOW())
	");

    echo "<script>alert('Booking successful!'); window.location='bookings.php';</script>";
}
?>

</div>
</div>

<script>
function getPrice(){
    let room_id = document.getElementById("room_id").value;

    if(room_id === ""){
        document.getElementById("room_price").value = "";
        return;
    }

    fetch("get_price.php?room_id=" + room_id)
    .then(res => res.text())
    .then(data => {
        document.getElementById("room_price").value = data;
    });
}
</script>

<script>
function limitCheckout(){

    let checkin = document.getElementById("checkin").value;

    if(!checkin) return;

    let start = new Date(checkin);

    let min = new Date(start);
    min.setDate(min.getDate() + 1);

    let max = new Date(start);
    max.setDate(max.getDate() + 7);

    let checkout = document.getElementById("checkout");

    checkout.min = min.toISOString().split('T')[0];
    checkout.max = max.toISOString().split('T')[0];
}
</script>

</body>
</html>