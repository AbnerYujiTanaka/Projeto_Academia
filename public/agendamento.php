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

$mensagem = '';
$tipo_mensagem = '';

// Processar agendamento
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $treinador_id = intval($_POST['treinador_id'] ?? 0);
    $data_consulta = trim($_POST['data_consulta'] ?? '');
    $hora_consulta = trim($_POST['hora_consulta'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    $aluno_id = $_SESSION['usuario_id'];
    
    // Validações
    if (empty($treinador_id) || empty($data_consulta) || empty($hora_consulta)) {
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
                // Verificar se o treinador existe e é realmente um treinador
                try {
                    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND tipo_usuario = 'treinador'");
                    $stmt->execute([$treinador_id]);
                    if (!$stmt->fetch()) {
                        $mensagem = 'Treinador inválido.';
                        $tipo_mensagem = 'erro';
                    } else {
                        // Verificar se já existe agendamento no mesmo horário
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
                                
                                $mensagem = 'Consulta agendada com sucesso!';
                                $tipo_mensagem = 'sucesso';
                                
                                // Limpar campos após sucesso
                                $_POST = array();
                            } catch (PDOException $e) {
                                $mensagem = 'Erro ao agendar consulta: ' . $e->getMessage();
                                $tipo_mensagem = 'erro';
                            }
                        }
                    }
                } catch (PDOException $e) {
                    $mensagem = 'Erro ao validar treinador: ' . $e->getMessage();
                    $tipo_mensagem = 'erro';
                }
            }
        }
    }
}

// Buscar lista de treinadores
try {
    $stmt = $pdo->query("SELECT id, nome, email FROM usuarios WHERE tipo_usuario = 'treinador' ORDER BY nome");
    $treinadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $treinadores = [];
}

// Buscar agendamentos do aluno
$agendamentos_aluno = [];
try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.nome as treinador_nome, u.email as treinador_email 
        FROM agendamentos a 
        INNER JOIN usuarios u ON a.treinador_id = u.id 
        WHERE a.aluno_id = ? 
        ORDER BY a.data_consulta DESC, a.hora_consulta DESC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $agendamentos_aluno = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $agendamentos_aluno = [];
}

$nome_usuario = !empty($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Aluno';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Consultas - NEON GYM</title>
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
            background-color: #27272a;
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
        .calendar-container {
            margin-top: 20px;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-bottom: 20px;
        }
        .calendar-header {
            text-align: center;
            padding: 10px;
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
            font-weight: 600;
            border-radius: 5px;
        }
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .calendar-day:hover {
            background-color: rgba(6, 182, 212, 0.2);
            border-color: #06b6d4;
        }
        .calendar-day.other-month {
            opacity: 0.3;
            cursor: not-allowed;
        }
        .calendar-day.selected {
            background-color: #06b6d4;
            color: #000;
            font-weight: 600;
        }
        .calendar-day.today {
            border-color: #22d3ee;
            border-width: 2px;
        }
        .calendar-day.past {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .calendar-nav button {
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
            border: 1px solid rgba(6, 182, 212, 0.3);
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .calendar-nav button:hover {
            background-color: #06b6d4;
            color: #000;
        }
        .calendar-nav h3 {
            color: #fff;
            margin: 0;
        }
        .agendamentos-list {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid rgba(6, 182, 212, 0.3);
        }
        .agendamentos-list h2 {
            color: #06b6d4;
            margin-bottom: 20px;
        }
        .agendamento-item {
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .agendamento-item h3 {
            color: #06b6d4;
            margin-bottom: 10px;
        }
        .agendamento-item p {
            color: #9ca3af;
            margin: 5px 0;
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
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Agendar Consulta</h1>
            <p>Selecione um treinador e escolha a data e horário para sua consulta</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="formAgendamento">
            <div class="form-group">
                <label for="treinador_id">Treinador <span class="required">*</span></label>
                <select name="treinador_id" id="treinador_id" required>
                    <option value="">Selecione um treinador</option>
                    <?php foreach ($treinadores as $treinador): ?>
                        <option value="<?php echo $treinador['id']; ?>" <?php echo (isset($_POST['treinador_id']) && $_POST['treinador_id'] == $treinador['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($treinador['nome'] . ' (' . $treinador['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Selecione a Data <span class="required">*</span></label>
                <div class="calendar-container">
                    <div class="calendar-nav">
                        <button type="button" onclick="changeMonth(-1)">← Mês Anterior</button>
                        <h3 id="monthYear"></h3>
                        <button type="button" onclick="changeMonth(1)">Próximo Mês →</button>
                    </div>
                    <div id="calendar" class="calendar"></div>
                    <input type="hidden" name="data_consulta" id="data_consulta" required>
                </div>
            </div>

            <div class="form-row">
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

            <button type="submit" class="btn-submit">Agendar Consulta</button>
        </form>

        <?php if (!empty($agendamentos_aluno)): ?>
            <div class="agendamentos-list">
                <h2>Meus Agendamentos</h2>
                <?php foreach ($agendamentos_aluno as $agendamento): ?>
                    <div class="agendamento-item">
                        <h3>Consulta com <?php echo htmlspecialchars($agendamento['treinador_nome']); ?></h3>
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
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="dashboard_aluno.php" class="btn-back">← Voltar ao Dashboard</a>
        </div>
    </div>

    <script>
        let currentDate = new Date();
        let selectedDate = null;

        function renderCalendar() {
            const calendar = document.getElementById('calendar');
            const monthYear = document.getElementById('monthYear');
            
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            const monthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                              'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            
            monthYear.textContent = `${monthNames[month]} ${year}`;
            
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            calendar.innerHTML = '';
            
            // Cabeçalho dos dias da semana
            const weekDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            weekDays.forEach(day => {
                const header = document.createElement('div');
                header.className = 'calendar-header';
                header.textContent = day;
                calendar.appendChild(header);
            });
            
            // Dias do mês anterior
            const prevMonthDays = new Date(year, month, 0).getDate();
            for (let i = firstDay - 1; i >= 0; i--) {
                const day = document.createElement('div');
                day.className = 'calendar-day other-month';
                day.textContent = prevMonthDays - i;
                calendar.appendChild(day);
            }
            
            // Dias do mês atual
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day;
                
                const date = new Date(year, month, day);
                if (date < today) {
                    dayElement.classList.add('past');
                } else if (date.getTime() === today.getTime()) {
                    dayElement.classList.add('today');
                }
                
                if (selectedDate && date.getTime() === selectedDate.getTime()) {
                    dayElement.classList.add('selected');
                }
                
                dayElement.addEventListener('click', function() {
                    if (!this.classList.contains('past') && !this.classList.contains('other-month')) {
                        // Remove seleção anterior
                        document.querySelectorAll('.calendar-day.selected').forEach(el => {
                            el.classList.remove('selected');
                        });
                        this.classList.add('selected');
                        selectedDate = new Date(year, month, day);
                        document.getElementById('data_consulta').value = formatDate(selectedDate);
                    }
                });
                
                calendar.appendChild(dayElement);
            }
            
            // Preencher dias restantes
            const totalCells = calendar.children.length;
            const remainingCells = 42 - totalCells; // 6 semanas * 7 dias
            for (let i = 1; i <= remainingCells; i++) {
                const day = document.createElement('div');
                day.className = 'calendar-day other-month';
                day.textContent = i;
                calendar.appendChild(day);
            }
        }
        
        function changeMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            renderCalendar();
        }
        
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        
        // Inicializar calendário
        renderCalendar();
    </script>

</body>
</html>

