<?php 
include "conexao.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    mysqli_query($conn, "DELETE FROM vendas WHERE id = $id");
    header("Location: relatorio.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="ramel.png" type="image/png">
</head>
<body class="container mt-4">
    <h1>Relatório de Vendas</h1>
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($conn, "
                SELECT v.id, p.nome, v.quantidade, v.data_venda 
                FROM vendas v
                JOIN produtos p ON v.produto_id = p.id
                ORDER BY v.data_venda DESC
            ");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['nome']}</td>
                    <td>{$row['quantidade']}</td>
                    <td>{$row['data_venda']}</td>
                    <td>
                        <form method='post' action=''>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <button type='submit' class='btn btn-danger btn-sm' onclick=\"return confirm('Tem certeza que deseja excluir esta venda?')\">Excluir</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
    <a href="index.php" class="btn btn-secondary">Voltar</a>
</body>
</html>
