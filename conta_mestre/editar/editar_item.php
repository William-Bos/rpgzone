<?php
session_start();
include("../../conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$id_item = intval($_POST['id_item']);
$id_campanha = intval($_POST['id_campanha']);

$nome = $_POST['nome'];
$descricao = $_POST['descricao'];
$ca = $_POST['ca'];
$efeito = $_POST['efeito'];
$dano = $_POST['dano'];
$buff = $_POST['buff'];
$debuff = $_POST['debuff'];

$foto = null;
if (!empty($_FILES['foto']['name'])) {
    $foto = 'uploads/' . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], '../../' . $foto);
}

$sql = "UPDATE itens SET nome=?, descricao=?, ca=?, efeito=?, dano=?, buff=?, debuff=?";

if ($foto) {
    $sql .= ", foto=?";
}

$sql .= " WHERE id=?";

$stmt = $conn->prepare($sql);

if ($foto) {
    $stmt->bind_param("ssisssssi", $nome, $descricao, $ca, $efeito, $dano, $buff, $debuff, $foto, $id_item);
} else {
    $stmt->bind_param("ssissssi", $nome, $descricao, $ca, $efeito, $dano, $buff, $debuff, $id_item);
}

if ($stmt->execute()) {
    header("Location: ../campanha/campanha.php?id=$id_campanha");
} else {
    echo "Erro ao atualizar item.";
}

$stmt->close();
$conn->close();
?>
