<?php
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        session_start();
        $room_name = $_POST['room_name'];
        $password = $_POST['password'];

        $mysqli = mysqli_connect("localhost", "root", "9932", "devcollab");
        if (!$mysqli) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $sql = "SELECT id FROM room WHERE room_name=? AND room_password=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $room_name, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            die("Query failed: " . mysqli_error($mysqli));
        }
        $row = $result->fetch_assoc();
        if ($row) {
            $room_id = $row["id"];
            $sql="SELECT COUNT(*) FROM room_member WHERE room_id=? AND user_id=?";
            $stmt2 = $mysqli->prepare($sql);
            $stmt2->bind_param("ii", $room_id, $_SESSION['id']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_array();
            if($row2[0]>0){
                $alert = "You are already a member of this room";
            }
            else{
                $sql="INSERT INTO room_member (room_id, user_id) VALUES (?, ?)";
                $stmt3 = $mysqli->prepare($sql);
                $stmt3->bind_param("ii", $room_id, $_SESSION['id']);
                if (!$stmt3->execute()) {
                    $alert = "Error joining room: " . $stmt3->error;
                } else {
                    $_SESSION['roomId'] = $room_id;
                    header("Location: dashboard.php");
                    exit();
                }
            }
        } else {
            $alert = "Room not found or incorrect password.";
        }
        $stmt->close();
        $mysqli->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Room | DevCollab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            background: rgba(255,255,255,0.95);
        }
        .form-control:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 0.2rem rgba(42,82,152,.25);
        }
        .btn-primary {
            background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #2a5298 0%, #1e3c72 100%);
        }
        .brand {
            font-weight: bold;
            font-size: 2rem;
            color: #2a5298;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <span class="brand">DevCollab</span>
                        <h4 class="mt-2">Join a Room</h4>
                    </div>
                    <?php if (!empty($alert)): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($alert); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form action="join_room.php" method="post" autocomplete="off">
                        <div class="mb-3">
                            <label for="room_name" class="form-label">Room Name</label>
                            <input type="text" id="room_name" name="room_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Room Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Join Room</button>
                        </div>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="dashboard.php" class="text-decoration-none text-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>