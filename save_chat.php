<?php
    if($_REQUEST==$_POST){
        $mysqli = mysqli_connect("localhost", "root", "9932", "devcollab");
        if (!$mysqli) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $fromId=$_POST["fromId"];
        $toId=$_POST["toId"];
        $roomId=$_POST["roomId"];
        $message=$_POST["message"];
        $timestamp=date("Y-m-d H:i:s");
        $sql = "insert into user_messages (fromId,message,roomId,tm,toId) values ('$fromId','$message','$roomId','$timestamp','$toId')";
        if (mysqli_query($mysqli, $sql)) {
            mysqli_close($mysqli);
            echo "Message saved successfully";
        } else {
            die("Error: " . mysqli_error($mysqli));
        }
    }
?>