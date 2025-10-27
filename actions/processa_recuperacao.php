<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        header('Location: ../public/recuperar_senha.php?erro=email_invalido');
        exit;
    }
    
    try {
        // Verificar se o email existe no banco de dados
        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            // Aqui você implementaria o envio do email
            // Por enquanto, vamos apenas redirecionar com sucesso
            
            // Em produção, você enviaria um email com um link de redefinição
            // Exemplo: https://seudominio.com/recuperar_senha.php?token=abc123
            
            header('Location: ../public/recuperar_senha.php?sucesso=1');
            exit;
        } else {
            header('Location: ../public/recuperar_senha.php?erro=email_nao_encontrado');
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("Erro ao processar recuperação de senha: " . $e->getMessage());
        header('Location: ../public/recuperar_senha.php?erro=erro_servidor');
        exit;
    }
} else {
    header('Location: ../public/login.php');
    exit;
}
?>

