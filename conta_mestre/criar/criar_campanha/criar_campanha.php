<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Criar Campanha</title>
  <link rel="stylesheet" href="criar_campanha.css" />
</head>
<body>

<header>
  <div class="header-left">Mestre</div>
  <nav class="header-right">
    <a href="../../inicial/inicial.php">Início</a>
    
    <a href="logout.php">Sair</a>
  </nav>
</header>

<div class="container">
  <h2>Criar Nova Campanha</h2>

  <form action="processa.php" method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label for="foto">Foto da Campanha:</label>
      <input type="file" id="foto" name="foto" accept="image/*" required />
    </div>

    <div class="form-group">
      <label for="nome">Nome da Campanha:</label>
      <input type="text" id="nome" name="nome" maxlength="100" required />
    </div>

    <div class="form-group">
      <label for="descricao">Descrição:</label>
      <textarea id="descricao" name="descricao" rows="5" maxlength="500" required></textarea>
    </div>

    <button type="submit">Criar Campanha</button>
  </form>
</div>

</body>
</html>
