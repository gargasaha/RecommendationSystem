<?php
session_start();
unset($_SESSION['roomId']); 
$sql="delete from room_member where user_id='{$_SESSION['id']}'";
$mysqli = mysqli_connect("localhost", "root", "9932", "devcollab");
if ($mysqli) {
    if (mysqli_query($mysqli, $sql)) {
        mysqli_close($mysqli);
    } else {
        die('<div class="alert alert-danger">Query failed: ' . mysqli_error($mysqli) . '</div>');
    }
} else {
    die('<div class="alert alert-danger">Connection failed: ' . mysqli_connect_error() . '</div>');
}
header("Location: dashboard.php");
exit();
?>