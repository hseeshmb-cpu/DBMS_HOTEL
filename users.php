<?php include "db.php"; ?>

<h2>Guests</h2>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Phone</th>
<th>Email</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM users WHERE role='guest'");

while($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['User_ID']}</td>
        <td>{$row['first_name']} {$row['last_name']}</td>
        <td>{$row['phone_number']}</td>
        <td>{$row['email']}</td>
    </tr>";
}
?>
</table>