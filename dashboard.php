<?php
// 1. Inicia a sessão
// (Sempre o primeiro passo para acessar ou verificar sessões)
session_start();

// 2. A VERIFICAÇÃO DE SEGURANÇA
// Verifica se a variável de sessão 'usuario_id' NÃO está definida
if (!isset($_SESSION['usuario_id'])) {
    
    // 3. Se não estiver definida (não logado), expulsa o usuário
    // Redireciona de volta para a tela de login
    header("Location: login.php?erro=2"); // ?erro=2 pode ser "Acesso restrito"
    exit(); // Para o script
}

// 4. Se o script chegou até aqui, o usuário ESTÁ LOGADO!
// Podemos buscar o nome dele na sessão para dar boas-vindas.
// Usamos htmlspecialchars para evitar falhas de segurança (XSS)
$nome_usuario = htmlspecialchars($_SESSION['usuario_nome']);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NEON GYM</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
        /* Estilos simples para o dashboard */
        body {
            background-color: #f0f2f5;
            color: #333;
        }
        .dashboard-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            text-align: center;
        }
        h1 {
            color: #1a1a1a;
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
        
        <h2>Bem-vindo(a), <?php echo $nome_usuario; ?>!</h2>
        
        <p>Aqui você poderá gerenciar alunos, treinos, pagamentos e muito mais.</p>
        
        <a href="logout.php" class="logout-link">Sair do Sistema</a>
    </div>

</body>
</html>