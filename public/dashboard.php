<?php
session_start();

// aqui já é uma verifiação se o cliente/usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    
    header("Location: login.php?erro=2"); 
    exit(); 
}

// aqui ele está pegando os dados do cliente/usuário
// Garante que sempre use o nome, nunca o email
$nome_usuario = !empty($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Usuário';

require_once '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NEON GYM</title>
    <link rel="stylesheet" href="../assets/css/styles.css"> 
    <style>
    
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #000000;
            color: #ffffff;
            min-height: 100vh;
            line-height: 1.5;
        }
        .dashboard-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #18181b;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            text-align: center;
        }
        h1 {
            color: #fff;
        }
        .logout-link {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background-color: #ff4136; /* Vermelho para sair */
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .logout-link:hover {
            background-color: #d13026;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <h1>Painel de Controle - NEON GYM</h1>
        <hr style="margin: 20px 0;">
        
        <h2>Seja bem vindo, <?php echo $nome_usuario; ?>!</h2>
        
        <p>Aqui você poderá gerenciar alunos, treinos, pagamentos e muito mais.</p>
        
        <a href="login.php" class="logout-link">Sair do Sistema</a>
    </div>

</body>
</html>