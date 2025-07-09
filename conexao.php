<?php
$host = "localhost";
$user = "root";
$senha = "";
$banco = "controle_estoque";

$conn = mysqli_connect($host, $user, $senha, $banco);

if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}
?>
