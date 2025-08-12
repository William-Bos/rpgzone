<?php
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
    $nome = $_POST['nome'];
    $ca = $_POST['ca'];
    $descricao = $_POST['descricao'];
    $efeito = $_POST['efeito'];
    $dano = $_POST['dano'];
    $buff = $_POST['buff'];
    $debuff = $_POST['debuff'];

    // Upload da imagem
    $foto = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $pasta = '../../../fotos_itens/';
        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = uniqid() . "." . $extensao;
        $caminho = $pasta . $nome_arquivo;

        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extensao, $extensoes_permitidas)) {
            echo "<script>
                    alert('Extensão de arquivo não permitida. Envie JPG, PNG ou GIF.');
                    window.history.back();
                  </script>";
            exit;
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
            $foto = $nome_arquivo;
        } else {
            echo "<script>
                    alert('Erro ao fazer upload da imagem.');
                    window.history.back();
                  </script>";
            exit;
        }
    }

    // Inserir no banco
    $stmt = $conn->prepare("INSERT INTO itens 
        (id_campanha, nome, foto, ca, descricao, efeito, dano, buff, debuff) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ississsss", 
        $id_campanha, $nome, $foto, $ca, $descricao, $efeito, $dano, $buff, $debuff
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
    <h1>Criar Novo Item</h1>

    <form method="POST" enctype="multipart/form-data">
        <label>Foto do Item:</label>
        <input type="file" name="foto" accept="image/*" required><br>

        <label>Nome do Item:</label>
        <input type="text" name="nome" required><br>

        <label>Classe de Armadura (CA):</label>
        <input type="number" name="ca"><br>

        <label>Descrição:</label>
        <textarea name="descricao" required></textarea><br>

        <label>Efeito:</label>
        <textarea name="efeito"></textarea><br>

        <label>Dano (Ex.: 1d8, 2d6+3):</label>
        <input type="text" name="dano"><br>

        <label>Buff (Ex.: +2 Defesa):</label>
        <input type="text" name="buff"><br>

        <label>Debuff (Ex.: -1 Velocidade):</label>
        <input type="text" name="debuff"><br>

        <button type="submit">Criar Item</button>
    </form>
</div>

</body>
</html>
