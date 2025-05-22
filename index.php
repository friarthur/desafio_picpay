<?php 
include_once 'php/sql/conexão.php'; // Corrija o nome do arquivo se for "conexao.php"

session_start(); 
// debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST["nome"] ?? '';
    $email = $_POST["email"] ?? '';
    $cpf   = $_POST["cpf"] ?? '';
    $tipo  = $_POST["tipo"] ?? '';
    $senha = $_POST["senha"] ?? '';
    $saldo = $_POST["saldo"] ?? '0.00'; // default para 0.00

    if (empty($nome) || empty($email) || empty($cpf) || empty($tipo) || empty($senha)) {
        die('Por favor, preencha todos os campos obrigatórios.');
    }

    // Verifica se o e-mail ou cpf já existem
    $sql_verifica = 'SELECT id FROM usuarios WHERE email = ? OR cpf = ?';
    $stmt_verifica = $conn->prepare($sql_verifica);
    $stmt_verifica->bind_param('ss', $email, $cpf);
    $stmt_verifica->execute();
    $stmt_verifica->store_result();

    if ($stmt_verifica->num_rows > 0) {
        die('Este e-mail ou CPF já foi utilizado!');
    }

    $stmt_verifica->close();

    // Criptografa a senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere novo usuário
    $sql = 'INSERT INTO usuarios (nome, email, cpf, tipo, senha, saldo) VALUES (?, ?, ?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssd", $nome, $email, $cpf, $tipo, $senha_hash, $saldo); // saldo como double (d)

    if ($stmt->execute()) {
        header("Location: /pos_cad.php");
        exit;
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

 <!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Usuário</title>
  <link rel="stylesheet" href="/php/css/cadastro.css">
</head>
<body>
  <div class="container">
    <h2>Cadastro de Usuário</h2>
    <form action="index.php" method="POST">
      <label for="nome">Nome completo:</label>
      <input type="text" id="nome" name="nome" required>

      <label for="email">E-mail:</label>
      <input type="email" id="email" name="email" required>

      <label for="cpf">CPF:</label>
      <input type="text" id="cpf" name="cpf" maxlength="11" required>

      <label for="senha">Senha:</label>
      <input type="password" id="senha" name="senha" required>

      <label for="tipo">Tipo de usuário:</label>
      <select id="tipo" name="tipo" required>
        <option value="">Selecione</option>
        <option value="comum">Comum</option>
        <option value="lojista">Lojista</option>
      </select>

      <label for="saldo">Saldo inicial (opcional):</label>
      <input type="number" id="saldo" name="saldo" step="0.01" placeholder="0.00">

      <button type="submit">Cadastrar</button>
      <a href="/php/login.php">Faça login!</a>
    </form>
  </div>
</body>
</html>
