<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include("../../conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST['nome'];
    $senha = $_POST['senha'];

    // Busca o usuário pelo nome, e que seja mestre
    $stmt = $conn->prepare("SELECT id, nome, senha, tipo FROM usuarios WHERE nome = ? AND tipo = 'mestre'");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Usuário não encontrado ou não é mestre
        echo "<script>
                alert('Usuário mestre não encontrado!');
                window.location.href = 'login_mestre.html'; // Ajuste o nome do arquivo HTML
              </script>";
        exit;
    }

    $row = $result->fetch_assoc();
    $senha_hash = $row['senha'];

    if (password_verify($senha, $senha_hash)) {
        // Login OK
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['usuario_nome'] = $row['nome'];
        $_SESSION['usuario_tipo'] = $row['tipo'];

        echo "<script>
                alert('Login realizado com sucesso! Bem-vindo, " . addslashes(htmlspecialchars($row['nome'])) . "');
                window.location.href = '../inicial/inicial.php'; // Página pós-login
              </script>";
        exit;
    } else {
        // Senha incorreta
        echo "<script>
                alert('Senha incorreta! Tente novamente.');
                window.location.href = 'login_mestre.html';
              </script>";
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
