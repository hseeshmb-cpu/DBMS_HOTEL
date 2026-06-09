<?php
include "db.php";
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <style>
        body{
            font-family: Arial;
            margin: 40px;
            background:#121212;
            color:white;
			
        }

        .box{
            width:300px;
            padding:20px;
            background:#1e1e1e;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.5);
        }

        input, select{
            width:95%;
            padding:8px;
            margin:5px 0;
            background:#2a2a2a;
            color:white;
            border:1px solid #444;
            border-radius:5px;
        }

        button{
            width:100%;
            padding:10px;
            background:#2d89ef;
            color:white;
            border:none;
            cursor:pointer;
            border-radius:5px;
            font-weight:bold;
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

        .container{
            display:flex;
            justify-content:center;
            align-items:center;
            height:80vh;
        }

        h1, h2{
            text-align:center;
        }
    </style>
</head>

<body>

<div class="container">

<div class="box">

<h1>COLOVE HOTEL</h1>

<h2>Login</h2>

<form method="POST">

    Username:
    <input type="text" name="username" required>

    Password:
    <input type="password" name="password" required>

    Role:
    <select name="role">
        <option value="guest">Guest</option>
        <option value="admin">Admin</option>
        <option value="receptionist">Receptionist</option>
    </select>

    <button type="submit" name="login">Login</button>

</form>

<br>

<p style="text-align:center;">
    Don't have an account? <a href="signup.php">Sign up</a>
</p>

</div>

</div>

<?php
if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $sql = "SELECT * FROM users 
            WHERE username='$username' 
            AND password='$password' 
            AND role='$role'";

    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){

        $user = $result->fetch_assoc();

        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] == 'admin'){
            header("Location: admin_dashboard.php");
            exit();
        }
        elseif($user['role'] == 'receptionist'){
            header("Location: reception_dashboard.php");
            exit();
        }
        else{
            header("Location: index.php");
            exit();
        }
    }
    else{
        echo "<script>alert('Invalid login credentials');</script>";
    }
}
?>

</body>
</html>