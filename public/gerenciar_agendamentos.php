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

$mensagem = '';
$tipo_mensagem = '';

// Processar criação de consulta pelo treinador
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] == 'criar') {
    $aluno_id = intval($_POST['aluno_id'] ?? 0);
    $data_consulta = trim($_POST['data_consulta'] ?? '');
    $hora_consulta = trim($_POST['hora_consulta'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    $treinador_id = $_SESSION['usuario_id'];
    
    // Validações
    if (empty($aluno_id) || empty($data_consulta) || empty($hora_consulta)) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'erro';
    } else {
        // Validar se a data não é no passado
        $data_consulta_obj = DateTime::createFromFormat('Y-m-d', $data_consulta);
        
        // Verificar se a data é válida
        if ($data_consulta_obj === false) {
            $mensagem = 'Data inválida. Por favor, selecione uma data válida.';
            $tipo_mensagem = 'erro';
        } else {
            $hoje = new DateTime();
            $hoje->setTime(0, 0, 0);
            
            if ($data_consulta_obj < $hoje) {
                $mensagem = 'Não é possível agendar consultas em datas passadas.';
                $tipo_mensagem = 'erro';
            } else {
                // Verificar se já existe agendamento no mesmo horário
                try {
                    $stmt = $pdo->prepare("SELECT id FROM agendamentos WHERE treinador_id = ? AND data_consulta = ? AND hora_consulta = ? AND status != 'cancelado'");
                    $stmt->execute([$treinador_id, $data_consulta, $hora_consulta]);
                    if ($stmt->fetch()) {
                        $mensagem = 'Este horário já está ocupado. Por favor, escolha outro horário.';
                        $tipo_mensagem = 'erro';
                    } else {
                        // Inserir agendamento
                        try {
                            $stmt = $pdo->prepare("INSERT INTO agendamentos (aluno_id, treinador_id, data_consulta, hora_consulta, observacoes, status) VALUES (?, ?, ?, ?, ?, 'agendado')");
                            $stmt->execute([$aluno_id, $treinador_id, $data_consulta, $hora_consulta, $observacoes]);
                            
                            $mensagem = 'Consulta criada com sucesso!';
                            $tipo_mensagem = 'sucesso';
                            
                            // Limpar campos após sucesso
                            $_POST = array();
                        } catch (PDOException $e) {
                            $mensagem = 'Erro ao criar consulta: ' . $e->getMessage();
                            $tipo_mensagem = 'erro';
                        }
                    }
                } catch (PDOException $e) {
                    $mensagem = 'Erro ao verificar disponibilidade: ' . $e->getMessage();
                    $tipo_mensagem = 'erro';
                }
            }
        }
    }
}

// Processar atualização de status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] == 'atualizar_status') {
    $agendamento_id = intval($_POST['agendamento_id'] ?? 0);
    $novo_status = trim($_POST['novo_status'] ?? '');
    $treinador_id = $_SESSION['usuario_id'];
    
    if ($agendamento_id > 0 && in_array($novo_status, ['agendado', 'confirmado', 'cancelado', 'concluido'])) {
        try {
            $stmt = $pdo->prepare("UPDATE agendamentos SET status = ? WHERE id = ? AND treinador_id = ?");
            $stmt->execute([$novo_status, $agendamento_id, $treinador_id]);
            
            if ($stmt->rowCount() > 0) {
                $mensagem = 'Status atualizado com sucesso!';
                $tipo_mensagem = 'sucesso';
            } else {
                $mensagem = 'Agendamento não encontrado ou você não tem permissão para alterá-lo.';
                $tipo_mensagem = 'erro';
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro ao atualizar status: ' . $e->getMessage();
            $tipo_mensagem = 'erro';
        }
    }
}

// Buscar lista de alunos
try {
    $stmt = $pdo->query("SELECT id, nome, email FROM usuarios WHERE tipo_usuario = 'aluno' ORDER BY nome");
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $alunos = [];
}

// Buscar agendamentos do treinador
$agendamentos = [];
try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.nome as aluno_nome, u.email as aluno_email 
        FROM agendamentos a 
        INNER JOIN usuarios u ON a.aluno_id = u.id 
        WHERE a.treinador_id = ? 
        ORDER BY a.data_consulta ASC, a.hora_consulta ASC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $agendamentos = [];
}

$nome_usuario = !empty($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Treinador';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Agendamentos - NEON GYM</title>
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
        .section {
            margin-bottom: 40px;
            padding: 25px;
            background-color: #27272a;
            border-radius: 10px;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }
        .section h2 {
            color: #06b6d4;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group label .required {
            color: #ff4136;
        }
        .form-group select,
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="time"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background-color: #18181b;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
            width: 100%;
        }
        .btn-submit:hover {
            background-color: #22d3ee;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
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
        .agendamentos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .agendamento-card {
            background-color: #18181b;
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .agendamento-card:hover {
            border-color: #06b6d4;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
        }
        .agendamento-card h3 {
            color: #06b6d4;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .agendamento-card p {
            color: #9ca3af;
            margin: 8px 0;
        }
        .agendamento-card strong {
            color: #fff;
        }
        .status-select {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .status-select select {
            flex: 1;
            padding: 8px;
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 5px;
            color: #fff;
        }
        .btn-update-status {
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
            padding: 8px 16px;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        .btn-update-status:hover {
            background-color: #06b6d4;
            color: #000;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 10px;
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
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }
        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Gerenciar Agendamentos</h1>
            <p>Visualize e gerencie todas as consultas agendadas com você</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Seção de Criar Consulta -->
        <div class="section">
            <h2>Criar Nova Consulta</h2>
            <form method="POST">
                <input type="hidden" name="acao" value="criar">
                
                <div class="form-group">
                    <label for="aluno_id">Aluno <span class="required">*</span></label>
                    <select name="aluno_id" id="aluno_id" required>
                        <option value="">Selecione um aluno</option>
                        <?php foreach ($alunos as $aluno): ?>
                            <option value="<?php echo $aluno['id']; ?>" <?php echo (isset($_POST['aluno_id']) && $_POST['aluno_id'] == $aluno['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($aluno['nome'] . ' (' . $aluno['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="data_consulta">Data <span class="required">*</span></label>
                        <input type="date" name="data_consulta" id="data_consulta" required 
                               value="<?php echo htmlspecialchars($_POST['data_consulta'] ?? ''); ?>"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="hora_consulta">Horário <span class="required">*</span></label>
                        <input type="time" name="hora_consulta" id="hora_consulta" required 
                               value="<?php echo htmlspecialchars($_POST['hora_consulta'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea name="observacoes" id="observacoes" 
                              placeholder="Informe o motivo da consulta ou observações adicionais..."><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-submit">Criar Consulta</button>
            </form>
        </div>

        <!-- Seção de Agendamentos -->
        <div class="section">
            <h2>Agendamentos com Você</h2>
            
            <?php if (empty($agendamentos)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <p>Nenhum agendamento encontrado.</p>
                </div>
            <?php else: ?>
                <div class="agendamentos-grid">
                    <?php foreach ($agendamentos as $agendamento): ?>
                        <div class="agendamento-card">
                            <h3><?php echo htmlspecialchars($agendamento['aluno_nome']); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($agendamento['aluno_email']); ?></p>
                            <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($agendamento['data_consulta'])); ?></p>
                            <p><strong>Horário:</strong> <?php echo date('H:i', strtotime($agendamento['hora_consulta'])); ?></p>
                            <?php if ($agendamento['observacoes']): ?>
                                <p><strong>Observações:</strong> <?php echo htmlspecialchars($agendamento['observacoes']); ?></p>
                            <?php endif; ?>
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
                            
                            <form method="POST" class="status-select" onsubmit="return confirm('Deseja realmente alterar o status desta consulta?');">
                                <input type="hidden" name="acao" value="atualizar_status">
                                <input type="hidden" name="agendamento_id" value="<?php echo $agendamento['id']; ?>">
                                <select name="novo_status" required>
                                    <option value="agendado" <?php echo $agendamento['status'] == 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                                    <option value="confirmado" <?php echo $agendamento['status'] == 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                    <option value="cancelado" <?php echo $agendamento['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    <option value="concluido" <?php echo $agendamento['status'] == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                </select>
                                <button type="submit" class="btn-update-status">Atualizar</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center;">
            <a href="dashboard_treinador.php" class="btn-back">← Voltar ao Dashboard</a>
        </div>
    </div>

</body>
</html>

