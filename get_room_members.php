<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['roomId'])) {
    echo json_encode([]);
    exit;
}

$mysqli = new mysqli("localhost", "root", "9932", "devcollab");
if ($mysqli->connect_errno) {
    echo json_encode([]);
    exit;
}

$roomId = intval($_SESSION['roomId']);
$sql = "SELECT users.id, users.username 
        FROM users 
        JOIN room_member ON users.id = room_member.user_id 
        WHERE room_member.room_id = $roomId";

$result = $mysqli->query($sql);
$members = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    $result->free();
}

$mysqli->close();
echo json_encode($members);
