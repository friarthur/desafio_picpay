<?php
session_start();
header('Content-Type: application/json');
include_once 'php/sql/conexão.php';

// Função para enviar resposta JSON com código HTTP
function resposta($sucesso, $mensagem, $codigo_http = 200) {
    http_response_code($codigo_http);
    echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem]);
    exit;
}

// Verifica autenticação
if (!isset($_SESSION['usuario_id'])) {
    resposta(false, 'Usuário não autenticado', 401);
}

$usuario_id = $_SESSION['usuario_id'];

// Ler JSON recebido
$input = json_decode(file_get_contents('php://input'), true);

// Valida entrada
if (empty($input['email']) || empty($input['valor'])) {
    resposta(false, 'Email e valor são obrigatórios', 400);
}

$email_destino = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
$valor = filter_var($input['valor'], FILTER_VALIDATE_FLOAT);

if (!$email_destino) {
    resposta(false, 'Email inválido', 400);
}

if ($valor === false || $valor <= 0) {
    resposta(false, 'Valor inválido', 400);
}

try {
    // Buscar saldo do remetente
    $stmt = $conn->prepare("SELECT saldo FROM usuarios WHERE id = ?");
    if (!$stmt) throw new Exception("Erro na preparação SQL: " . $conn->error);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($saldo_remetente);
    if (!$stmt->fetch()) {
        resposta(false, 'Usuário remetente não encontrado', 404);
    }
    $stmt->close();

    if ($saldo_remetente < $valor) {
        resposta(false, 'Saldo insuficiente', 400);
    }

    // Buscar destinatário
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    if (!$stmt) throw new Exception("Erro na preparação SQL: " . $conn->error);
    $stmt->bind_param("s", $email_destino);
    $stmt->execute();
    $stmt->bind_result($destinatario_id);
    if (!$stmt->fetch()) {
        resposta(false, 'Destinatário não encontrado', 404);
    }
    $stmt->close();

    if ($destinatario_id == $usuario_id) {
        resposta(false, 'Não pode transferir para si mesmo', 400);
    }

    // Iniciar transação
    $conn->begin_transaction();

    // Atualizar saldo remetente
    $stmt = $conn->prepare("UPDATE usuarios SET saldo = saldo - ? WHERE id = ?");
    if (!$stmt) throw new Exception("Erro na preparação SQL: " . $conn->error);
    $stmt->bind_param("di", $valor, $usuario_id);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) throw new Exception("Falha ao descontar saldo do remetente");
    $stmt->close();

    // Atualizar saldo destinatário
    $stmt = $conn->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
    if (!$stmt) throw new Exception("Erro na preparação SQL: " . $conn->error);
    $stmt->bind_param("di", $valor, $destinatario_id);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) throw new Exception("Falha ao creditar saldo do destinatário");
    $stmt->close();

    // Inserir registro da transação
    $stmt = $conn->prepare("INSERT INTO transacoes (remetente_id, destinatario_id, valor, data_hora) VALUES (?, ?, ?, NOW())");
    if (!$stmt) throw new Exception("Erro na preparação SQL: " . $conn->error);
    $stmt->bind_param("iid", $usuario_id, $destinatario_id, $valor);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) throw new Exception("Falha ao registrar transação");
    $stmt->close();

    // Commit da transação
    $conn->commit();

    resposta(true, 'Transferência realizada com sucesso!');

} catch (Exception $e) {
    $conn->rollback();

    // Log do erro (pode trocar para um arquivo de log real)
    error_log("[Transferir Error] " . $e->getMessage());

    resposta(false, 'Erro ao realizar transferência', 500);
}
