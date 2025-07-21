<?php
 session_start();
 if(session_destroy())
 {
 header("Location: staff_login.php");
 exit();
 }
 ?>