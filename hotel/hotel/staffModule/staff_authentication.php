<?php
 session_start();
 if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
 header('Location: staff_login.php');
 exit;
 }
 ?>