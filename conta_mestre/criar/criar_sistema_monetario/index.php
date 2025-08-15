<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/login.php");
    exit;
}

include("../../../conexao.php");

// Verifica se recebeu o ID da campanha
if (!isset($_GET['id'])) {
    echo "Campanha não encontrada.";
    exit;
}

$id_campanha = intval($_GET['id']);

// Processamento do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_moeda = $_POST['nome_moeda'];
    $valor_base = $_POST['valor_base'];
    

    // Upload da imagem


    // Inserir no banco
    $stmt = $conn->prepare("INSERT INTO carteiras
        (id_campanha, nome_moeda, valor_base) 
        VALUES (?, ?, ?)");

    $stmt->bind_param("isi", 
        $id_campanha, $nome_moeda, $valor_base
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Item criado com sucesso!');
                window.location.href = '../../campanha/campanha.php?id=$id_campanha';
              </script>";
        exit;
    } else {
        echo "Erro ao criar item: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Item</title>
    <link rel="stylesheet" href="criar_itens.css">
</head>
<body>

<header>
    <div class="titulo">Criar Item</div>
    <nav class="links">
        <a href="../../campanha/campanha.php?id=<?php echo $id_campanha; ?>">Voltar para Campanha</a>
        <a href="../../inicial/inicial.php">Início</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<div class="conteudo">
    <h1>Criar Nova Moeda</h1>

    <form method="POST" enctype="multipart/form-data">


        <label>Nome da Moeda:</label>
        <input type="text" name="nome_moeda" required><br>

        <label>Valor da moeda:</label>
        <input type="number" name="valor_base"><br>


        <button type="submit">Criar Item</button>
    </form>
</div>

</body>
</html>
