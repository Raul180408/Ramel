<?php 
include "conexao.php";

// Total de vendas
$total_vendas_res = mysqli_query($conn, "SELECT SUM(quantidade) AS total_vendas FROM vendas");
$total_vendas = mysqli_fetch_assoc($total_vendas_res)['total_vendas'] ?? 0;

// Total arrecadado
$total_arrecadado_res = mysqli_query($conn, "
    SELECT SUM(p.preco * v.quantidade) AS total_arrecadado 
    FROM vendas v 
    JOIN produtos p ON v.produto_id = p.id
");
$total_arrecadado = mysqli_fetch_assoc($total_arrecadado_res)['total_arrecadado'] ?? 0;

// Lucro estimado
$total_lucro_res = mysqli_query($conn, "
    SELECT SUM((p.preco - p.custo) * v.quantidade) AS lucro 
    FROM vendas v 
    JOIN produtos p ON v.produto_id = p.id
");
$total_lucro = mysqli_fetch_assoc($total_lucro_res)['lucro'] ?? 0;

// 1. Produtos com estoque baixo (limite = 5)
$res_estoque_baixo = mysqli_query($conn, 
    "SELECT nome, quantidade 
     FROM produtos 
     WHERE quantidade <= 5"
);

?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - RaMel</title>
    <link rel="icon" href="ramel.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            height: 100vh;
            overflow: hidden;
        }
        .sidebar {
            height: 100vh;
            background-color:rgb(26, 29, 32);
        }
        
        .h:hover {
            color: #ffda6a !important;
            background-color: rgb(42, 46, 51);
            border-radius: 6px;
        }
        .notificacao {
            margin: 15px;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3 text-white col-2">
        <h4>Ramel</h4>
        <ul class="nav flex-column mt-4">
            <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link text-white h">Dashboard</a></li>
            <li class="nav-item mb-2"><a href="produtos.php" class="nav-link text-white h">Produtos</a></li>
            <li class="nav-item mb-2"><a href="vender.php" class="nav-link text-white h">Vendas</a></li>
            <li class="nav-item mb-2"><a href="relatorio.php" class="nav-link text-white h">Relatórios</a></li>
        </ul>
    </div>

    <!-- Conteúdo principal -->
    <div class="content p-4 col">
        <h1 class="mb-4">Início</h1>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-bg-primary">
                    <div class="card-body">
                        <h5>Total de Vendas</h5>
                        <p class="fs-4"><?= $total_vendas ?></p>
                    </div>
                </div>
            </div>
        
            <div class="col-md-4">
                <div class="card text-bg-success">
                    <div class="card-body">
                        <h5>Total Arrecadado</h5>
                        <p class="fs-4">R$ <?= number_format($total_arrecadado, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        
            <div class="col-md-4">
                <div class="card text-bg-warning">
                    <div class="card-body">
                        <h5>Lucro Estimado</h5>
                        <p class="fs-4">R$ <?= number_format($total_lucro, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h4>Gráfico de Lucro (Simulado)</h4>
        <canvas id="lucroChart" height="100"></canvas>  

        <!-- Produtos mais vendidos -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card text-bg-dark">
                    <div class="card-body">
                        <h5>Produtos mais vendidos (Mês)</h5>
                        <?php 
                        // Consulta para os 5 produtos mais vendidos do mês
                        $res_top3 = mysqli_query($conn, "
                            SELECT p.nome, SUM(v.quantidade) AS total_vendido 
                            FROM vendas v 
                            JOIN produtos p ON v.produto_id = p.id 
                            WHERE MONTH(v.data_venda) = MONTH(CURDATE()) 
                            GROUP BY p.id 
                            ORDER BY total_vendido DESC 
                            LIMIT 5
                        ");
                        ?>
                        <ol class="ps-3">
                            <?php while($t = mysqli_fetch_assoc($res_top3)): ?>
                                <li><?= $t['nome'] ?> (<?= $t['total_vendido'] ?>)</li>
                            <?php endwhile; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Notificações de estoque baixo -->
    <div class="d-flex justify-content-end mb-4">
    <div class="dropdown notificacao">
        <button class="btn btn-secondary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            🔔
            <?php 
            $quant_estoque_baixo = mysqli_num_rows($res_estoque_baixo);
            ?>
            <?php if($quant_estoque_baixo > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $quant_estoque_baixo ?>
            </span>
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <?php if($quant_estoque_baixo > 0): ?>
                <?php 
                // Reexecuta a consulta para listar novamente os produtos
                $res_estoque_baixo = mysqli_query($conn, 
                    "SELECT nome, quantidade 
                     FROM produtos 
                     WHERE quantidade <= 5"
                );
                ?>
                <?php while($p = mysqli_fetch_assoc($res_estoque_baixo)): ?>
                    <li><a class="dropdown-item" href="editar_produto.php"><?= $p['nome'] ?> (<?= $p['quantidade'] ?>)</a></li>
                <?php endwhile; ?>
            <?php else: ?>
                <li><span class="dropdown-item-text">Sem produtos com estoque baixo</span></li>
            <?php endif; ?>
        </ul>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('lucroChart').getContext('2d');
const lucroChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
        datasets: [
            {
            label: 'Lucro da Semana',
            data: [1200, 1500, 800, 1700, 1400, 1900, 1000],
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0
            },
            {
            label: 'Vendas da Semana',
            data: [1000, 1000, 1000, 1000, 1000, 1000, 1000],
            borderColor: '#ffda6a',
            backgroundColor: 'rgba(255, 218, 106, 0.2)',
            tension: 0
            },
            {label: 'Valor arrecadado da Semana',
            data: [800, 800, 800, 800, 800, 800, 800],
            borderColor: '#75b798',
            backgroundColor: 'rgba(117, 183, 152, 0.2)',
            tension: 0
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>


</body>
</html>
