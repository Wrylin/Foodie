<?php

    $serverName = "LAPTOP-67MFNV2V\SQLEXPRESS";
    $connectionInfo = array(
        "Database" => "food website",
        "UID" => "",
        "PWD" => ""
    );

    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn) {
        //echo "Connection is successful.<br>";
    } else {
        echo "Connection failed.<br>";
        die(print_r(sqlsrv_errors(), true));
    }

?>