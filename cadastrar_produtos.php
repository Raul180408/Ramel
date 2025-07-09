<?php
include "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $preco = $_POST["preco"];
    $custo = $_POST["custo"];
    $quantidade = $_POST["quantidade"];
    $categoria = $_POST["categoria"];

    $sql = "INSERT INTO produtos (nome, preco, custo, quantidade, categoria) VALUES ('$nome', '$preco', '$custo', '$quantidade', '$categoria')";
    mysqli_query($conn, $sql);

    header("Location: produtos.php");
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
    <link rel="icon" href="ramel.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h1>Cadastrar Produto</h1>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nome:</label>
            <input type="text" name="nome" required class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Preço de Venda:</label>
            <input type="number" step="0.01" name="preco" required class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Custo:</label>
            <input type="number" step="0.01" name="custo" required class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Quantidade:</label>
            <input type="number" name="quantidade" required class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Categoria:</label>
            <input type="text" name="categoria" required class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Cadastrar</button>
        <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</body>
</html>
