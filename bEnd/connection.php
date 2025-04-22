<?php
$host = "localhost";
$port = "1521";
$sid = "xe"; // or your specific SID/service name
$db_username = "system";
$db_password = "123";

$tns = "(DESCRIPTION =
    (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))
    (CONNECT_DATA =
      (SID = $sid)
    )
)";

$conn = oci_connect($db_username, $db_password, $tns);

if (!$conn) {
    $e = oci_error();
    die("Connection failed: " . htmlentities($e['message'], ENT_QUOTES));
}
?>
