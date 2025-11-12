<?php
require '../config/conexao.php'; 

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $senha_pura = trim($_POST['senha']);

    try {
    
        // Seleciona explicitamente os campos necessários incluindo tipo_usuario
        $sql = "SELECT id, nome, email, senha_hash, tipo_usuario FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

       
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha_pura, $usuario['senha_hash'])) {
            
            // Garante que o nome seja usado (não o email)
            $nome_usuario = !empty($usuario['nome']) ? trim($usuario['nome']) : $usuario['email'];

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $nome_usuario;
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['tipo_usuario'] = !empty($usuario['tipo_usuario']) ? $usuario['tipo_usuario'] : 'aluno';

            header("Location: ../public/dashboard.php");
            exit();

        } else {
            
            header("Location: ../public/login.php?erro=credenciais");
            exit();
        }

    } catch (PDOException $e) {
        die("Erro no login: " . $e->getMessage());
    }

} else {
    header("Location: ../public/login.php");
    exit();
}
?>