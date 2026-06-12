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
		padding:20px;
		background-image: url('hotel-bg.jpg');
		background-size: cover;
		background-position: center;
		background-repeat: no-repeat;
		background-attachment: fixed;
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
    <h2>Bookings</h2>

    <a href="index.php">
        <button>Back to Home</button>
    </a>
	</div>

<div class="form-container">

<div class="form-box">

<form method="POST">

    <label>Room Type</label>

	<select name="room_type" id="room_type" required onchange="getPrice()">
		<option value="" disabled selected>Select Room Type</option>

		<?php
		$order = "
			CASE room_type
				WHEN 'Standard Single' THEN 1
				WHEN 'Standard Double' THEN 2
				WHEN 'Deluxe Room' THEN 3
				WHEN 'Family Room' THEN 4
				WHEN 'Suite' THEN 5
				ELSE 6
			END
		";
		
		$room_types = $conn->query("
			SELECT
				r.room_type,

				(
					COUNT(r.room_id)
					-
					COALESCE((
						SELECT COUNT(*)
						FROM bookings b
						JOIN rooms r2 ON b.room_id = r2.room_id
						WHERE r2.room_type = r.room_type
						AND b.booking_status IN ('Confirmed','Checked In')
					),0)
				) AS available

			FROM rooms r
			GROUP BY r.room_type
			HAVING available > 0
			ORDER BY $order
		");

		while($room_type = $room_types->fetch_assoc()){
			echo "
			<option value='{$room_type['room_type']}'>
				{$room_type['room_type']} ({$room_type['available']} Available)
			</option>";
		}
		?>
	</select>

    <label>Room Price</label>
    <input type="text" id="room_price" readonly>

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

    <label>Amount</label>
    <input type="number" name="amount_paid" required>

    <button type="submit" name="book">Book Now</button>

</form>

<?php
if(isset($_POST['book'])){

	$user = $_SESSION['user'];

	$user_id = $conn->query("
		SELECT User_ID 
		FROM users 
		WHERE username='$user'
	")->fetch_assoc()['User_ID'];
	
	$guest = $conn->query("
		SELECT guest_id 
		FROM guest 
		WHERE User_ID = $user_id
		LIMIT 1
	")->fetch_assoc();

	if(!$guest){
		die("ERROR: Guest record not found for this user");
	}

	$guest_id = $guest['guest_id'];
	
	$room_type = $_POST['room_type'];
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
	
		$roomData = $conn->query("
			SELECT room_id, price
			FROM rooms
			WHERE room_type='$room_type'
			LIMIT 1
		")->fetch_assoc();

		$room_id = $roomData['room_id'];
		$price = $roomData['price'];

	$conn->query("
		INSERT INTO bookings
		(user_id, guest_id, room_id, check_in_date, check_out_date, num_guests, total_price, booking_status)
		VALUES
		($user_id, $guest_id, $room_id, '$in', '$out', '$num_guests', '$price', 'Pending')
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
    let room_type = document.getElementById("room_type").value;

    if(room_type === ""){
        document.getElementById("room_price").value = "";
        return;
    }

    fetch("get_price.php?room_type=" + room_type)
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