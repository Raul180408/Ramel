<?php
include "conexao.php";

// Verifica o ID do produto
$produto_id = isset($_GET['produto_id']) ? (int)$_GET['produto_id'] : 0;

if ($produto_id <= 0) {
    die("Produto inválido!");
}

// Busca o produto no banco
$sql = "SELECT nome, imagem FROM produtos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $produto_id);
$stmt->execute();
$stmt->bind_result($nome, $imagem);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!$imagem) {
    die("Este produto não possui imagem cadastrada.");
}

// Transforma o blob em Base64
$base64 = base64_encode($imagem);
$imgSrc = "data:image/jpeg;base64," . $base64;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Imagem - <?= htmlspecialchars($nome) ?></title>
    <link rel="icon" href="ramel.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; }
        .image-frame {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.4);
        }
        img {
            max-width: 100%;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="image-frame text-center">
        <h3 class="mb-4"> <?= htmlspecialchars($nome) ?></h3>
        
        <!-- A Imagem -->
        <img src="<?= $imgSrc ?>" alt="Imagem do Produto">

        <!-- Botões -->
        <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
            <!-- Baixar -->
            <a href="<?= $imgSrc ?>" download="<?= htmlspecialchars($nome) ?>.jpg" class="btn btn-success">
                Baixar
            </a>

            <!-- Abrir em nova aba -->
            <a href="<?= $imgSrc ?>" target="_blank" class="btn btn-primary">
                Abrir em nova aba
            </a>

            <!-- Copiar link -->
            <button class="btn btn-warning" onclick="copiarLink()">Copiar Link</button>

            <!-- Voltar -->
            <a href="produtos.php" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
</div>

<script>
function copiarLink() {
    const link = "<?= $imgSrc ?>";
    navigator.clipboard.writeText(link).then(() => {
        alert("Link da imagem copiado para a área de transferência!");
    });
}
</script>

</body>
</html>
