<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit;
}

include("../../conexao.php");

if (!isset($_GET['id'])) {
    echo "Campanha não encontrada.";
    exit;
}

$id_campanha = intval($_GET['id']);
$id_mestre = $_SESSION['usuario_id'];

// Verifica se a campanha pertence ao mestre logado
$stmt = $conn->prepare("SELECT * FROM campanhas WHERE id = ? AND id_mestre = ?");
$stmt->bind_param("ii", $id_campanha, $id_mestre);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Campanha não encontrada ou você não tem permissão para acessá-la.";
    exit;
}

$campanha = $result->fetch_assoc();
$stmt->close();

// Busca itens da campanha
$stmt_itens = $conn->prepare("SELECT * FROM itens WHERE id_campanha = ?");
$stmt_itens->bind_param("i", $id_campanha);
$stmt_itens->execute();
$result_itens = $stmt_itens->get_result();

// Busca habilidades da campanha
$stmt_habilidades = $conn->prepare("SELECT * FROM habilidades WHERE id_campanha = ?");
$stmt_habilidades->bind_param("i", $id_campanha);
$stmt_habilidades->execute();
$result_habilidades = $stmt_habilidades->get_result();

// Busca personagens da campanha (nova parte)
$stmt_personagens = $conn->prepare("SELECT * FROM personagens WHERE id_campanha = ?");
$stmt_personagens->bind_param("i", $id_campanha);
$stmt_personagens->execute();
$result_personagens = $stmt_personagens->get_result();


$stmt_carteira = $conn->prepare("SELECT * FROM carteira WHERE id_campanha = ?");
$stmt_carteira->bind_param("i", $id_campanha);
$stmt_carteira->execute();
$result_carteira = $stmt_carteira->get_result();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title><?php echo htmlspecialchars($campanha['nome']); ?> - Campanha</title>
    <link rel="stylesheet" href="campanha.css" />
</head>
<body>

<header>
    <div class="titulo"><?php echo htmlspecialchars($campanha['nome']); ?></div>
    <nav class="links">
        <a href="../criar/criar_itens/criar_itens.php?id=<?php echo $id_campanha; ?>">Criar Itens</a>
        <a href="../criar/criar_habilidades/criar_habilidades.php?id=<?php echo $id_campanha; ?>">Criar Habilidades</a>
        <a href="../criar/criar_personagem/criar_personagem.php?id=<?php echo $id_campanha; ?>">Criar Personagens</a>
        <a href="../criar/criar_sistema_monetario/index.php?id=<?php echo $id_campanha; ?>">Criar Sistema monetário</a>
        <a href="../inicial/inicial.php">Início</a>
        <a href="../inicial/logout.php">Sair</a>
    </nav>
</header>

<div class="conteudo">
    <section class="info-campanha">
        <img src="/rpg/<?php echo htmlspecialchars($campanha['foto']); ?>" alt="Imagem da Campanha" />
        <h1><?php echo htmlspecialchars($campanha['nome']); ?></h1>
        <p><?php echo htmlspecialchars($campanha['descricao']); ?></p>
    </section>

    <section class="secao">
        <h2>Itens da Campanha</h2>

        <?php if ($result_itens->num_rows > 0): ?>
            <div class="cards">
                <?php while($item = $result_itens->fetch_assoc()): ?>
                    <div class="card" onclick='abrirModal(<?php echo json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                        <img src="../../fotos_itens/<?php echo htmlspecialchars($item['foto']); ?>" alt="Imagem do Item" />

                        <h3><?php echo htmlspecialchars($item['nome']); ?></h3>
                        <p><strong>Descrição:</strong> <?php echo htmlspecialchars($item['descricao']); ?></p>

                        <?php if (!empty($item['efeito'])): ?>
                            <p><strong>Efeito:</strong> <?php echo htmlspecialchars($item['efeito']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['dano'])): ?>
                            <p><strong>Dano:</strong> <?php echo htmlspecialchars($item['dano']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['ca'])): ?>
                            <p><strong>CA:</strong> <?php echo htmlspecialchars($item['ca']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['buff'])): ?>
                            <p><strong>Buff:</strong> <?php echo htmlspecialchars($item['buff']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['debuff'])): ?>
                            <p><strong>Debuff:</strong> <?php echo htmlspecialchars($item['debuff']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Nenhum item cadastrado ainda.</p>
        <?php endif; ?>
    </section>

    <section class="secao">
        <h2>Habilidades da campanha</h2>

        <?php if ($result_habilidades && $result_habilidades->num_rows > 0): ?>
            <div class="cards">
                <?php while($habilidade = $result_habilidades->fetch_assoc()): ?>
                    <div class="card" onclick='abrirModalHabilidade(<?php echo json_encode($habilidade, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                        <img src="/rpg/fotos_habilidades/<?php echo htmlspecialchars($habilidade['foto']); ?>" alt="Imagem da Habilidade" />

                        <h3><?php echo htmlspecialchars($habilidade['nome']); ?></h3>
                        <p><strong>Descrição:</strong> <?php echo htmlspecialchars($habilidade['descricao']); ?></p>

                        <?php if (!empty($habilidade['efeito'])): ?>
                            <p><strong>Efeito:</strong> <?php echo htmlspecialchars($habilidade['efeito']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($habilidade['dano'])): ?>
                            <p><strong>Dano:</strong> <?php echo htmlspecialchars($habilidade['dano']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($habilidade['buff'])): ?>
                            <p><strong>Buff:</strong> <?php echo htmlspecialchars($habilidade['buff']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($habilidade['debuff'])): ?>
                            <p><strong>Debuff:</strong> <?php echo htmlspecialchars($habilidade['debuff']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($habilidade['custo'])): ?>
                            <p><strong>Custo:</strong> <?php echo htmlspecialchars($habilidade['custo']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Nenhuma habilidade cadastrada ainda.</p>
        <?php endif; ?>
    </section>

    <!-- Seção nova de personagens -->
    <section class="secao">
        <h2>Personagens da Campanha</h2>

        <?php if ($result_personagens && $result_personagens->num_rows > 0): ?>
            <div class="cards">
                <?php while($personagem = $result_personagens->fetch_assoc()): ?>
                    <div class="card">
                        <a href="../ficha/ficha_personagem.php?id=<?php echo $personagem['id']; ?>" style="text-decoration:none; color:inherit;">
                            <?php if (!empty($personagem['foto'])): ?>
                                <img src="../../fotos_personagens/<?php echo htmlspecialchars($personagem['foto']); ?>" alt="Foto do Personagem" />
                            <?php else: ?>
                                <img src="/rpg/img/default_avatar.png" alt="Sem Foto" />
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($personagem['nome']); ?></h3>
                            <p><strong>Classe:</strong> <?php echo htmlspecialchars($personagem['classe']); ?></p>
                            <p><strong>Raça:</strong> <?php echo htmlspecialchars($personagem['raca']); ?></p>
                            <p><strong>Nível:</strong> <?php echo htmlspecialchars($personagem['nivel']); ?></p>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Nenhum personagem cadastrado ainda.</p>
        <?php endif; ?>
    </section>

    <section class="secao">
        <h2>Moedas da campanha</h2>
        
            <?php if ($result_carteira && $result_carteira->num_rows > 0): ?>
                <div class="cards">
                    <?php while($carteira = $result_carteira->fetch_assoc()): ?>
                        <div class="card" onclick='abrirModalMoeda(<?php echo json_encode($carteira, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                            
                            
                                <h3><?php echo htmlspecialchars($carteira['nome_moeda']); ?></h3>
                                <p><strong>Valor Base:</strong> <?php echo htmlspecialchars($carteira['valor_base']); ?></p>

                
                        </div>
                    <?php endwhile; ?>
               
            <?php else: ?>
        </div>    
            <p>Nenhum personagem cadastrado ainda.</p>
        <?php endif; ?>
    </section>

</div>

<!-- Modal de edição -->
<div id="modal" class="modal" style="display:none;">
    <div class="modal-conteudo">
        <span class="fechar" onclick="fecharModal()">&times;</span>
        <h2>Editar Item</h2>
        <form id="formEditar" method="POST" action="../editar/editar_item.php" enctype="multipart/form-data">
            <input type="hidden" name="id_item" id="id_item" />
            <input type="hidden" name="id_campanha" value="<?php echo $id_campanha; ?>" />

            <label>Nome:</label>
            <input type="text" name="nome" id="nome" required />

            <label>Descrição:</label>
            <textarea name="descricao" id="descricao" required></textarea>

            <label>CA:</label>
            <input type="number" name="ca" id="ca" />

            <label>Efeito:</label>
            <input type="text" name="efeito" id="efeito" />

            <label>Dano:</label>
            <input type="text" name="dano" id="dano" />

            <label>Buff:</label>
            <input type="text" name="buff" id="buff" />

            <label>Debuff:</label>
            <input type="text" name="debuff" id="debuff" />

            <label>Imagem (opcional):</label>
            <input type="file" name="foto" />

            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<div id="modalHabilidade" class="modal" style="display:none;">
    <div class="modal-conteudo">
        <span class="fechar" onclick="fecharModalHabilidade()">&times;</span>
        <h2>Editar Habilidade</h2>
        <form id="formEditarHabilidade" method="POST" action="../editar/editar_habilidade.php" enctype="multipart/form-data">
            <input type="hidden" name="id_habilidade" id="id_habilidade" />
            <input type="hidden" name="id_campanha" value="<?php echo $id_campanha; ?>" />

            <label>Nome:</label>
            <input type="text" name="nome" id="nome_habilidade" required />

            <label>Descrição:</label>
            <textarea name="descricao" id="descricao_habilidade" required></textarea>

            <label>Efeito:</label>
            <input type="text" name="efeito" id="efeito_habilidade" />

            <label>Dano:</label>
            <input type="text" name="dano" id="dano_habilidade" />

            <label>Buff:</label>
            <input type="text" name="buff" id="buff_habilidade" />

            <label>Debuff:</label>
            <input type="text" name="debuff" id="debuff_habilidade" />

            <label>Custo:</label>
            <input type="text" name="custo" id="custo_habilidade" />

            <label>Imagem (opcional):</label>
            <input type="file" name="foto" />

            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<div id="modalMoeda" class="modal" style="display:none;">
    <div class="modal-conteudo">
        <span class="fechar" onclick="fecharModalMoeda()">&times;</span>

        <h2>Editar Moeda</h2>
        <form id="formEditar" method="POST" action="../editar/editar_moeda.php" enctype="multipart/form-data">
            <input type="hidden" name="id_carteira" id="id_carteira" />
            <input type="hidden" name="id_campanha" value="<?php echo $id_campanha; ?>" />

            <label>Nome Moeda:</label>
            <input type="text" name="nome_moeda" id="nome_moeda" required />

            <label>Valor Moeda:</label>
            <input type="number" name="valor_base" id="valor_base" required></input>

           

            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<script src="campanha.js"></script>

</body>
</html>

<?php
$stmt_itens->close();
$stmt_habilidades->close();
$stmt_personagens->close();
$conn->close();
?>
