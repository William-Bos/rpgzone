<?php
// Define o servidor do banco de dados (localhost = mesmo servidor)
$host = "localhost";
$user = "will7361_Williambzx";
$pass = "Lbos0932#";
$dbname = "will7361_rpgzone";

// Cria a conexão com o banco usando MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
