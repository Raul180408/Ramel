<?php 
include "conexao.php";

// 1. Produtos com estoque baixo (limite = 5)
$res_estoque_baixo = mysqli_query($conn, 
    "SELECT nome, quantidade 
     FROM produtos 
     WHERE quantidade <= 5"
);

// 2. Dados da semana para gráfico e cards
$res_semana = mysqli_query($conn, "
    SELECT 
      DAYNAME(v.data_venda) AS dia,
      SUM(v.quantidade) AS total_vendas,
      SUM(p.preco * v.quantidade) AS total_arrecadado,
      SUM(p.custo  * v.quantidade) AS total_gasto
    FROM vendas v
    JOIN produtos p ON p.id = v.produto_id
    WHERE v.data_venda >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
    GROUP BY DAY(v.data_venda)
    ORDER BY v.data_venda
");
$dados_semana = [];
while($r = mysqli_fetch_assoc($res_semana)){
    $dados_semana[] = $r;
}

// 3. Produtos mais vendidos (top 3 no mês)
$res_top3 = mysqli_query($conn, "
    SELECT p.nome, SUM(v.quantidade) AS total_vendido
    FROM vendas v
    JOIN produtos p ON p.id = v.produto_id
    WHERE MONTH(v.data_venda) = MONTH(CURRENT_DATE())
      AND YEAR(v.data_venda) = YEAR(CURRENT_DATE())
    GROUP BY p.id
    ORDER BY total_vendido DESC
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <title>Index - Controle de Estoque</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container mt-4">
  <h1>Resumo Rápido</h1>
  <div class="row mb-4">
    <!-- Estoque baixo -->
    <div class="col-md-4">
      <div class="card text-bg-danger">
        <div class="card-body">
          <h5>Estoque Baixo</h5>
          <ul class="list-unstyled">
            <?php while($p = mysqli_fetch_assoc($res_estoque_baixo)): ?>
              <li><?= $p['nome'] ?> (<?= $p['quantidade'] ?>)</li>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- Gráfico semana -->
    <div class="col-md-4">
      <div class="card text-bg-primary">
        <div class="card-body">
          <h5>Vendas Últimos 7 dias</h5>
          <canvas id="chartSemana"></canvas>
        </div>
      </div>
    </div>

    <!-- Top3 produtos -->
    <div class="col-md-4">
      <div class="card text-bg-success">
        <div class="card-body">
          <h5>Produtos mais vendidos (Mês)</h5>
          <ol class="ps-3">
            <?php while($t = mysqli_fetch_assoc($res_top3)): ?>
              <li><?= $t['nome'] ?> (<?= $t['total_vendido'] ?>)</li>
            <?php endwhile; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <a href="dashboard.php" class="btn btn-light">Ver Dashboard Completo</a>

  <script>
    const dados = <?= json_encode($dados_semana) ?>;
    const labels = dados.map(d=>d.dia);
    const vendas = dados.map(d=>+d.total_vendas);
    new Chart(document.getElementById('chartSemana'), {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Vendas',
          data: vendas,
          backgroundColor: 'rgba(255,255,255,0.3)',
          borderColor: 'white',
          borderWidth: 1
        }]
      },
      options: {
        scales: { y: { beginAtZero:true } }
      }
    });
  </script>
</body>
</html>
