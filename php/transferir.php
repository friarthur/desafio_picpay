<?php
session_start();
include_once 'php/sql/conexão.php';


if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_destino = trim($_POST['email']);
    $valor = floatval($_POST['valor']);

    if ($valor <= 0) {
        $mensagem = "Valor inválido.";
    } else {
        // Buscar o usuário remetente
        $stmt = $conn->prepare("SELECT saldo FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->bind_result($saldo_remetente);
        $stmt->fetch();
        $stmt->close();

        if ($saldo_remetente < $valor) {
            $mensagem = "Saldo insuficiente.";
        } else {
            // Buscar destinatário
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email_destino);
            $stmt->execute();
            $stmt->bind_result($destinatario_id);
            if ($stmt->fetch()) {
                $stmt->close();

                if ($destinatario_id == $usuario_id) {
                    $mensagem = "Você não pode transferir para si mesmo.";
                } else {
                    // Iniciar transação
                    $conn->begin_transaction();

                    try {
                        // Descontar do remetente
                        $stmt = $conn->prepare("UPDATE usuarios SET saldo = saldo - ? WHERE id = ?");
                        $stmt->bind_param("di", $valor, $usuario_id);
                        $stmt->execute();

                        // Adicionar ao destinatário
                        $stmt = $conn->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
                        $stmt->bind_param("di", $valor, $destinatario_id);
                        $stmt->execute();

                        // Registrar transação
                        $stmt = $conn->prepare("INSERT INTO transacoes (remetente_id, destinatario_id, valor) VALUES (?, ?, ?)");
                        $stmt->bind_param("iid", $usuario_id, $destinatario_id, $valor);
                        $stmt->execute();

                        $conn->commit();
                        $mensagem = "Transferência realizada com sucesso!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $mensagem = "Erro ao realizar transferência.";
                    }
                }
            } else {
                $mensagem = "Destinatário não encontrado.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Transferir</title>
    <link rel="stylesheet" href="/php/css/usuario.css" />
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Realizar Transferência</h2>
        <div id="mensagem"></div>

        <form id="form-transferir">
            <label for="email">E-mail do destinatário:</label>
            <input type="email" id="email" name="email" required />

            <label for="valor">Valor:</label>
            <input type="number" step="0.01" id="valor" name="valor" required />

            <button type="submit">Transferir</button>
        </form>

        <a class="btn" href="usuario.php">Voltar para o painel</a>
    </div>
</div>

<script src='/php/js/transferir.js'>
</script>
</body>
</html>
