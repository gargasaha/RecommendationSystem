<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Real-time Collaboration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            min-height: 100vh;
        }
        .dashboard-container {
            max-width: 850px;
            margin: 48px auto;
            background: linear-gradient(120deg, #ffffff 60%, #f3e7e9 100%);
            border-radius: 22px;
            box-shadow: 0 10px 40px rgba(80, 60, 120, 0.18);
            padding: 48px 36px 36px 36px;
            border: 2px solid #e0c3fc;
        }
        .welcome {
            font-weight: 800;
            color: #6a11cb;
            letter-spacing: 1px;
            font-size: 2rem;
        }
        .room-title {
            color: #2575fc;
            font-weight: 700;
            letter-spacing: 1px;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-custom {
            min-width: 220px;
            font-size: 1.15rem;
            margin-bottom: 18px;
            font-weight: 600;
            border-radius: 30px;
            box-shadow: 0 4px 16px rgba(106,17,203,0.10);
            transition: transform 0.12s, box-shadow 0.12s;
        }
        .btn-primary.btn-custom {
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            border: none;
        }
        .btn-success.btn-custom {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
            border: none;
            color: #222;
        }
        .btn-custom:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 8px 24px rgba(80, 60, 120, 0.18);
        }
        .iframe-container {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(80, 60, 120, 0.10);
            margin-top: 28px;
            border: 2px solid #a18cd1;
            background: #f8fafc;
        }
        .btn-outline-danger {
            border-radius: 20px;
            font-weight: 600;
            border-width: 2px;
        }
        @media (max-width: 600px) {
            .dashboard-container {
                padding: 20px 8px;
            }
            .welcome {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="welcome mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
        <?php
        $sql="select * from room_member where user_id='{$_SESSION['id']}'";
        $mysqli = mysqli_connect("localhost", "root", "9932", "devcollab");
        if (!$mysqli) {
            die('<div class="alert alert-danger">Connection failed: ' . mysqli_connect_error() . '</div>');
        }
        $result = mysqli_query($mysqli, $sql);
        if (!$result) {
            die('<div class="alert alert-danger">Query failed: ' . mysqli_error($mysqli) . '</div>');
        }
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_close($mysqli);
        if (!isset( $row['room_id'])) {
        ?>
            
            <div class="text-center mt-5">
                <a href="create_room.php" class="btn btn-primary btn-lg btn-custom me-3 shadow">Create a New Room</a>
                <a href="join_room.php" class="btn btn-success btn-lg btn-custom shadow">Join an Existing Room</a>
            </div>
        <?php
        } else {
            $_SESSION["roomId"] = $row['room_id'];
            $mysqli = mysqli_connect("localhost", "root", "9932", "devcollab");
            if (!$mysqli) {
                die('<div class="alert alert-danger">Connection failed: ' . mysqli_connect_error() . '</div>');
            }
            $sql = "SELECT room_name FROM room WHERE id='" . mysqli_real_escape_string($mysqli, $_SESSION['roomId']) . "'";
            $result = mysqli_query($mysqli, $sql);
            if (!$result) {
                die('<div class="alert alert-danger">Query failed: ' . mysqli_error($mysqli) . '</div>');
            }
            $row = mysqli_fetch_assoc($result);
            echo '<div class="d-flex align-items-center justify-content-between mb-4">';
            echo '<h3 class="room-title mb-0">Room: ' . htmlspecialchars($row['room_name']) . '</h3>';
            echo '<a href="leave_room.php" class="btn btn-outline-danger btn-custom ms-3">Leave Room</a>';
            echo '</div>';
            mysqli_free_result($result);
            mysqli_close($mysqli);
        ?>
            <div class="iframe-container position-relative" style="overflow:hidden;">
                <iframe 
                    src="index.php" 
                    style="width:100%; height:70vh; border:none; background: #f8fafc; overflow:hidden;" 
                    allowfullscreen 
                    loading="lazy"
                    title="Collaboration Room"
                    id="collab-iframe"
                    scrolling="no"
                ></iframe>
                <button 
                    type="button" 
                    class="btn btn-light position-absolute top-0 end-0 m-2 shadow-sm" 
                    onclick="document.getElementById('collab-iframe').requestFullscreen();"
                    title="Fullscreen"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrows-fullscreen" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 1v5h1V2.707l4.146 4.147.708-.708L2.707 2H6V1H1zm14 0h-5v1h3.293l-4.147 4.146.708.708L13.293 2H10V1h5zm-1 14v-5h-1v3.293l-4.146-4.147-.708.708L13.293 14H10v1h5zm-14 0h5v-1H2.707l4.147-4.146-.708-.708L2 13.293V10H1v5z"/>
                    </svg>
                </button>
            </div>
        <?php
        }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
