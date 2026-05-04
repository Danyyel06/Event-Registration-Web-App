<?php
echo "<h2>Debug Info</h2>";

echo "MYSQLHOST: " . getenv('MYSQLHOST') . "<br>";
echo "MYSQLPORT: " . getenv('MYSQLPORT') . "<br>";
echo "MYSQLUSER: " . getenv('MYSQLUSER') . "<br>";
echo "MYSQLDATABASE: " . getenv('MYSQLDATABASE') . "<br>";
echo "DB_HOST constant: " . DB_HOST . "<br><br>";

phpinfo();
?>