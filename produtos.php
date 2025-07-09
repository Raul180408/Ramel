<?php
include "conexao.php";

// Filtro de categoria e busca por nome
$categoria = $_GET['categoria'] ?? '';
$busca = $_GET['busca'] ?? '';

// Monta o SQL dinamicamente
$sql = "SELECT * FROM produtos WHERE 1";
if ($categoria !== '') {
    $sql .= " AND categoria = '" . mysqli_real_escape_string($conn, $categoria) . "'";
}
if ($busca !== '') {
    $sql .= " AND nome LIKE '%" . mysqli_real_escape_string($conn, $busca) . "%'";
}
$sql .= " ORDER BY categoria, nome";

$res = mysqli_query($conn, $sql);

// Pega as categorias distintas para o filtro
$cat_res = mysqli_query($conn, "SELECT DISTINCT categoria FROM produtos");
?>

<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Produtos - Controle de Estoque</title>
    <link rel="icon" href="ramel.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h1>Produtos</h1>

    <a href="cadastrar_produtos.php" class="btn btn-primary mb-3">Cadastrar Novo Produto</a>

    <!-- Filtros -->
    <form class="row mb-4" method="get">
        <div class="col-md-3">
            <select name="categoria" class="form-select" onchange="this.form.submit()">
                <option value="">Todas as Categorias</option>
                <?php while($c = mysqli_fetch_assoc($cat_res)): ?>
                    <option value="<?= $c['categoria'] ?>" <?= $c['categoria']===$categoria ? 'selected' : '' ?>>
                        <?= $c['categoria'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome" value="<?= htmlspecialchars($busca) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-light">Filtrar</button>
        </div>
    </form>

    <?php
    $categoria_atual = '';
    while ($row = mysqli_fetch_assoc($res)):
        if ($row['categoria'] !== $categoria_atual):
            if ($categoria_atual !== '') echo "</tbody></table>";
            $categoria_atual = $row['categoria'];
    ?>
        <h3 class="mt-4"><?= $categoria_atual ?></h3>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Custo</th>
                    <th>Quantidade</th>
                    <th colspan="3">Ações</th>
                </tr>
            </thead>
            <tbody>
    <?php
        endif;
    ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['nome'] ?></td>
            <td>R$ <?= number_format($row['preco'], 2, ',', '.') ?></td>
            <td>R$ <?= number_format($row['custo'], 2, ',', '.') ?></td>
            <td><?= $row['quantidade'] ?></td>
            <td><a href="vender.php?produto_id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Vender</a></td>
            <td><a href="editar_produto.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a></td>
            <td>
                <form method="post" style="display:inline-block">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    <?php if ($categoria_atual !== '') echo "</tbody></table>"; ?>

    <a href="index.php" class="btn btn-secondary mt-4">Voltar</a>
</body>
</html>
