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

require_once '../config/conexao.php';

$aluno_id = $_SESSION['usuario_id'];
$nome_usuario = !empty($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Aluno';

// Buscar treinos do aluno
try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.nome as treinador_nome 
        FROM treinos t 
        INNER JOIN usuarios u ON t.treinador_id = u.id 
        WHERE t.aluno_id = ? 
        ORDER BY t.data_criacao DESC
    ");
    $stmt->execute([$aluno_id]);
    $treinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $treinos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Treinos - NEON GYM</title>
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
            max-width: 1200px;
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
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
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
        .treinos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .treino-card {
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 10px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        .treino-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.3);
            border-color: #06b6d4;
        }
        .treino-card h3 {
            color: #06b6d4;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        .treino-info {
            margin-bottom: 15px;
        }
        .treino-info p {
            color: #9ca3af;
            margin: 8px 0;
            font-size: 0.95rem;
        }
        .treino-info strong {
            color: #fff;
        }
        .treino-descricao {
            color: #9ca3af;
            margin: 15px 0;
            padding: 15px;
            background-color: #18181b;
            border-radius: 5px;
            border-left: 3px solid #06b6d4;
        }
        .btn-download {
            display: inline-block;
            padding: 10px 20px;
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid rgba(6, 182, 212, 0.3);
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-download:hover {
            background-color: #06b6d4;
            color: #000;
        }
        .sem-treinos {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        .sem-treinos svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .sem-treinos h3 {
            color: #06b6d4;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Meus Treinos</h1>
            <p>Treinos personalizados criados pelo seu treinador</p>
        </div>

        <a href="dashboard_aluno.php" class="btn-back">← Voltar ao Dashboard</a>

        <?php if (empty($treinos)): ?>
            <div class="sem-treinos">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <h3>Nenhum treino encontrado</h3>
                <p>Você ainda não recebeu nenhum treino do seu treinador.</p>
            </div>
        <?php else: ?>
            <div class="treinos-grid">
                <?php foreach ($treinos as $treino): ?>
                    <div class="treino-card">
                        <h3><?php echo htmlspecialchars($treino['nome_treino']); ?></h3>
                        
                        <div class="treino-info">
                            <p><strong>Treinador:</strong> <?php echo htmlspecialchars($treino['treinador_nome']); ?></p>
                            <p><strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i', strtotime($treino['data_criacao'])); ?></p>
                            <?php if ($treino['data_atualizacao'] != $treino['data_criacao']): ?>
                                <p><strong>Última Atualização:</strong> <?php echo date('d/m/Y H:i', strtotime($treino['data_atualizacao'])); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($treino['descricao'])): ?>
                            <div class="treino-descricao">
                                <p><?php echo nl2br(htmlspecialchars($treino['descricao'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($treino['arquivo_planilha'])): ?>
                            <a href="download_treino.php?id=<?php echo $treino['id']; ?>" class="btn-download">
                                📥 Baixar Planilha
                            </a>
                        <?php else: ?>
                            <p style="color: #9ca3af; font-size: 0.9rem; margin-top: 10px;">Nenhuma planilha anexada</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>


