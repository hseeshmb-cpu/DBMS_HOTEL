<?php
include "db.php";
session_start();

if($_SESSION['role'] != 'receptionist'){
    header("Location: login.php");
    exit();
}

// ❌ BLOCK GUESTS
if($_SESSION['role'] != 'admin'){
    echo "<script>
        alert('Access denied! Admin only.');
        window.location='index.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reception Staff</title>

<style>
body{
    font-family:Arial;
    background:#121212;
    color:white;
    margin:0;
    padding:30px;
}

.box{
    width:400px;
    background:#1e1e1e;
    padding:20px;
    border-radius:10px;
}

input{
    width:100%;
    padding:8px;
    margin:6px 0;
    background:#2a2a2a;
    color:white;
    border:1px solid #444;
    border-radius:5px;
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
</style>
</head>

<body>

<h2>Reception Staff Management</h2>

<div class="box">

<form method="POST">

<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<input type="text" name="first_name" placeholder="First Name" required>
<input type="text" name="last_name" placeholder="Last Name" required>
<input type="text" name="phone_number" placeholder="Phone Number" required>
<input type="date" name="birthdate" required>
<input type="email" name="email" placeholder="Email" required>

<button type="submit" name="add">Add Reception Staff</button>

</form>

</div>

<?php
if(isset($_POST['add'])){

    $username = $_POST['username'];
    $password = $_POST['password'];
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $phone = $_POST['phone_number'];
    $birth = $_POST['birthdate'];
    $email = $_POST['email'];

    $sql = "INSERT INTO users
    (username,password,first_name,last_name,phone_number,birthdate,role,email)
    VALUES
    ('$username','$password','$first','$last','$phone','$birth','receptionist','$email')";

    if($conn->query($sql)){
        echo "<script>alert('Reception staff added!'); window.location='reception_staff.php';</script>";
    }
}
?>

<h3>Existing Staff</h3>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM users WHERE role='receptionist'");
while($row=$res->fetch_assoc()){
    echo "<tr>
        <td>{$row['User_ID']}</td>
        <td>{$row['first_name']} {$row['last_name']}</td>
        <td>{$row['email']}</td>
        <td>{$row['role']}</td>
    </tr>";
}
?>

</table>

</body>
</html>