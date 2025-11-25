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

require_once '../config/conexao.php';

$nome_usuario = !empty($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Treinador';

// Buscar todos os alunos com informações adicionais
$alunos = [];
try {
    $stmt = $pdo->query("
        SELECT 
            u.id,
            u.nome,
            u.email,
            u.data_cadastro,
            COUNT(DISTINCT t.id) as total_treinos,
            COUNT(DISTINCT a.id) as total_agendamentos
        FROM usuarios u
        LEFT JOIN treinos t ON u.id = t.aluno_id
        LEFT JOIN agendamentos a ON u.id = a.aluno_id
        WHERE u.tipo_usuario = 'aluno'
        GROUP BY u.id, u.nome, u.email, u.data_cadastro
        ORDER BY u.nome ASC
    ");
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada aluno, buscar treinos e agendamentos recentes
    foreach ($alunos as &$aluno) {
        // Buscar treinos recentes
        try {
            $stmt_treinos = $pdo->prepare("
                SELECT t.*, u.nome as treinador_nome 
                FROM treinos t 
                INNER JOIN usuarios u ON t.treinador_id = u.id 
                WHERE t.aluno_id = ? 
                ORDER BY t.data_criacao DESC
                LIMIT 3
            ");
            $stmt_treinos->execute([$aluno['id']]);
            $aluno['treinos_recentes'] = $stmt_treinos->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $aluno['treinos_recentes'] = [];
        }
        
        // Buscar agendamentos recentes
        try {
            $stmt_agendamentos = $pdo->prepare("
                SELECT a.*, u.nome as treinador_nome 
                FROM agendamentos a 
                INNER JOIN usuarios u ON a.treinador_id = u.id 
                WHERE a.aluno_id = ? 
                ORDER BY a.data_consulta DESC, a.hora_consulta DESC
                LIMIT 3
            ");
            $stmt_agendamentos->execute([$aluno['id']]);
            $aluno['agendamentos_recentes'] = $stmt_agendamentos->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $aluno['agendamentos_recentes'] = [];
        }
    }
    unset($aluno);
} catch (PDOException $e) {
    $alunos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Alunos</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #000000;
            color: #ffffff;
            min-height: 100vh;
            line-height: 1.5;
        }
        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 30px;
            background-color: #18181b;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(6, 182, 212, 0.3);
        }
        .header h1 {
            color: #06b6d4;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .header p {
            color: #9ca3af;
            font-size: 1rem;
        }
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-item {
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .stat-item .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #06b6d4;
            margin-bottom: 5px;
        }
        .stat-item .stat-label {
            color: #9ca3af;
            font-size: 0.9rem;
        }
        .alunos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }
        .aluno-card {
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 10px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        .aluno-card:hover {
            border-color: #06b6d4;
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.3);
            transform: translateY(-3px);
        }
        .aluno-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(6, 182, 212, 0.2);
        }
        .aluno-info h3 {
            color: #06b6d4;
            margin: 0 0 8px 0;
            font-size: 1.3rem;
        }
        .aluno-info p {
            color: #9ca3af;
            margin: 4px 0;
            font-size: 0.9rem;
        }
        .aluno-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .aluno-stat {
            flex: 1;
            text-align: center;
            padding: 10px;
            background-color: #18181b;
            border-radius: 5px;
            border: 1px solid rgba(6, 182, 212, 0.1);
        }
        .aluno-stat .number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #06b6d4;
            margin-bottom: 5px;
        }
        .aluno-stat .label {
            font-size: 0.75rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .aluno-section {
            margin-bottom: 20px;
        }
        .aluno-section h4 {
            color: #fff;
            font-size: 1rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .aluno-section svg {
            width: 18px;
            height: 18px;
            color: #06b6d4;
        }
        .treino-item, .agendamento-item {
            background-color: #18181b;
            border-left: 3px solid rgba(6, 182, 212, 0.5);
            padding: 10px 12px;
            margin-bottom: 8px;
            border-radius: 4px;
        }
        .treino-item h5, .agendamento-item h5 {
            color: #06b6d4;
            margin: 0 0 5px 0;
            font-size: 0.9rem;
        }
        .treino-item p, .agendamento-item p {
            color: #9ca3af;
            margin: 3px 0;
            font-size: 0.85rem;
        }
        .empty-state {
            text-align: center;
            padding: 15px;
            color: #9ca3af;
            font-size: 0.9rem;
            font-style: italic;
        }
        .aluno-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(6, 182, 212, 0.2);
        }
        .btn-action {
            flex: 1;
            padding: 10px 15px;
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        .btn-action:hover {
            background-color: #06b6d4;
            color: #000;
            transform: translateY(-2px);
        }
        .btn-back {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            background-color: #27272a;
            color: #06b6d4;
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid rgba(6, 182, 212, 0.3);
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background-color: rgba(6, 182, 212, 0.1);
            border-color: #06b6d4;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 5px;
        }
        .status-agendado {
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
        }
        .status-confirmado {
            background-color: rgba(34, 211, 238, 0.2);
            color: #22d3ee;
        }
        .status-cancelado {
            background-color: rgba(255, 65, 54, 0.2);
            color: #ff4136;
        }
        .status-concluido {
            background-color: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }
        .search-box {
            margin-bottom: 25px;
        }
        .search-box input {
            width: 100%;
            padding: 12px 15px;
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .search-box input:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }
        .search-box input::placeholder {
            color: #6b7280;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Gerenciar Alunos</h1>
            <p>Visualize e gerencie todos os alunos cadastrados na academia</p>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number"><?php echo count($alunos); ?></div>
                <div class="stat-label">Total de Alunos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">
                    <?php 
                    $total_treinos = array_sum(array_column($alunos, 'total_treinos'));
                    echo $total_treinos;
                    ?>
                </div>
                <div class="stat-label">Treinos Criados</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">
                    <?php 
                    $total_agendamentos = array_sum(array_column($alunos, 'total_agendamentos'));
                    echo $total_agendamentos;
                    ?>
                </div>
                <div class="stat-label">Agendamentos</div>
            </div>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Buscar aluno por nome ou email...">
        </div>

        <?php if (empty($alunos)): ?>
            <div class="empty-state" style="padding: 60px; text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5; margin-bottom: 20px;">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <p style="font-size: 1.1rem; margin-bottom: 10px;">Nenhum aluno cadastrado</p>
                <p style="font-size: 0.9rem; color: #6b7280;">Os alunos aparecerão aqui quando se cadastrarem na academia.</p>
            </div>
        <?php else: ?>
            <div class="alunos-grid" id="alunosGrid">
                <?php foreach ($alunos as $aluno): ?>
                    <div class="aluno-card" data-nome="<?php echo strtolower(htmlspecialchars($aluno['nome'])); ?>" data-email="<?php echo strtolower(htmlspecialchars($aluno['email'])); ?>">
                        <div class="aluno-header">
                            <div class="aluno-info">
                                <h3><?php echo htmlspecialchars($aluno['nome']); ?></h3>
                                <p> <?php echo htmlspecialchars($aluno['email']); ?></p>
                                <p> Cadastrado em <?php echo date('d/m/Y', strtotime($aluno['data_cadastro'])); ?></p>
                            </div>
                        </div>

                        <div class="aluno-stats">
                            <div class="aluno-stat">
                                <div class="number"><?php echo $aluno['total_treinos']; ?></div>
                                <div class="label">Treinos</div>
                            </div>
                            <div class="aluno-stat">
                                <div class="number"><?php echo $aluno['total_agendamentos']; ?></div>
                                <div class="label">Agendamentos</div>
                            </div>
                        </div>

                        <?php if (!empty($aluno['treinos_recentes'])): ?>
                            <div class="aluno-section">
                                <h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    Treinos Recentes
                                </h4>
                                <?php foreach (array_slice($aluno['treinos_recentes'], 0, 2) as $treino): ?>
                                    <div class="treino-item">
                                        <h5><?php echo htmlspecialchars($treino['nome_treino']); ?></h5>
                                        <p>Por: <?php echo htmlspecialchars($treino['treinador_nome']); ?></p>
                                        <p>Criado em: <?php echo date('d/m/Y', strtotime($treino['data_criacao'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($aluno['agendamentos_recentes'])): ?>
                            <div class="aluno-section">
                                <h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Agendamentos Recentes
                                </h4>
                                <?php foreach (array_slice($aluno['agendamentos_recentes'], 0, 2) as $agendamento): ?>
                                    <div class="agendamento-item">
                                        <h5><?php echo date('d/m/Y', strtotime($agendamento['data_consulta'])); ?> às <?php echo date('H:i', strtotime($agendamento['hora_consulta'])); ?></h5>
                                        <p>Com: <?php echo htmlspecialchars($agendamento['treinador_nome']); ?></p>
                                        <span class="status-badge status-<?php echo $agendamento['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'agendado' => 'Agendado',
                                                'confirmado' => 'Confirmado',
                                                'cancelado' => 'Cancelado',
                                                'concluido' => 'Concluído'
                                            ];
                                            echo $status_labels[$agendamento['status']] ?? $agendamento['status'];
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="aluno-actions">
                            <a href="criar_treino.php?aluno_id=<?php echo $aluno['id']; ?>" class="btn-action">Criar Treino</a>
                            <a href="gerenciar_agendamentos.php" class="btn-action">Ver Agendamentos</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="dashboard_treinador.php" class="btn-back">← Voltar ao Dashboard</a>
        </div>
    </div>

    <script>
        // Funcionalidade de busca
        const searchInput = document.getElementById('searchInput');
        const alunosGrid = document.getElementById('alunosGrid');
        
        if (searchInput && alunosGrid) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                const cards = alunosGrid.querySelectorAll('.aluno-card');
                
                cards.forEach(card => {
                    const nome = card.getAttribute('data-nome') || '';
                    const email = card.getAttribute('data-email') || '';
                    
                    if (nome.includes(searchTerm) || email.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    </script>

</body>
</html>

