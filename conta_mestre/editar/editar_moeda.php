<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include("../../conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$id_campanha = intval($_POST['id_campanha']);
$nome_moeda = $_POST['nome_moeda'];
$valor_base = floatval($_POST['valor_base']); // garantir que seja float

// Faltava o WHERE para indicar qual registro atualizar
// Supondo que você tenha um campo id_moeda para identificar a moeda a ser atualizada,
// esse id deve ser enviado via POST, por exemplo $_POST['id_moeda']
$id_carteira = intval($_POST['id_carteira']); 

$sql = "UPDATE carteiras SET nome_moeda = ?, valor_base = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação da query: " . $conn->error);
}

// O bind_param precisa dos tipos e das variáveis passadas
// "s" para string, "d" para double, "i" para inteiro
$stmt->bind_param("sdi", $nome_moeda, $valor_base, $id_carteira);

if ($stmt->execute()) {
    header("Location: ../campanha/campanha.php?id=$id_campanha");
} else {
    echo "Erro ao atualizar Moeda: " . $stmt->error;
}
?>
