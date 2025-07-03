<?php
$mysqli = new mysqli("localhost", "root", "9932", "devcollab");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$code = $_POST['code'] ?? '';
$session_id = 1;

$sql = "UPDATE code_sessions SET content = '$code', last_updated = NOW() WHERE id = $session_id";
$mysqli->query($sql);

echo "Code saved.";
$mysqli->close();
