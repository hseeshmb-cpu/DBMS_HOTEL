<?php
include "db.php";
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>

    <style>
        body{
            margin:0;
            font-family: Arial;
            background:#121212;
            color:white;
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .box{
            width:320px;
            padding:25px;
            background:#1e1e1e;
            border-radius:12px;
            box-shadow:0 0 15px rgba(0,0,0,0.6);
        }

        h2{
            text-align:center;
            margin-bottom:15px;
        }

        input, select{
            width:100%;
            padding:10px;
            margin:6px 0;
            background:#2a2a2a;
            color:white;
            border:1px solid #444;
            border-radius:6px;
        }

        button{
            width:100%;
            padding:10px;
            background:#2d89ef;
            color:white;
            border:none;
            border-radius:6px;
            font-weight:bold;
            cursor:pointer;
            margin-top:10px;
        }

        button:hover{
            background:#1f6fd1;
        }

        a{
            color:#4da6ff;
            text-decoration:none;
        }

        a:hover{
            color:#80c1ff;
        }

        .footer{
            text-align:center;
            margin-top:10px;
        }
    </style>
</head>

<body>

<div class="box">

<h2>Sign Up</h2>

<form method="POST">

    <input type="text" name="username" placeholder="Username" required>

    <input type="password" name="password" placeholder="Password" required>

    <input type="text" name="first_name" placeholder="First Name" required>

    <input type="text" name="last_name" placeholder="Last Name" required>

    <input type="text" name="phone_number" placeholder="Phone Number" required>

    <input type="date" name="birthdate" required>

    <input type="email" name="email" placeholder="Email" required>

    <input type="hidden" name="role" value="guest">

    <button type="submit" name="signup">Create Account</button>

</form>

<div class="footer">
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

</div>

<?php
if(isset($_POST['signup'])){

    $username = $_POST['username'];
    $password = $_POST['password'];
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $phone = $_POST['phone_number'];
    $birth = $_POST['birthdate'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $sql = "INSERT INTO users
    (username, password, first_name, last_name, phone_number, birthdate, role, email)
    VALUES
    ('$username','$password','$first','$last','$phone','$birth','$role','$email')";

    if($conn->query($sql)){
        echo "<script>
            alert('Account created successfully!');
            window.location='login.php';
        </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

</body>
</html>