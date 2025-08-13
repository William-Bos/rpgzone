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

include("../../conexao.php");

// Verifica se recebeu o ID do personagem via GET
if (!isset($_GET['id'])) {
    echo "Personagem não informado.";
    exit;
}

echo "a"



// Buscar dados do personagem para preencher o formulário
$personagem_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT * FROM personagens WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $personagem_id, $usuario_id,);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Personagem não encontrado ou você não tem permissão para acessá-lo.";
    exit;
}

$personagem = $result->fetch_assoc();
$stmt->close();










// Agora que $personagem está definido, pegue o id_campanha
$id_campanha = intval($personagem['id_campanha']);  // <-- Correção feita aqui

// Buscar status
$stmt_status = $conn->prepare("SELECT * FROM status_personagem WHERE personagem_id = ?");
$stmt_status->bind_param("i", $personagem_id);
$stmt_status->execute();
$result_status = $stmt_status->get_result();
$status = $result_status->fetch_assoc();
$stmt_status->close();

// Buscar atributos
$stmt_atributos = $conn->prepare("SELECT * FROM atributos WHERE personagem_id = ?");
$stmt_atributos->bind_param("i", $personagem_id);
$stmt_atributos->execute();
$result_atributos = $stmt_atributos->get_result();
$atributos = [];
while ($row = $result_atributos->fetch_assoc()) {
    $atributos[] = $row;
}
$stmt_atributos->close();

// Buscar perícias
$stmt_pericias = $conn->prepare("SELECT * FROM pericias WHERE personagem_id = ?");
$stmt_pericias->bind_param("i", $personagem_id);
$stmt_pericias->execute();
$result_pericias = $stmt_pericias->get_result();
$pericias = [];
while ($row = $result_pericias->fetch_assoc()) {
    $pericias[] = $row;
}
$stmt_pericias->close();

// Buscar carteira
$stmt_carteira = $conn->prepare("SELECT * FROM carteira WHERE personagem_id = ?");
$stmt_carteira->bind_param("i", $personagem_id);
$stmt_carteira->execute();
$result_carteira = $stmt_carteira->get_result();
$carteira = [];
while ($row = $result_carteira->fetch_assoc()) {
    $carteira[] = $row;
}
$stmt_carteira->close();

// Processa o formulário de edição
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

    // Upload da imagem (se enviar nova)
    $foto_personagem = $personagem['foto']; // mantém a foto antiga por padrão
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
            // Opcional: apagar a foto antiga
            if ($foto_personagem && file_exists($pasta . $foto_personagem)) {
                unlink($pasta . $foto_personagem);
            }
            $foto_personagem = $nome_arquivo;
        } else {
            echo "<script>alert('Erro ao fazer upload da imagem.');history.back();</script>";
            exit;
        }
    }

    // Atualizar personagem
    $stmt_update = $conn->prepare("UPDATE personagens SET
        nome = ?, idade = ?, classe = ?, inspiracao = ?, raca = ?, ca = ?, nivel = ?, tier_aura = ?, tier_magico = ?, historia = ?, foto = ?
        WHERE id = ?");

    $stmt_update->bind_param(
        "sisssiissssi",
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
        $personagem_id
    );

    if ($stmt_update->execute()) {
        // Atualizar status
        $stmt_status_update = $conn->prepare("UPDATE status_personagem SET
            mana_atual = ?, mana_maxima = ?, vigor_atual = ?, vigor_maximo = ?, sanidade_atual = ?, sanidade_maxima = ?, hp_atual = ?, hp_maximo = ?
            WHERE personagem_id = ?");

        $stmt_status_update->bind_param(
            "iiiiiiiii",
            $mana_atual,
            $mana_maxima,
            $vigor_atual,
            $vigor_maximo,
            $sanidade_atual,
            $sanidade_maxima,
            $hp_atual,
            $hp_maximo,
            $personagem_id
        );

        $stmt_status_update->execute();
        $stmt_status_update->close();

        // Deletar atributos antigos e inserir novos
        $conn->query("DELETE FROM atributos WHERE personagem_id = $personagem_id");

        if (isset($_POST['nome_atributo'], $_POST['valor_atributo'], $_POST['modificador_atributo'])) {
            $nomes_atributo = $_POST['nome_atributo'];
            $valores_atributo = $_POST['valor_atributo'];
            $modificadores_atributo = $_POST['modificador_atributo'];

            $stmt_atributo = $conn->prepare("INSERT INTO atributos (personagem_id, nome, valor, modificador) VALUES (?, ?, ?, ?)");

            foreach ($nomes_atributo as $index => $nome) {
                $valor = intval($valores_atributo[$index]);
                $modificador = intval($modificadores_atributo[$index]);

                $stmt_atributo->bind_param(
                    "isii",
                    $personagem_id,
                    $nome,
                    $valor,
                    $modificador
                );
                $stmt_atributo->execute();
            }
            $stmt_atributo->close();
        }

        // Deletar perícias antigas e inserir novas
        $conn->query("DELETE FROM pericias WHERE personagem_id = $personagem_id");

        if (isset($_POST['pericia_nome'], $_POST['pericia_valor'])) {
            $nomes_pericia = $_POST['pericia_nome'];
            $valores_pericia = $_POST['pericia_valor'];

            $stmt_pericia = $conn->prepare("INSERT INTO pericias (personagem_id, nome, valor) VALUES (?, ?, ?)");

            foreach ($nomes_pericia as $index => $nome) {
                $valor = intval($valores_pericia[$index]);

                if (trim($nome) !== '') {
                    $stmt_pericia->bind_param(
                        "isi",
                        $personagem_id,
                        $nome,
                        $valor
                    );
                    $stmt_pericia->execute();
                }
            }
            $stmt_pericia->close();
        }

        // Deletar carteira antiga e inserir nova
        $conn->query("DELETE FROM carteira WHERE personagem_id = $personagem_id");

        if (isset($_POST['nome_moeda'], $_POST['valor_base'], $_POST['quantidade'])) {
            $nomes_moeda = $_POST['nome_moeda'];
            $valores_base = $_POST['valor_base'];
            $quantidades = $_POST['quantidade'];

            $stmt_carteira = $conn->prepare("INSERT INTO carteira (personagem_id, nome_moeda, valor_base, quantidade) VALUES (?, ?, ?, ?)");

            foreach ($nomes_moeda as $index => $nome_moeda) {
                $valor_base = intval($valores_base[$index]);
                $quantidade = intval($quantidades[$index]);

                if (trim($nome_moeda) !== '') {
                    $stmt_carteira->bind_param(
                        "isii",
                        $personagem_id,
                        $nome_moeda,
                        $valor_base,
                        $quantidade
                    );
                    $stmt_carteira->execute();
                }
            }
            $stmt_carteira->close();
        }

        echo "<script>
                alert('Personagem atualizado com sucesso!');
                window.location.href = 'ficha_personagem.php?id={$personagem_id}';

              </script>";
        exit;

    } else {
        echo "Erro ao atualizar personagem: " . $stmt_update->error;
    }

    $stmt_update->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Personagem</title>
    <link rel="stylesheet" href="ficha_personagem.css">
</head>
<body>

<div class="container">
<a href="../campanha/campanha.php?id=<?php echo $id_campanha; ?>">Voltar para Campanha</a>

    <h1>Editar Personagem</h1>
    <form action="" method="POST" enctype="multipart/form-data">

        <h2>Dados do Personagem</h2>
        <input type="text" name="nome_personagem" placeholder="Nome" required value="<?= htmlspecialchars($personagem['nome']) ?>">
        <input type="number" name="idade_personagem" placeholder="Idade" required value="<?= $personagem['idade'] ?>">
        <input type="text" name="classe_personagem" placeholder="Classe" required value="<?= htmlspecialchars($personagem['classe']) ?>">
        <input type="text" name="raca_personagem" placeholder="Raça" required value="<?= htmlspecialchars($personagem['raca']) ?>">
        <input type="text" name="inspiracao_personagem" placeholder="Inspiração" value="<?= htmlspecialchars($personagem['inspiracao']) ?>">
        <input type="number" name="ca_personagem" placeholder="Classe de Armadura" required value="<?= $personagem['ca'] ?>">
        <input type="number" name="nivel_personagem" placeholder="Nível" required value="<?= $personagem['nivel'] ?>">
        <input type="text" name="tier_aura_personagem" placeholder="Tier Aura" value="<?= htmlspecialchars($personagem['tier_aura']) ?>">
        <input type="text" name="tier_magico_personagem" placeholder="Tier Mágico" value="<?= htmlspecialchars($personagem['tier_magico']) ?>">
        <textarea name="historia_personagem" placeholder="História" rows="5"><?= htmlspecialchars($personagem['historia']) ?></textarea>

        <label>Foto do Personagem (JPG, PNG, GIF):</label>
        <input type="file" name="foto" accept="image/*">

        <h2>Status</h2>
        <input type="number" name="mana_atual" placeholder="Mana Atual" required value="<?= $status['mana_atual'] ?>">
        <input type="number" name="mana_maxima" placeholder="Mana Máxima" required value="<?= $status['mana_maxima'] ?>">
        <input type="number" name="vigor_atual" placeholder="Vigor Atual" required value="<?= $status['vigor_atual'] ?>">
        <input type="number" name="vigor_maximo" placeholder="Vigor Máximo" required value="<?= $status['vigor_maximo'] ?>">
        <input type="number" name="sanidade_atual" placeholder="Sanidade Atual" required value="<?= $status['sanidade_atual'] ?>">
        <input type="number" name="sanidade_maxima" placeholder="Sanidade Máxima" required value="<?= $status['sanidade_maxima'] ?>">
        <input type="number" name="hp_atual" placeholder="HP Atual" required value="<?= $status['hp_atual'] ?>">
        <input type="number" name="hp_maximo" placeholder="HP Máximo" required value="<?= $status['hp_maximo'] ?>">

        <h2>Atributos</h2>
        <div id="atributos-container">
            <?php foreach ($atributos as $atributo): ?>
                <div class="atributo-item">
                    <input type="text" name="nome_atributo[]" placeholder="Nome" value="<?= htmlspecialchars($atributo['nome']) ?>" required>
                    <input type="number" name="valor_atributo[]" placeholder="Valor" value="<?= $atributo['valor'] ?>" required>
                    <input type="number" name="modificador_atributo[]" placeholder="Modificador" value="<?= $atributo['modificador'] ?>" required>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" onclick="adicionarAtributo()">Adicionar Atributo</button>

        <h2>Perícias</h2>
        <div id="pericias-container">
            <?php foreach ($pericias as $pericia): ?>
                <div class="pericia-item">
                    <input type="text" name="pericia_nome[]" placeholder="Nome" value="<?= htmlspecialchars($pericia['nome']) ?>" required>
                    <input type="number" name="pericia_valor[]" placeholder="Valor" value="<?= $pericia['valor'] ?>" required>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" onclick="adicionarPericia()">Adicionar Perícia</button>

        <h2>Carteira</h2>
        <div id="carteira-container">
            <?php foreach ($carteira as $item): ?>
                <div class="carteira-item">
                    <input type="text" name="nome_moeda[]" placeholder="Nome da Moeda" value="<?= htmlspecialchars($item['nome_moeda']) ?>" required>
                    <input type="number" name="valor_base[]" placeholder="Valor Base" value="<?= $item['valor_base'] ?>" required>
                    <input type="number" name="quantidade[]" placeholder="Quantidade" value="<?= $item['quantidade'] ?>" required>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" onclick="adicionarMoeda()">Adicionar Moeda</button>

        <button type="submit">Salvar</button>
    </form>
</div>

<script>
function adicionarAtributo() {
    const container = document.getElementById('atributos-container');
    const div = document.createElement('div');
    div.classList.add('atributo-item');
    div.innerHTML = `
        <input type="text" name="nome_atributo[]" placeholder="Nome" required>
        <input type="number" name="valor_atributo[]" placeholder="Valor" required>
        <input type="number" name="modificador_atributo[]" placeholder="Modificador" required>
    `;
    container.appendChild(div);
}

function adicionarPericia() {
    const container = document.getElementById('pericias-container');
    const div = document.createElement('div');
    div.classList.add('pericia-item');
    div.innerHTML = `
        <input type="text" name="pericia_nome[]" placeholder="Nome" required>
        <input type="number" name="pericia_valor[]" placeholder="Valor" required>
    `;
    container.appendChild(div);
}

function adicionarMoeda() {
    const container = document.getElementById('carteira-container');
    const div = document.createElement('div');
    div.classList.add('carteira-item');
    div.innerHTML = `
        <input type="text" name="nome_moeda[]" placeholder="Nome da Moeda" required>
        <input type="number" name="valor_base[]" placeholder="Valor Base" required>
        <input type="number" name="quantidade[]" placeholder="Quantidade" required>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
