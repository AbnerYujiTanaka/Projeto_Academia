<?php
session_start();

// Verificação se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?erro=2"); 
    exit(); 
}

// Verificar se é realmente um aluno
if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] !== 'aluno') {
    header("Location: dashboard.php");
    exit();
}

$nome_usuario = !empty($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Aluno';

require_once '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Aluno - NEON GYM</title>
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
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background-color: #18181b;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        .dashboard-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(6, 182, 212, 0.3);
        }
        .dashboard-header h1 {
            color: #06b6d4;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .dashboard-header p {
            color: #9ca3af;
            font-size: 1.1rem;
        }
        .welcome-message {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background-color: rgba(6, 182, 212, 0.1);
            border-radius: 8px;
            border-left: 4px solid #06b6d4;
        }
        .welcome-message h2 {
            color: #fff;
            margin-bottom: 10px;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 10px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.3);
            border-color: #06b6d4;
        }
        .dashboard-card-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(6, 182, 212, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .dashboard-card-icon svg {
            color: #06b6d4;
        }
        .dashboard-card h3 {
            color: #fff;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        .dashboard-card p {
            color: #9ca3af;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .actions {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(6, 182, 212, 0.3);
        }
        .btn-action {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #06b6d4;
            color: #000;
        }
        .btn-primary:hover {
            background-color: #22d3ee;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
        }
        .btn-danger {
            background-color: #ff4136;
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #d13026;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1> Dashboard do Aluno</h1>
            <p>Gerencie seus treinos e acompanhe seu progresso</p>
        </div>

        <div class="welcome-message">
            <h2>Bem-vindo, <?php echo $nome_usuario; ?>! </h2>
            <p>Este é seu painel pessoal. Aqui você pode visualizar seus treinos, acompanhar seu progresso e muito mais.</p>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <h3>Meus Treinos</h3>
                <p>Visualize e acompanhe seus treinos personalizados pelo seu treinador.</p>
            </div>

            <div class="dashboard-card">
                <div class="dashboard-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
                <h3>Progresso</h3>
                <p>Acompanhe sua evolução, medidas e resultados ao longo do tempo.</p>
            </div>

            <div class="dashboard-card">
                <div class="dashboard-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <h3>Agendamentos</h3>
                <p>Agende suas aulas, consultas e avaliações físicas.</p>
            </div>

            <div class="dashboard-card">
                <div class="dashboard-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>
                </div>
                <h3>Histórico</h3>
                <p>Veja seu histórico de treinos, presenças e avaliações anteriores.</p>
            </div>
        </div>

        <div class="actions">
            <a href="login.php" class="btn-action btn-danger">Sair do Sistema</a>
        </div>
    </div>

</body>
</html>

