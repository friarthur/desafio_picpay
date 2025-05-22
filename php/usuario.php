<?php
session_start();
include_once 'php/sql/conexão.php';

// debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Busca dados do usuário
$sql = "SELECT nome, email, tipo, saldo FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nome, $email, $tipo, $saldo);
$stmt->fetch();
$stmt->close();

$sql_transacoes = "SELECT t.id, u.nome as destino, t.valor, t.data 
                   FROM transacoes t
                   JOIN usuarios u ON u.id = t.destinatario_id
                   WHERE t.remetente_id = ?
                   ORDER BY t.data DESC";
$stmt_trans = $conn->prepare($sql_transacoes);
$stmt_trans->bind_param("i", $usuario_id);
$stmt_trans->execute();
$result_trans = $stmt_trans->get_result();


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel do Usuário</title>
    <link rel="stylesheet" href="/php/css/usuario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="card usuario">
        <h2>Bem-vindo, <?= htmlspecialchars($nome) ?></h2>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Tipo:</strong> <?= htmlspecialchars($tipo) ?></p>
        <p><strong>Saldo:</strong> R$ <?= number_format($saldo, 2, ',', '.') ?></p>

        <a class="btn" href="/php/transferir.php"><i class="fas fa-money-check-alt"></i> Realizar Transferência</a>
        <a class="btn sair" href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
 

    <div class="card historico">
        <h3>Suas Transferências</h3>
        <?php if ($result_trans->num_rows > 0): ?>
            <ul>
                <?php while($row = $result_trans->fetch_assoc()): ?>
                    <li>
                        <span><i class="fas fa-arrow-up"></i> Enviado para: <?= htmlspecialchars($row['destino']) ?></span>
                        <span>R$ <?= number_format($row['valor'], 2, ',', '.') ?></span>
                        <span class="data"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Nenhuma transferência realizada ainda.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
