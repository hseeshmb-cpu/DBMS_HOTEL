<?php
session_start();
include "db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != 'receptionist'){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];

$resUser = $conn->query("
    SELECT User_ID 
    FROM users 
    WHERE username='$username'
");

$userData = $resUser->fetch_assoc();
$receptionist_id = $userData['User_ID'];

if(isset($_GET['confirm'])){
    $booking_id = intval($_GET['confirm']);
    $conn->query("UPDATE bookings SET booking_status='Confirmed', processed_by=$receptionist_id WHERE booking_id=$booking_id");
    header("Location: reception_dashboard.php");
    exit();
}

if(isset($_GET['checkin'])){
    $id = intval($_GET['checkin']);
    $conn->query("UPDATE bookings SET booking_status='Checked In' WHERE booking_id=$id");
    header("Location: reception_dashboard.php");
    exit();
}

if(isset($_GET['checkout'])){
    $id = intval($_GET['checkout']);
    $conn->query("UPDATE bookings SET booking_status='Checked Out' WHERE booking_id=$id");
    header("Location: reception_dashboard.php");
    exit();
}

if(isset($_GET['pay'])){
    $id = intval($_GET['pay']);
    $conn->query("UPDATE bookings SET proof_of_payment='Paid Receipt' WHERE booking_id=$id");
    echo "<script>alert('Payment marked as PAID.'); window.location='reception_dashboard.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reception Dashboard</title>

<style>
body {
    font-family: Arial;
    background: #121212;
    color: white;
    margin: 0;
    padding: 20px;
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

.box {
    background: #1e1e1e;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.box h3 {
    margin: 0 0 18px 0;
    font-size: 16px;
    color: #ccc;
    border-bottom: 1px solid #333;
    padding-bottom: 12px;
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
    <h2 style="margin:0;">Reception Dashboard</h2>
    <a href="logout.php"><button>Logout</button></a>
</div>

<!-- Booking Management -->
<div class="box">
    <h3>Bookings Management</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Room</th>
            <th>User</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Actions</th>
        </tr>
        <?php
        $res = $conn->query("SELECT * FROM bookings");
        while($row = $res->fetch_assoc()){
            echo "<tr>
                <td>{$row['booking_id']}</td>
                <td>{$row['room_id']}</td>
                <td>{$row['user_id']}</td>
                <td>{$row['booking_status']}</td>
                <td>{$row['amount_paid']}</td>
                <td>
                    <div class='action-btn'>
                        <a href='reception_dashboard.php?confirm={$row['booking_id']}'><button>Confirm</button></a>
                        <a href='reception_dashboard.php?checkin={$row['booking_id']}'><button>Check-in</button></a>
                        <a href='reception_dashboard.php?checkout={$row['booking_id']}'><button>Check-out</button></a>
                        <a href='reception_dashboard.php?pay={$row['booking_id']}'><button>Pay</button></a>
                    </div>
                </td>
            </tr>";
        }
        ?>
    </table>
</div>

<!-- Guest Records -->
<div class="box">
    <h3>Guest Records</h3>
    <table>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
        </tr>
        <?php
        $res = $conn->query("
            SELECT DISTINCT u.User_ID, u.first_name, u.last_name, u.email
            FROM users u
            INNER JOIN bookings b ON u.User_ID = b.user_id
            WHERE u.role = 'guest'
            AND b.booking_status = 'Confirmed'
        ");
        while($row = $res->fetch_assoc()){
            echo "<tr>
                <td>{$row['User_ID']}</td>
                <td>{$row['first_name']} {$row['last_name']}</td>
                <td>{$row['email']}</td>
            </tr>";
        }
        ?>
    </table>
</div>

<!-- Void Requests -->
<div class="box">
    <h3>Void Requests</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Booking</th>
            <th>Guest</th>
            <th>Reason</th>
            <th>Status</th>
        </tr>
        <?php
        $res = $conn->query("SELECT * FROM void_booking");
        while($row = $res->fetch_assoc()){
            echo "<tr>
                <td>{$row['Void_ID']}</td>
                <td>{$row['booking_id']}</td>
                <td>{$row['guest_name']}</td>
                <td>{$row['reason']}</td>
                <td>{$row['status']}</td>
            </tr>";
        }
        ?>
    </table>
</div>

<!-- Payment Records -->
<div class="box">
    <h3>Payment Records</h3>
    <table>
        <tr>
            <th>Booking ID</th>
            <th>Room ID</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
        <?php
        $res = $conn->query("
            SELECT b.booking_id, b.room_id, b.booking_status, r.price
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.room_id
        ");
        while($row = $res->fetch_assoc()){
            $amount = $row['price'] ? $row['price'] : 0;
            echo "<tr>
                <td>{$row['booking_id']}</td>
                <td>{$row['room_id']}</td>
                <td>₱{$amount}</td>
                <td>{$row['booking_status']}</td>
            </tr>";
        }
        ?>
    </table>
</div>

</body>
</html>