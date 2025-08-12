<?php
session_start();
include("../../conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$id_habilidade = intval($_POST['id_habilidade']);
$id_campanha = intval($_POST['id_campanha']);

$nome = $_POST['nome'];
$descricao = $_POST['descricao'];
$efeito = $_POST['efeito'];
$dano = $_POST['dano'];
$buff = $_POST['buff'];
$debuff = $_POST['debuff'];
$custo = $_POST['custo'];

$foto = null;
if (!empty($_FILES['foto']['name'])) {
    $diretorio = '../../uploads/';
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $nome_arquivo = time() . '_' . basename($_FILES['foto']['name']);
    $foto = 'uploads/' . $nome_arquivo;
    move_uploaded_file($_FILES['foto']['tmp_name'], $diretorio . $nome_arquivo);
}

if ($foto) {
    $sql = "UPDATE habilidades SET nome=?, descricao=?, efeito=?, dano=?, buff=?, debuff=?, custo=?, foto=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssi",
        $nome,
        $descricao,
        $efeito,
        $dano,
        $buff,
        $debuff,
        $custo,
        $foto,
        $id_habilidade
    );
} else {
    $sql = "UPDATE habilidades SET nome=?, descricao=?, efeito=?, dano=?, buff=?, debuff=?, custo=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssi",
        $nome,
        $descricao,
        $efeito,
        $dano,
        $buff,
        $debuff,
        $custo,
        $id_habilidade
    );
}

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header("Location: ../campanha/campanha.php?id=$id_campanha");
        exit;
    } else {
        echo "Nenhuma habilidade foi atualizada. Verifique se o ID está correto ou se os dados são os mesmos.";
    }
} else {
    echo "Erro ao atualizar habilidade: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
