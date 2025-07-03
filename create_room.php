<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        session_start();
        $room_name = $_POST['room_name'];
        $password = $_POST['password'];

        $mysqli = mysqli_connect("localhost", "root", "9932", "devcollab");
        if (!$mysqli) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $query = "SELECT count(*) as count FROM room WHERE room_name = '" . mysqli_real_escape_string($mysqli, $room_name) . "'";
        $result = mysqli_query($mysqli, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($mysqli));
        }
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
        mysqli_free_result($result);

        if (isset($count) && $count > 0) {
            echo "<script>alert('Room already exists');</script>";
        } else {
            $stmt = mysqli_prepare($mysqli, "INSERT INTO room (room_name, room_password) VALUES (?, ?)");
            if (!$stmt) {
                die("Prepare failed: " . mysqli_error($mysqli));
            }
            mysqli_stmt_bind_param($stmt, "ss", $room_name, $password);
            if (mysqli_stmt_execute($stmt)) {
                $sql="select id from room where room_name='$room_name' and room_password='$password'";
                $result = mysqli_query($mysqli, $sql);
                if (!$result) {
                    die("Query failed: " . mysqli_error($mysqli));
                }
                $row = mysqli_fetch_assoc($result);
                mysqli_free_result($result);
                $_SESSION['roomId'] = $row['id'];
                mysqli_stmt_close($stmt);
                $sql="insert into room_member (room_id, user_id) values ('{$row['id']}', '{$_SESSION['id']}')";
                if (!mysqli_query($mysqli, $sql)) {
                    echo "<script>alert('Error adding member to room: " . mysqli_error($mysqli) . "');</script>";
                }
                mysqli_close($mysqli);
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<script>alert('Error creating room: " . mysqli_stmt_error($stmt) . "');</script>";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($mysqli);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="create_room.php" method="post">
        <label for="room_name">Room Name:</label>
        <input type="text" id="room_name" name="room_name" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Create Room</button>
    </form>
</body>
</html>