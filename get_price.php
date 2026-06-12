<?php
include "db.php";

$room_type = $_GET['room_type'];

$res = $conn->query("SELECT price FROM rooms WHERE room_type='$room_type'");

if($res->num_rows > 0){
    $row = $res->fetch_assoc();
	echo "₱" . $row['price'];
}else{
    echo "0";
}
?>