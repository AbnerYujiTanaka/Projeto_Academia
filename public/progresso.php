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

$mensagem = '';
$tipo_mensagem = '';

// Processar envio de progresso
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['salvar_progresso'])) {
    $treino_id = intval($_POST['treino_id'] ?? 0);
    $data_treino = trim($_POST['data_treino'] ?? '');
    
    if (empty($treino_id) || empty($data_treino)) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'erro';
    } else {
        // Validar se pelo menos um campo de carga está preenchido
        $tem_carga = false;
        if (isset($_POST['exercicios']) && is_array($_POST['exercicios'])) {
            foreach ($_POST['exercicios'] as $dados) {
                if (!empty($dados['carga']) && floatval($dados['carga']) > 0) {
                    $tem_carga = true;
                    break;
                }
            }
        }
        
        if (!$tem_carga) {
            $mensagem = 'Por favor, preencha pelo menos um campo de Carga (kg) para salvar o progresso.';
            $tipo_mensagem = 'erro';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Processar cada exercício
                if (isset($_POST['exercicios']) && is_array($_POST['exercicios'])) {
                    foreach ($_POST['exercicios'] as $exercicio_id => $dados) {
                        $exercicio_id = intval($exercicio_id);
                        $carga = !empty($dados['carga']) ? floatval($dados['carga']) : null;
                        $series = !empty($dados['series']) ? intval($dados['series']) : null;
                        $repeticoes = !empty($dados['repeticoes']) ? trim($dados['repeticoes']) : null;
                        $observacoes = !empty($dados['observacoes']) ? trim($dados['observacoes']) : null;
                        
                        // Só salva se tiver carga preenchida
                        if ($carga !== null && $carga > 0) {
                            // Verificar se já existe registro para este exercício nesta data
                            $stmt_check = $pdo->prepare("
                                SELECT id FROM progresso_treino 
                                WHERE aluno_id = ? AND treino_id = ? AND exercicio_id = ? AND data_treino = ?
                            ");
                            $stmt_check->execute([$aluno_id, $treino_id, $exercicio_id, $data_treino]);
                            $existe = $stmt_check->fetch();
                            
                            if ($existe) {
                                // Atualizar registro existente
                                $stmt = $pdo->prepare("
                                    UPDATE progresso_treino 
                                    SET carga = ?, series_realizadas = ?, repeticoes_realizadas = ?, observacoes = ?
                                    WHERE id = ?
                                ");
                                $stmt->execute([$carga, $series, $repeticoes, $observacoes, $existe['id']]);
                            } else {
                                // Inserir novo registro
                                $stmt = $pdo->prepare("
                                    INSERT INTO progresso_treino 
                                    (aluno_id, treino_id, exercicio_id, carga, series_realizadas, repeticoes_realizadas, data_treino, observacoes) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                $stmt->execute([$aluno_id, $treino_id, $exercicio_id, $carga, $series, $repeticoes, $data_treino, $observacoes]);
                            }
                        }
                    }
                }
                
                $pdo->commit();
                $mensagem = 'Progresso salvo com sucesso!';
                $tipo_mensagem = 'sucesso';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensagem = 'Erro ao salvar progresso: ' . $e->getMessage();
                $tipo_mensagem = 'erro';
            }
        }
    }
}

// Buscar treinos do aluno com exercícios
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
                SELECT id, exercicio, series, repeticoes, descricao 
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

// Buscar progresso histórico (últimos 30 dias)
$progresso_historico = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, te.exercicio, t.nome_treino
        FROM progresso_treino p
        INNER JOIN treino_exercicios te ON p.exercicio_id = te.id
        INNER JOIN treinos t ON p.treino_id = t.id
        WHERE p.aluno_id = ? AND p.data_treino >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ORDER BY p.data_treino DESC, p.id DESC
        LIMIT 50
    ");
    $stmt->execute([$aluno_id]);
    $progresso_historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $progresso_historico = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Progresso - NEON GYM</title>
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
        .mensagem {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
        }
        .mensagem.sucesso {
            background-color: rgba(34, 211, 238, 0.2);
            border: 1px solid #22d3ee;
            color: #22d3ee;
        }
        .mensagem.erro {
            background-color: rgba(255, 65, 54, 0.2);
            border: 1px solid #ff4136;
            color: #ff4136;
        }
        .treino-section {
            margin-bottom: 40px;
            background-color: #27272a;
            border-radius: 10px;
            padding: 25px;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }
        .treino-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(6, 182, 212, 0.2);
        }
        .treino-header h3 {
            color: #06b6d4;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        .treino-header p {
            color: #9ca3af;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            background-color: #18181b;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 5px;
            color: #fff;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }
        .planilha-progresso {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #18181b;
            border-radius: 8px;
            overflow: hidden;
        }
        .planilha-progresso thead {
            background-color: rgba(6, 182, 212, 0.2);
        }
        .planilha-progresso th {
            padding: 12px;
            text-align: left;
            color: #06b6d4;
            font-weight: 600;
            border-bottom: 2px solid rgba(6, 182, 212, 0.3);
            font-size: 0.9rem;
        }
        .planilha-progresso td {
            padding: 10px;
            border-bottom: 1px solid rgba(6, 182, 212, 0.1);
        }
        .planilha-progresso input[type="number"],
        .planilha-progresso input[type="text"],
        .planilha-progresso textarea {
            width: 100%;
            padding: 8px;
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 5px;
            color: #fff;
            font-size: 0.85rem;
            box-sizing: border-box;
        }
        .planilha-progresso input:focus,
        .planilha-progresso textarea:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.1);
        }
        .btn-submit {
            background-color: #06b6d4;
            color: #000;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        .btn-submit:hover {
            background-color: #22d3ee;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
        }
        .historico-section {
            margin-top: 40px;
            background-color: #27272a;
            border-radius: 10px;
            padding: 25px;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }
        .historico-section h3 {
            color: #06b6d4;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .historico-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #18181b;
            border-radius: 8px;
            overflow: hidden;
        }
        .historico-table thead {
            background-color: rgba(6, 182, 212, 0.2);
        }
        .historico-table th {
            padding: 12px;
            text-align: left;
            color: #06b6d4;
            font-weight: 600;
            border-bottom: 2px solid rgba(6, 182, 212, 0.3);
            font-size: 0.9rem;
        }
        .historico-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(6, 182, 212, 0.1);
            color: #fff;
            font-size: 0.9rem;
        }
        .historico-table tbody tr:hover {
            background-color: rgba(6, 182, 212, 0.05);
        }
        .sem-treinos {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
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
            <h1>Meu Progresso</h1>
            <p>Anote as cargas utilizadas durante seus treinos e acompanhe sua evolução</p>
        </div>

        <a href="dashboard_aluno.php" class="btn-back">← Voltar ao Dashboard</a>

        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($treinos)): ?>
            <div class="sem-treinos">
                <h3>Nenhum treino encontrado</h3>
                <p>Você ainda não recebeu nenhum treino do seu treinador.</p>
            </div>
        <?php else: ?>
            <?php foreach ($treinos as $treino): ?>
                <?php if (!empty($treino['exercicios'])): ?>
                    <div class="treino-section">
                        <div class="treino-header">
                            <h3><?php echo htmlspecialchars($treino['nome_treino']); ?></h3>
                            <p>Treinador: <?php echo htmlspecialchars($treino['treinador_nome']); ?></p>
                        </div>

                        <form method="POST" action="">
                            <input type="hidden" name="treino_id" value="<?php echo $treino['id']; ?>">
                            
                            <div class="form-group">
                                <label for="data_treino_<?php echo $treino['id']; ?>">Data do Treino <span style="color: #ff4136;">*</span></label>
                                <input type="date" 
                                       name="data_treino" 
                                       id="data_treino_<?php echo $treino['id']; ?>" 
                                       value="<?php echo date('Y-m-d'); ?>" 
                                       required>
                            </div>

                            <table class="planilha-progresso">
                                <thead>
                                    <tr>
                                        <th>Exercício</th>
                                        <th>Séries (Planejado)</th>
                                        <th>Repetições (Planejado)</th>
                                        <th>Carga (kg)</th>
                                        <th>Séries Realizadas</th>
                                        <th>Repetições Realizadas</th>
                                        <th>Observações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($treino['exercicios'] as $exercicio): ?>
                                        <tr>
                                            <td style="color: #06b6d4; font-weight: 600;">
                                                <?php echo htmlspecialchars($exercicio['exercicio']); ?>
                                            </td>
                                            <td style="color: #9ca3af;">
                                                <?php echo $exercicio['series'] ? htmlspecialchars($exercicio['series']) : '-'; ?>
                                            </td>
                                            <td style="color: #9ca3af;">
                                                <?php echo !empty($exercicio['repeticoes']) ? htmlspecialchars($exercicio['repeticoes']) : '-'; ?>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       name="exercicios[<?php echo $exercicio['id']; ?>][carga]" 
                                                       step="0.5" 
                                                       min="0" 
                                                       placeholder="Ex: 20.5">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       name="exercicios[<?php echo $exercicio['id']; ?>][series]" 
                                                       min="0" 
                                                       placeholder="Ex: 4">
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       name="exercicios[<?php echo $exercicio['id']; ?>][repeticoes]" 
                                                       placeholder="Ex: 10-12">
                                            </td>
                                            <td>
                                                <textarea name="exercicios[<?php echo $exercicio['id']; ?>][observacoes]" 
                                                          rows="2" 
                                                          placeholder="Observações..."></textarea>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <button type="submit" name="salvar_progresso" class="btn-submit" onclick="return validarCargas(this.form);">
                                Salvar Progresso
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!empty($progresso_historico)): ?>
                <div class="historico-section">
                    <h3>Histórico de Progresso (Últimos 30 dias)</h3>
                    <table class="historico-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Treino</th>
                                <th>Exercício</th>
                                <th>Carga (kg)</th>
                                <th>Séries</th>
                                <th>Repetições</th>
                                <th>Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($progresso_historico as $progresso): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($progresso['data_treino'])); ?></td>
                                    <td><?php echo htmlspecialchars($progresso['nome_treino']); ?></td>
                                    <td style="color: #06b6d4;"><?php echo htmlspecialchars($progresso['exercicio']); ?></td>
                                    <td><?php echo $progresso['carga'] ? number_format($progresso['carga'], 1) . ' kg' : '-'; ?></td>
                                    <td><?php echo $progresso['series_realizadas'] ? htmlspecialchars($progresso['series_realizadas']) : '-'; ?></td>
                                    <td><?php echo !empty($progresso['repeticoes_realizadas']) ? htmlspecialchars($progresso['repeticoes_realizadas']) : '-'; ?></td>
                                    <td style="color: #9ca3af; font-size: 0.85rem;">
                                        <?php echo !empty($progresso['observacoes']) ? htmlspecialchars($progresso['observacoes']) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
         <?php endif; ?>
     </div>

     <script>
         function validarCargas(form) {
             // Buscar todos os campos de carga no formulário
             const camposCarga = form.querySelectorAll('input[name*="[carga]"]');
             let temCarga = false;
             
             // Verificar se pelo menos um campo de carga está preenchido
             camposCarga.forEach(function(campo) {
                 const valor = parseFloat(campo.value);
                 if (!isNaN(valor) && valor > 0) {
                     temCarga = true;
                 }
             });
             
             if (!temCarga) {
                 alert('Por favor, preencha pelo menos um campo de Carga (kg) para salvar o progresso.');
                 return false;
             }
             
             return true;
         }
     </script>
 
 </body>
 </html>

