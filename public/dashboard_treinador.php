<?php
session_start();

// Verificação se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?erro=2"); 
    exit(); 
}

// Verificar se é realmente um treinador
if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] !== 'treinador') {
    header("Location: dashboard.php");
    exit();
}

$nome_usuario = !empty($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Treinador';

require_once '../includes/header.php';
require_once '../config/conexao.php';

// Contar total de alunos
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'aluno'");
    $total_alunos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_alunos = 0;
}

// Contar agendamentos pendentes
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE treinador_id = ? AND status IN ('agendado', 'confirmado')");
    $stmt->execute([$_SESSION['usuario_id']]);
    $total_agendamentos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_agendamentos = 0;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Treinador - NEON GYM</title>
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
            max-width: 1400px;
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
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(34, 211, 238, 0.1));
            border-radius: 8px;
            border-left: 4px solid #06b6d4;
        }
        .welcome-message h2 {
            color: #fff;
            margin-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, #27272a, #1f1f23);
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.4);
            border-color: #06b6d4;
        }
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #06b6d4;
            margin: 10px 0;
        }
        .stat-label {
            color: #9ca3af;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
            margin-bottom: 15px;
        }
        .card-action {
            display: inline-block;
            padding: 8px 20px;
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .card-action:hover {
            background-color: #06b6d4;
            color: #000;
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
            <h1>Dashboard do Treinador</h1>
            <p>Gerencie alunos, treinos e acompanhe o progresso da academia</p>
        </div>

        <div class="welcome-message">
            <h2>Bem-vindo, <?php echo $nome_usuario; ?>! </h2>
            <p>Este é seu painel administrativo. Gerencie todos os aspectos da academia.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total de Alunos</div>
                <div class="stat-number"><?php echo $total_alunos; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Treinos Ativos</div>
                <div class="stat-number">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Agendamentos Pendentes</div>
                <div class="stat-number"><?php echo $total_agendamentos; ?></div>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3>Gerenciar Alunos</h3>
                <p>Visualize, edite e gerencie todos os alunos cadastrados na academia.</p>
                <a href="gerenciar_alunos.php" class="card-action">Acessar →</a>
            </div>

            <div class="dashboard-card">
                <div class="dashboard-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <h3>Criar Treinos</h3>
                <p>Crie e personalize treinos para seus alunos com exercícios e séries específicas.</p>
                <a href="criar_treino.php" class="card-action">Acessar →</a>
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
                <p>Gerencie agendamentos de aulas e consultas.</p>
                <a href="gerenciar_agendamentos.php" class="card-action">Acessar →</a>
            </div>
        </div>

        <div class="actions">
            <a href="logout.php" class="btn-action btn-danger">Sair do Sistema</a>
        </div>
    </div>

</body>
</html>

