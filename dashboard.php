<?php
include "conexao.php";

// Pega filtro via GET (semana|mes|ano)
$f = in_array($_GET['f'] ?? '', ['semana','mes','ano'])
     ? $_GET['f'] : 'mes';

switch($f){
  case 'semana':
    $where = "v.data_venda >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
    $group = "DAYNAME(v.data_venda)";
    $label = 'Últimos 7 Dias';
    break;
  case 'ano':
    $where = "YEAR(v.data_venda) = YEAR(CURRENT_DATE())";
    $group = "MONTHNAME(v.data_venda)";
    $label = 'Ano Atual';
    break;
  case 'mes':
  default:
    $where = "MONTH(v.data_venda)=MONTH(CURRENT_DATE())
              AND YEAR(v.data_venda)=YEAR(CURRENT_DATE())";
    $group = "DAY(v.data_venda)";
    $label = 'Mês Atual';
}

$sql = "
  SELECT 
    $group AS periodo,
    SUM(v.quantidade) AS qtd,
    SUM(p.preco*v.quantidade) AS arrecadado,
    SUM(p.custo*v.quantidade) AS gasto
  FROM vendas v
  JOIN produtos p ON p.id=v.produto_id
  WHERE $where
  GROUP BY $group
  ORDER BY MIN(v.data_venda)
";
$res = mysqli_query($conn, $sql);
$dados = [];
while($r = mysqli_fetch_assoc($res)){
    $dados[] = $r;
}

// Totais gerais
$total_qtd       = array_sum(array_column($dados,'qtd'));
$total_arrecad   = array_sum(array_column($dados,'arrecadado'));
$total_gasto     = array_sum(array_column($dados,'gasto'));
$lucro_bruto     = $total_arrecad;
$lucro_liquido   = $total_arrecad - $total_gasto;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Completo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="icon" href="ramel.png" type="image/png">
</head>
<body class="container mt-4">
  <h1>Dashboard Detalhado</h1>

  <!-- Filtros -->
  <form class="mb-4">
    <label>Período:</label>
    <select name="f" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
      <option value="semana" <?= $f=='semana'?'selected':'' ?>>Semana</option>
      <option value="mes"     <?= $f=='mes'?'selected':'' ?>>Mês</option>
      <option value="ano"     <?= $f=='ano'?'selected':'' ?>>Ano</option>
    </select>
  </form>

  <!-- Cards de Totais -->
  <div class="row mb-4">
    <?php foreach([
      ['titulo'=>'Total Vendas','valor'=>$total_qtd,'bg'=>'primary'],
      ['titulo'=>'Total Arrecadado','valor'=>'R$ '.number_format($total_arrecad,2,',','.'),'bg'=>'success'],
      ['titulo'=>'Total Gasto','valor'=>'R$ '.number_format($total_gasto,2,',','.'),'bg'=>'danger'],
      ['titulo'=>'Lucro Bruto','valor'=>'R$ '.number_format($lucro_bruto,2,',','.'),'bg'=>'warning'],
      ['titulo'=>'Lucro Líquido','valor'=>'R$ '.number_format($lucro_liquido,2,',','.'),'bg'=>'info']
    ] as $c): ?>
    <div class="col-md-2 mb-3">
      <div class="card text-bg-<?= $c['bg'] ?>">
        <div class="card-body">
          <h6><?= $c['titulo'] ?></h6>
          <p class="fs-5"><?= $c['valor'] ?></p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Gráfico -->
  <div class="card mb-4 text-bg-dark">
    <div class="card-body">
      <h5>Vendas / Receita / Custo – <?= $label ?></h5>
      <canvas id="chartDetalhado"></canvas>
    </div>
  </div>

  <a href="index.php" class="btn btn-secondary">Voltar</a>

  <script>
    const d = <?= json_encode($dados) ?>;
    const labels = d.map(x=>x.periodo);
    const dataV = d.map(x=>+x.qtd);
    const dataA = d.map(x=>+x.arrecadado);
    const dataG = d.map(x=>+x.gasto);

    new Chart(document.getElementById('chartDetalhado'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label:'Qtd Vendida',   data: dataV, fill:false,    tension:0 },
          { label:'Arrecadado',    data: dataA, fill:false,    tension:0 },
          { label:'Gasto',         data: dataG, fill:false,    tension:0 }
        ]
      },
      options:{ responsive:true,
        scales:{ y:{ beginAtZero:true } }
      }
    });
  </script>
</body>
</html>
