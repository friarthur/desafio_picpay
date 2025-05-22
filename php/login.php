<?php 
include_once 'php/sql/conexão.php'; 

session_start(); 
// debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error ="";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if(empty($nome) || empty($senha)){
        die('Preencha todos os dados');
    }else{
        $sql = "SELECT id, nome, senha FROM usuarios WHERE nome =?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows === 1){
            $stmt->bind_result($id, $nome, $senha_hash);
            $stmt->fetch();
            
            if(password_verify($senha, $senha_hash)){
                $_SESSION['usuario_id']= $id;
                $_SESSION['usuario_nome'] = $nome;

                header('Location: usuario.php');
                exit;
            }else{
                $erro = "senha incorreta!";
            }           

        }else{
            $erro = "usuário não encontrado";
        }

        $stmt->close();

    }
    $conn->close();
    
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/php/css/login.css">

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <form action="/php/login.php" method="POST">
        <h2><i class="fas fa-sign-in-alt"></i> Login do Usuário</h2>

        <?php if (!empty($erro)) : ?>
            <p class="erro"><?= $erro ?></p>
        <?php endif; ?>

        <label for="nome"><i class="fas fa-envelope"></i> Nome:</label>
        <input type="text" name="nome" required>

        <label for="senha"><i class="fas fa-lock"></i> Senha:</label>
        <input type="password" name="senha" required>

        <button type="submit"><i class="fas fa-sign-in-alt"></i> Entrar</button>
        <a href="/php/index.php">Voltar ao cadastro</a>
       
    </form>
</body>
</html>
