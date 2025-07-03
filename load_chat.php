<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$conn = mysqli_connect('localhost', 'root', '9932', 'devcollab');
if (mysqli_connect_errno()) {
    die('Connection failed: ' . mysqli_connect_error());
}
$sql = 'SELECT * FROM user_messages WHERE roomId=' . intval($_SESSION['roomId']) . ' ORDER BY tm ASC';
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['fromId'] == $_SESSION['id']) {
            if ($row['toId'] != 0) {
                $userSql = 'SELECT * FROM users WHERE id=' . intval($row['toId']);
                $userResult = $conn->query($userSql);
                $userRow = $userResult ? $userResult->fetch_assoc() : null;
                $toUsername = $userRow ? htmlspecialchars($userRow['username']) : 'Unknown';
                echo '<div class="message from-me" style="text-align: right;">
                    <div style="display:inline;background-color:rgb(189, 31, 31); padding: 5px; border-radius: 5px;">
                        <strong>Me to ' . $toUsername . '</strong>: ' . htmlspecialchars($row['message']) . ' <small>[' . htmlspecialchars($row['tm']) . '1]</small>
                    </div>
                </div>';
            } else {
                echo '<div class="message from-me" style="text-align: right;"><strong>Me to All</strong>: ' . htmlspecialchars($row['message']) . ' <small>[' . htmlspecialchars($row['tm']) . '2]</small></div>';
            }
        } 
        else {
            $userSql = 'SELECT * FROM users WHERE id=' . intval($row['fromId']);
            $userResult = $conn->query($userSql);
            $userRow = $userResult ? $userResult->fetch_assoc() : null;
            $fromUsername = $userRow ? htmlspecialchars($userRow['username']) : 'Unknown';
            if (intval($row['toId']) === 0) {
                echo '<div class="message from-them"><strong>' . $fromUsername . ' to All</strong>: ' . htmlspecialchars($row['message']) . ' <small>[' . htmlspecialchars($row['tm']) . '3]</small></div>';
            } else if (intval($row['toId']) == $_SESSION['id']) {
                echo '<div class="message from-them"><strong>' . $fromUsername . ' to Me</strong>: ' . htmlspecialchars($row['message']) . ' <small>[' . htmlspecialchars($row['tm']) . '4]</small></div>';
            }
        }
    }
} else {
    echo '<div class="error">Error loading messages: ' . htmlspecialchars($conn->error) . '</div>';
}

$conn->close();
