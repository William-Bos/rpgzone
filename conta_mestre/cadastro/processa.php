<?php
include("../../conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Verifica se email já existe na tabela usuarios
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Este email já está cadastrado!";
    } else {
        // Insere usuário com tipo 'mestre'
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'mestre')");
        $stmt->bind_param("sss", $nome, $email, $senha_hash);

        if ($stmt->execute()) {
            echo "<script>
                alert('Cadastro realizado com sucesso! Bem-vindo, " . addslashes(htmlspecialchars($row['nome'])) . "');
                window.location.href = '../login/login_mestre.html'; // Página pós-login
              </script>";
        } else {
            echo "Erro ao cadastrar: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
