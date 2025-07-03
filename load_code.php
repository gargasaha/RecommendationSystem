<?php
$mysqli = new mysqli("localhost", "root", "9932", "devcollab");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$session_id = 1;
$result = $mysqli->query("SELECT content FROM code_sessions WHERE id = $session_id");

if ($row = $result->fetch_assoc()) {
    echo $row['content'];
}

$mysqli->close();
