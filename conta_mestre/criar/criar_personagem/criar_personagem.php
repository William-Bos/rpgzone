<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/login_mestre.html");
    exit;
}
$usuario_id = $_SESSION['usuario_id'];

include("../../../conexao.php");

// Verifica se recebeu o ID da campanha via GET
if (!isset($_GET['id'])) {
    echo "Campanha não informada.";
    exit;
}

$id_campanha = intval($_GET['id']);

// Buscar moedas para o select da carteira
$stmt_moedas = $conn->prepare("SELECT * FROM carteiras WHERE id_campanha = ?");
$stmt_moedas->bind_param("i", $id_campanha);
$stmt_moedas->execute();
$result_moedas = $stmt_moedas->get_result();
$carteira = [];
while ($row = $result_moedas->fetch_assoc()) {
    $carteira[] = $row;
}
$stmt_moedas->close();

// Processa o formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Dados do personagem
    $nome_personagem = $_POST['nome_personagem'];
    $idade_personagem = intval($_POST['idade_personagem']);
    $classe_personagem = $_POST['classe_personagem'];
    $inspiracao_personagem = $_POST['inspiracao_personagem'];
    $raca_personagem = $_POST['raca_personagem'];
    $ca_personagem = intval($_POST['ca_personagem']);
    $nivel_personagem = intval($_POST['nivel_personagem']);
    $tier_aura_personagem = $_POST['tier_aura_personagem'];
    $tier_magico_personagem = $_POST['tier_magico_personagem'];
    $historia_personagem = $_POST['historia_personagem'];

    // Status
    $mana_atual = intval($_POST['mana_atual']);
    $mana_maxima = intval($_POST['mana_maxima']);
    $vigor_atual = intval($_POST['vigor_atual']);
    $vigor_maximo = intval($_POST['vigor_maximo']);
    $sanidade_atual = intval($_POST['sanidade_atual']);
    $sanidade_maxima = intval($_POST['sanidade_maxima']);
    $hp_atual = intval($_POST['hp_atual']);
    $hp_maximo = intval($_POST['hp_maximo']);

    // Upload da imagem
    $foto_personagem = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $pasta = '../../../fotos_personagens/';
        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = uniqid() . "." . $extensao;
        $caminho = $pasta . $nome_arquivo;

        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extensao, $extensoes_permitidas)) {
            echo "<script>alert('Extensão de arquivo não permitida. Envie JPG, PNG ou GIF.');history.back();</script>";
            exit;
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
            $foto_personagem = $nome_arquivo;
        } else {
            echo "<script>alert('Erro ao fazer upload da imagem.');history.back();</script>";
            exit;
        }
    }

    // Inserir personagem
    $stmt = $conn->prepare("INSERT INTO personagens
    (nome, idade, classe, inspiracao, raca, ca, nivel, tier_aura, tier_magico, historia, foto, id_campanha, id_usuario) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sisssiissssii",
        $nome_personagem,
        $idade_personagem,
        $classe_personagem,
        $inspiracao_personagem,
        $raca_personagem,
        $ca_personagem,
        $nivel_personagem,
        $tier_aura_personagem,
        $tier_magico_personagem,
        $historia_personagem,
        $foto_personagem,
        $id_campanha,
        $usuario_id
    );

    if ($stmt->execute()) {
        $personagem_id = $stmt->insert_id;
        $stmt->close();

        // Inserir status
        $stmt_status = $conn->prepare("INSERT INTO status_personagem 
            (personagem_id, mana_atual, mana_maxima, vigor_atual, vigor_maximo, sanidade_atual, sanidade_maxima, hp_atual, hp_maximo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_status->bind_param(
            "iiiiiiiii",
            $personagem_id,
            $mana_atual,
            $mana_maxima,
            $vigor_atual,
            $vigor_maximo,
            $sanidade_atual,
            $sanidade_maxima,
            $hp_atual,
            $hp_maximo
        );
        $stmt_status->execute();
        $stmt_status->close();

        // Inserir atributos
        if (isset($_POST['nome_atributo'], $_POST['valor_atributo'], $_POST['modificador_atributo'])) {
            $nomes_atributo = $_POST['nome_atributo'];
            $valores_atributo = $_POST['valor_atributo'];
            $modificadores_atributo = $_POST['modificador_atributo'];

            $stmt_atributo = $conn->prepare("INSERT INTO atributos
                (personagem_id, nome, valor, modificador)
                VALUES (?, ?, ?, ?)");
            foreach ($nomes_atributo as $index => $nome) {
                $valor = intval($valores_atributo[$index]);
                $modificador = intval($modificadores_atributo[$index]);
                $stmt_atributo->bind_param("isii", $personagem_id, $nome, $valor, $modificador);
                $stmt_atributo->execute();
            }
            $stmt_atributo->close();
        }

        // Inserir perícias
        if (isset($_POST['pericia_nome'], $_POST['pericia_valor'])) {
            $nomes_pericia = $_POST['pericia_nome'];
            $valores_pericia = $_POST['pericia_valor'];

            $stmt_pericia = $conn->prepare("INSERT INTO pericias
                (personagem_id, nome, valor)
                VALUES (?, ?, ?)");
            foreach ($nomes_pericia as $index => $nome) {
                $valor = intval($valores_pericia[$index]);
                if (trim($nome) !== '') {
                    $stmt_pericia->bind_param("isi", $personagem_id, $nome, $valor);
                    $stmt_pericia->execute();
                }
            }
            $stmt_pericia->close();
        }

        // Inserir moedas (AGORA SUPORTA VÁRIAS)
        if (!empty($_POST['moeda_id']) && !empty($_POST['quantidade'])) {
            $moedas_ids = $_POST['moeda_id'];
            $quantidades = $_POST['quantidade'];

            // Prepara o statement
            $stmt_moedas_insert = $conn->prepare("
        INSERT INTO carteiras_personagens (personagem_id, quantidade, id_carteira)
        VALUES (?, ?, ?)
    ");

            // Verifica se preparou corretamente
            if (!$stmt_moedas_insert) {
                die("Erro na preparação: " . $conn->error);
            }

            // Percorre as moedas recebidas
            foreach ($moedas_ids as $index => $id_carteira) {
                $id_carteira = intval($id_carteira);
                $quantidade = floatval($quantidades[$index]);

                // Faz o bind dos parâmetros: personagem_id, quantidade, id_carteira
                $stmt_moedas_insert->bind_param("idi", $personagem_id, $quantidade, $id_carteira);

                // Executa o insert
                if (!$stmt_moedas_insert->execute()) {
                    echo "Erro ao inserir moeda: " . $stmt_moedas_insert->error;
                }
            }

            $stmt_moedas_insert->close();
        }


        echo "<script>
            alert('Personagem criado com sucesso!');
            window.location.href = '../../campanha/campanha.php?id=$id_campanha';
        </script>";
    } else {
        echo "Erro ao criar personagem: " . $stmt->error;
        $stmt->close();
    }

    $conn->close();
}
?>



<?php
// Aqui assumimos que $carteira já foi definido no PHP que processa antes do HTML
if (!isset($carteira) || !is_array($carteira)) {
    $carteira = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Criar Personagem</title>
    <link rel="stylesheet" href="criar_personagem.css">
</head>

<body>
    <header>
        <h1>Criar Personagem</h1>
    </header>

    <div class="container">
        <form action="" method="POST" enctype="multipart/form-data">

            <!-- DADOS DO PERSONAGEM -->
            <div class="section dados-personagem">
                <h2>Dados do Personagem</h2>
                <input type="text" name="nome_personagem" placeholder="Nome" required>
                <input type="number" name="idade_personagem" placeholder="Idade" required>
                <input type="text" name="classe_personagem" placeholder="Classe" required>
                <input type="text" name="raca_personagem" placeholder="Raça" required>
                <input type="text" name="inspiracao_personagem" placeholder="Inspiração">
                <input type="number" name="ca_personagem" placeholder="Classe de Armadura (CA)" required>
                <input type="number" name="nivel_personagem" placeholder="Nível" required>
                <input type="text" name="tier_aura_personagem" placeholder="Tier Aura">
                <input type="text" name="tier_magico_personagem" placeholder="Tier Mágico">
                <textarea name="historia_personagem" placeholder="História"></textarea>
                <label>Foto do Personagem:</label>
                <input type="file" name="foto" accept=".jpg,.jpeg,.png,.gif">
            </div>

            <!-- STATUS -->
            <div class="section status">
                <h2>Status</h2>
                <input type="number" name="mana_atual" placeholder="Mana Atual" required>
                <input type="number" name="mana_maxima" placeholder="Mana Máxima" required>
                <input type="number" name="vigor_atual" placeholder="Vigor Atual" required>
                <input type="number" name="vigor_maximo" placeholder="Vigor Máximo" required>
                <input type="number" name="sanidade_atual" placeholder="Sanidade Atual" required>
                <input type="number" name="sanidade_maxima" placeholder="Sanidade Máxima" required>
                <input type="number" name="hp_atual" placeholder="HP Atual" required>
                <input type="number" name="hp_maximo" placeholder="HP Máximo" required>
            </div>

            <!-- ATRIBUTOS -->
            <div class="section atributos">
                <h2>Atributos</h2>
                <div id="atributos">
                    <div class="atributo">
                        <input type="text" name="nome_atributo[]" placeholder="Nome do Atributo" required>
                        <input type="number" name="valor_atributo[]" placeholder="Valor" required>
                        <input type="number" name="modificador_atributo[]" placeholder="Modificador" required>
                    </div>
                </div>
                <button type="button" onclick="addAtributo()">Adicionar Atributo</button>
            </div>

            <!-- PERÍCIAS -->
            <div class="section pericias">
                <h2>Perícias</h2>
                <div id="pericias">
                    <div class="pericia">
                        <input type="text" name="pericia_nome[]" placeholder="Nome da Perícia" required>
                        <input type="number" name="pericia_valor[]" placeholder="Valor" required>
                    </div>
                </div>
                <button type="button" onclick="addPericia()">Adicionar Perícia</button>
            </div>

            <!-- MOEDAS -->
            <div class="section moedas">
                <label>Escolha as moedas e quantidades:</label>
                <div id="carteira">
                    <div class="moeda">
                        <select name="moeda_id[]" required>
                            <option value="" disabled selected>Escolha a moeda</option>
                            <?php foreach ($carteira as $c): ?>
                                <option value="<?= htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($c['nome_moeda'], ENT_QUOTES, 'UTF-8') ?>
                                    (Vale <?= number_format((float)$c['valor_base'], 2, ',', '.') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label>Quantidade:</label>
                        <input type="number" name="quantidade[]" min="0" step="0.01" value="0" required>
                    </div>
                </div>

                <button type="button" onclick="addMoeda()">Adicionar Moeda</button>
            </div>


            <!-- SUBMIT -->
            <br><br>
            <input type="submit" value="Criar Personagem">
        </form>
    </div>

    <script src="criar_personagem.js"></script>
</body>

</html>