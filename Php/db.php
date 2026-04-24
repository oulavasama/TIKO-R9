<?php
$conn = pg_connect("host=localhost dbname=DBNAME user=DBUSER password=DBPASSWORD");

if (!$conn) {
    die("Database connection failed");
}
?>