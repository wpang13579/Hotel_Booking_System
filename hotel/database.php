<?php

// servername, username, password, database_name
$con = mysqli_connect("localhost", "root", "", "hotel_db");

if (mysqli_connect_errno()) {
    echo "Failed connect to MysSQL: " . mysqli_connect_errno();
}
