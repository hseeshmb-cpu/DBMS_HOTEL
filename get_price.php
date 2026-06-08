<?php
include "db.php";

$room_id = $_GET['room_id'];

$res = $conn->query("SELECT price FROM rooms WHERE room_id='$room_id'");

if($res->num_rows > 0){
    $row = $res->fetch_assoc();
	echo "₱" . $row['price'];
}else{
    echo "0";
}
?>