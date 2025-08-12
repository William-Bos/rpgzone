<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/login.php");
    exit;
}

include("../../../conexao.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $id_mestre = $_SESSION['usuario_id'];

    // Alterado para salvar na pasta fotos_campanha
    $diretorio = __DIR__ . "/../../../fotos_campanha/";

    // Cria a pasta se não existir
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0755, true);
    }

    $arquivo = $_FILES['foto'];

    // Pega a extensão correta do arquivo enviado
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    // Gera nome único com a extensão correta
    $nome_arquivo = uniqid() . "." . $extensao;

    $caminho_arquivo = $diretorio . $nome_arquivo;

    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($extensao, $extensoes_permitidas)) {
        echo "<script>
                alert('Extensão de arquivo não permitida. Envie JPG, PNG ou GIF.');
                window.history.back();
              </script>";
        exit;
    }

    if (move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
        // Atualizado para refletir a pasta fotos_campanha
        $caminho_banco = "fotos_campanha/" . $nome_arquivo;

        $stmt = $conn->prepare("INSERT INTO campanhas (id_mestre, foto, nome, descricao) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_mestre, $caminho_banco, $nome, $descricao);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Campanha criada com sucesso!');
                    window.location.href = '../../inicial/inicial.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Erro ao criar a campanha: " . addslashes($stmt->error) . "');
                    window.history.back();
                  </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
                alert('Erro ao fazer upload da imagem.');
                window.history.back();
              </script>";
    }

    $conn->close();

} else {
    header("Location: criar_campanha.php");
    exit;
}
?>
