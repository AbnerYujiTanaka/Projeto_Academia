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
    
    // Buscar exercícios para cada treino
    foreach ($treinos as &$treino) {
        try {
            $stmt_exercicios = $pdo->prepare("
                SELECT exercicio, series, repeticoes, descricao 
                FROM treino_exercicios 
                WHERE treino_id = ? 
                ORDER BY ordem ASC
            ");
            $stmt_exercicios->execute([$treino['id']]);
            $treino['exercicios'] = $stmt_exercicios->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $treino['exercicios'] = [];
        }
    }
    unset($treino);
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
        .planilha-exercicios {
            margin-top: 20px;
        }
        .planilha-view {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #18181b;
            border-radius: 8px;
            overflow: hidden;
        }
        .planilha-view thead {
            background-color: rgba(6, 182, 212, 0.2);
        }
        .planilha-view th {
            padding: 12px;
            text-align: left;
            color: #06b6d4;
            font-weight: 600;
            border-bottom: 2px solid rgba(6, 182, 212, 0.3);
            font-size: 0.9rem;
        }
        .planilha-view td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(6, 182, 212, 0.1);
            color: #fff;
            font-size: 0.9rem;
        }
        .planilha-view tbody tr:last-child td {
            border-bottom: none;
        }
        .planilha-view tbody tr:hover {
            background-color: rgba(6, 182, 212, 0.05);
        }
        .btn-print {
            display: inline-block;
            padding: 10px 20px;
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid rgba(6, 182, 212, 0.3);
            transition: all 0.3s ease;
            margin-top: 10px;
            margin-right: 10px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-print:hover {
            background-color: #06b6d4;
            color: #000;
        }
        .treino-actions {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        /* Estilos para impressão */
        @media print {
            body {
                background-color: #fff;
                color: #000;
            }
            .container {
                max-width: 100%;
                margin: 0;
                padding: 20px;
                background-color: #fff;
                border: none;
                box-shadow: none;
            }
            .header {
                border-bottom: 2px solid #000;
                margin-bottom: 20px;
            }
            .header h1 {
                color: #000;
            }
            .btn-back,
            .btn-download,
            .btn-print,
            .treino-actions {
                display: none !important;
            }
            .treinos-grid {
                display: block;
            }
            .treino-card {
                background-color: #fff;
                border: 1px solid #000;
                border-radius: 0;
                padding: 20px;
                margin-bottom: 30px;
                page-break-inside: avoid;
                box-shadow: none;
            }
            .treino-card h3 {
                color: #000;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 15px;
            }
            .treino-info p {
                color: #000;
            }
            .treino-info strong {
                color: #000;
            }
            .treino-descricao {
                background-color: #f5f5f5;
                border-left: 3px solid #000;
                color: #000;
            }
            .planilha-view {
                background-color: #fff;
                border: 1px solid #000;
            }
            .planilha-view thead {
                background-color: #f0f0f0;
            }
            .planilha-view th {
                color: #000;
                border-bottom: 2px solid #000;
            }
            .planilha-view td {
                color: #000;
                border-bottom: 1px solid #ccc;
            }
            .planilha-view tbody tr:last-child td {
                border-bottom: 1px solid #ccc;
            }
            @page {
                margin: 2cm;
            }
        }
    </style>
    <script>
        function imprimirTreino(treinoId) {
            // Criar uma nova janela para impressão
            const treinoCard = document.querySelector(`[data-treino-id="${treinoId}"]`);
            if (!treinoCard) return;

            // Criar conteúdo para impressão
            const conteudoImpressao = treinoCard.cloneNode(true);
            
            // Remover botões do clone
            const actions = conteudoImpressao.querySelector('.treino-actions');
            if (actions) actions.remove();

            // Criar janela de impressão
            const janelaImpressao = window.open('', '_blank', 'width=800,height=600');
            janelaImpressao.document.write(`
                <!DOCTYPE html>
                <html lang="pt-BR">
                <head>
                    <meta charset="UTF-8">
                    <title>Treino - ${treinoCard.querySelector('h3').textContent}</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                            color: #000;
                            background: #fff;
                        }
                        h3 {
                            color: #000;
                            border-bottom: 2px solid #000;
                            padding-bottom: 10px;
                            margin-bottom: 15px;
                        }
                        .treino-info p {
                            color: #000;
                            margin: 8px 0;
                        }
                        .treino-info strong {
                            color: #000;
                        }
                        .treino-descricao {
                            background-color: #f5f5f5;
                            border-left: 3px solid #000;
                            color: #000;
                            padding: 15px;
                            margin: 15px 0;
                        }
                        .planilha-view {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 10px;
                            border: 1px solid #000;
                        }
                        .planilha-view thead {
                            background-color: #f0f0f0;
                        }
                        .planilha-view th {
                            padding: 12px;
                            text-align: left;
                            color: #000;
                            font-weight: 600;
                            border-bottom: 2px solid #000;
                        }
                        .planilha-view td {
                            padding: 10px 12px;
                            border-bottom: 1px solid #ccc;
                            color: #000;
                        }
                        .planilha-view tbody tr:last-child td {
                            border-bottom: 1px solid #ccc;
                        }
                        @media print {
                            @page {
                                margin: 2cm;
                            }
                            body {
                                padding: 0;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${conteudoImpressao.innerHTML}
                </body>
                </html>
            `);
            janelaImpressao.document.close();
            
            // Aguardar carregamento e imprimir
            janelaImpressao.onload = function() {
                setTimeout(function() {
                    janelaImpressao.print();
                }, 250);
            };
        }
    </script>
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
                    <div class="treino-card" data-treino-id="<?php echo $treino['id']; ?>">
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

                        <?php if (!empty($treino['exercicios'])): ?>
                            <div class="planilha-exercicios">
                                <h4 style="color: #06b6d4; margin: 20px 0 15px 0; font-size: 1.1rem;">Planilha de Exercícios</h4>
                                <table class="planilha-view">
                                    <thead>
                                        <tr>
                                            <th>Exercício</th>
                                            <th>Séries</th>
                                            <th>Repetições</th>
                                            <th>Descrição</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($treino['exercicios'] as $exercicio): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($exercicio['exercicio']); ?></td>
                                                <td><?php echo $exercicio['series'] ? htmlspecialchars($exercicio['series']) : '-'; ?></td>
                                                <td><?php echo !empty($exercicio['repeticoes']) ? htmlspecialchars($exercicio['repeticoes']) : '-'; ?></td>
                                                <td><?php echo !empty($exercicio['descricao']) ? htmlspecialchars($exercicio['descricao']) : '-'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <div class="treino-actions">
                            <button onclick="imprimirTreino(<?php echo $treino['id']; ?>)" class="btn-print">
                                🖨️ Imprimir Treino
                            </button>
                            <?php if (!empty($treino['arquivo_planilha'])): ?>
                                <a href="download_treino.php?id=<?php echo $treino['id']; ?>" class="btn-download">
                                    📥 Baixar Planilha Anexada
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>






