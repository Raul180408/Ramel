<?php
include "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produto_id = $_POST["produto_id"];
    $quantidade = $_POST["quantidade"];

    $res = mysqli_query($conn, "SELECT quantidade FROM produtos WHERE id = $produto_id");
    $produto = mysqli_fetch_assoc($res);

    if ($quantidade > 0 && $quantidade <= $produto['quantidade']) {
        mysqli_query($conn, "INSERT INTO vendas (produto_id, quantidade) VALUES ($produto_id, $quantidade)");
        mysqli_query($conn, "UPDATE produtos SET quantidade = quantidade - $quantidade WHERE id = $produto_id");
        echo "<div class='alert alert-success'>Venda registrada!</div>";
    } else {
        echo "<div class='alert alert-danger'>Quantidade inválida!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Registrar Venda</title>
    <link rel="icon" href="ramel.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h1>Registrar Venda</h1>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Produto:</label>
            <select name="produto_id" required class="form-select">
                <option value="">Selecione...</option>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM produtos WHERE quantidade > 0");
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<option value='{$row['id']}'>{$row['nome']} (Estoque: {$row['quantidade']})</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Quantidade:</label>
            <input type="number" name="quantidade" required class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Vender</button>
        <a href="index.php" class="btn btn-secondary">Voltar</a>
    </form>
</body>
</html>
