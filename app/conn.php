<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbABEDEPORT = "ABEDEPORT";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbABEDEPORT", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Conectado con exito";
} catch(PDOException $e) {
  echo "Conección fallida: " . $e->getMessage();
}
?>