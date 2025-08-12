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
    $descricao = $_POST['descricao'];
    $efeito = $_POST['efeito'];
    $dano = $_POST['dano'];
    $buff = $_POST['buff'];
    $debuff = $_POST['debuff'];
    $custo = $_POST['custo'];
    $criado_em = date('Y-m-d H:i:s');

    // Upload da imagem
    $foto = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $pasta = '../../../fotos_habilidades/';
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
    $stmt = $conn->prepare("INSERT INTO habilidades 
        (id_campanha, nome, foto, descricao, efeito, dano, buff, debuff, custo, criado_em) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ississssss", 
        $id_campanha, $nome, $foto, $descricao, $efeito, $dano, $buff, $debuff, $custo, $criado_em
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Habilidade criada com sucesso!');
                window.location.href = '../../campanha/campanha.php?id=$id_campanha';
              </script>";
        exit;
    } else {
        echo "Erro ao criar habilidade: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Habilidade</title>
    <link rel="stylesheet" href="criar_habilidades.css">
</head>
<body>

<header>
    <div class="titulo">Criar Habilidade</div>
    <nav class="links">
        <a href="../../campanha/campanha.php?id=<?php echo $id_campanha; ?>">Voltar para Campanha</a>
        <a href="../../inicial/inicial.php">Início</a>
        <a href="../../login/logout.php">Sair</a>
    </nav>
</header>

<div class="conteudo">
    <h1>Criar Nova Habilidade</h1>

    <form method="POST" enctype="multipart/form-data">
        <label>Imagem da Habilidade:</label>
        <input type="file" name="foto" accept="image/*" required>

        <label>Nome da Habilidade:</label>
        <input type="text" name="nome" required>

        <label>Descrição:</label>
        <textarea name="descricao" rows="3" required></textarea>

        <label>Efeito:</label>
        <textarea name="efeito" rows="2"></textarea>

        <label>Dano (Ex.: 1d8, 2d6+3):</label>
        <input type="text" name="dano">

        <label>Buff (Ex.: +2 Defesa):</label>
        <input type="text" name="buff">

        <label>Debuff (Ex.: -1 Velocidade):</label>
        <input type="text" name="debuff">

        <label>Custo:</label>
        <input type="text" name="custo">

        <button type="submit">Criar Habilidade</button>
    </form>
</div>

</body>
</html>
