<?php
require '../config/conexao.php'; 

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $senha_pura = trim($_POST['senha']);

    try {
    
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

       
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha_pura, $usuario['senha_hash'])) {
            

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];

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