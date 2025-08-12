<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit;
}

include("../../conexao.php");

$id_mestre = $_SESSION['usuario_id'];

// Consulta campanhas do mestre
$stmt = $conn->prepare("SELECT * FROM campanhas WHERE id_mestre = ?");
$stmt->bind_param("i", $id_mestre);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Página do Mestre</title>
  <link rel="stylesheet" href="inicial.css" />
</head>
<body>

<header>
  <div class="titulo">Mestre</div>
  <nav class="links">
    <a href="../criar/criar_campanha/criar_campanha.php">Criar Campanha</a>
    <a href="logout.php">Sair</a>
  </nav>
</header>

<div class="conteudo">
  <h1>Bem-vindo <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h1>

  <h2>Suas Campanhas</h2>

  <div class="campanhas">
  <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="card">
        <a href="../campanha/campanha.php?id=<?php echo (int)$row['id']; ?>">
          <img src="/rpg/<?php echo htmlspecialchars($row['foto']); ?>" alt="Imagem da Campanha" />
          <h3><?php echo htmlspecialchars($row['nome']); ?></h3>
          <p><?php echo htmlspecialchars($row['descricao']); ?></p>
        </a>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>Você ainda não criou nenhuma campanha.</p>
  <?php endif; ?>
</div>


</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
