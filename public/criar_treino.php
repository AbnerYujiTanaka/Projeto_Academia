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

// Processar envio do treino
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aluno_id = intval($_POST['aluno_id'] ?? 0);
    $nome_treino = trim($_POST['nome_treino'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $treinador_id = $_SESSION['usuario_id'];
    
    // Validações
    if (empty($aluno_id) || empty($nome_treino)) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'erro';
    } else {
        // Processar exercícios da planilha
        $exercicios = [];
        if (isset($_POST['exercicios']) && is_array($_POST['exercicios'])) {
            foreach ($_POST['exercicios'] as $index => $exercicio) {
                $exercicio_nome = trim($exercicio['exercicio'] ?? '');
                if (!empty($exercicio_nome)) {
                    $exercicios[] = [
                        'exercicio' => $exercicio_nome,
                        'series' => !empty($exercicio['series']) ? intval($exercicio['series']) : null,
                        'repeticoes' => trim($exercicio['repeticoes'] ?? ''),
                        'descricao' => trim($exercicio['descricao'] ?? ''),
                        'ordem' => $index
                    ];
                }
            }
        }
        
        if (empty($exercicios)) {
            $mensagem = 'Por favor, adicione pelo menos um exercício na planilha.';
            $tipo_mensagem = 'erro';
        } else {
            try {
                // Iniciar transação
                $pdo->beginTransaction();
                
                // Inserir treino
                $stmt = $pdo->prepare("INSERT INTO treinos (treinador_id, aluno_id, nome_treino, descricao, arquivo_planilha) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$treinador_id, $aluno_id, $nome_treino, $descricao, null]);
                $treino_id = $pdo->lastInsertId();
                
                // Inserir exercícios
                $stmt_exercicio = $pdo->prepare("INSERT INTO treino_exercicios (treino_id, exercicio, series, repeticoes, descricao, ordem) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($exercicios as $exercicio) {
                    $stmt_exercicio->execute([
                        $treino_id,
                        $exercicio['exercicio'],
                        $exercicio['series'],
                        $exercicio['repeticoes'],
                        $exercicio['descricao'],
                        $exercicio['ordem']
                    ]);
                }
                
                // Confirmar transação
                $pdo->commit();
                
                $mensagem = 'Treino enviado com sucesso!';
                $tipo_mensagem = 'sucesso';
                
                // Limpar campos após sucesso
                $_POST = array();
            } catch (PDOException $e) {
                // Reverter transação em caso de erro
                $pdo->rollBack();
                $mensagem = 'Erro ao salvar treino: ' . $e->getMessage();
                $tipo_mensagem = 'erro';
            }
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

$nome_usuario = !empty($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Treinador';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Treino - NEON GYM</title>
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
        .form-group textarea,
        .form-group input[type="file"] {
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
        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group input[type="file"] {
            padding: 8px;
            cursor: pointer;
        }
        .form-group small {
            display: block;
            color: #9ca3af;
            margin-top: 5px;
            font-size: 0.85rem;
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
        .planilha-container {
            margin-top: 10px;
        }
        .planilha-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #27272a;
            border-radius: 8px;
            overflow: hidden;
        }
        .planilha-table thead {
            background-color: rgba(6, 182, 212, 0.2);
        }
        .planilha-table th {
            padding: 12px;
            text-align: left;
            color: #06b6d4;
            font-weight: 600;
            border-bottom: 2px solid rgba(6, 182, 212, 0.3);
        }
        .planilha-table td {
            padding: 10px;
            border-bottom: 1px solid rgba(6, 182, 212, 0.1);
        }
        .planilha-input {
            width: 100%;
            padding: 8px;
            background-color: #18181b;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 5px;
            color: #fff;
            font-size: 0.9rem;
            box-sizing: border-box;
        }
        .planilha-input:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.1);
        }
        .btn-add {
            background-color: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
            padding: 10px 20px;
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-add:hover {
            background-color: #06b6d4;
            color: #000;
        }
        .btn-remove {
            background-color: rgba(255, 65, 54, 0.2);
            color: #ff4136;
            padding: 6px 12px;
            border: 1px solid rgba(255, 65, 54, 0.3);
            border-radius: 5px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-remove:hover {
            background-color: #ff4136;
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Criar Novo Treino</h1>
            <p>Crie uma planilha de treino personalizada com exercícios, séries e repetições</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
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

            <div class="form-group">
                <label for="nome_treino">Nome do Treino <span class="required">*</span></label>
                <input type="text" name="nome_treino" id="nome_treino" required 
                       value="<?php echo htmlspecialchars($_POST['nome_treino'] ?? ''); ?>"
                       placeholder="Ex: Treino A - Peito e Tríceps">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" 
                          placeholder="Descreva o treino, objetivos, observações..."><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Planilha de Treino <span class="required">*</span></label>
                <div class="planilha-container">
                    <table id="planilha-treino" class="planilha-table">
                        <thead>
                            <tr>
                                <th>Exercício</th>
                                <th>Séries</th>
                                <th>Repetições</th>
                                <th>Descrição</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="planilha-body">
                            <tr class="planilha-row">
                                <td>
                                    <input type="text" name="exercicios[0][exercicio]" class="planilha-input" placeholder="Ex: Supino Reto" required>
                                </td>
                                <td>
                                    <input type="number" name="exercicios[0][series]" class="planilha-input" placeholder="Ex: 4" min="1">
                                </td>
                                <td>
                                    <input type="text" name="exercicios[0][repeticoes]" class="planilha-input" placeholder="Ex: 10-12">
                                </td>
                                <td>
                                    <input type="text" name="exercicios[0][descricao]" class="planilha-input" placeholder="Observações...">
                                </td>
                                <td>
                                    <button type="button" class="btn-remove" onclick="removerLinha(this)">Remover</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn-add" onclick="adicionarLinha()">+ Adicionar Exercício</button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Enviar Treino</button>
        </form>

        <div style="text-align: center;">
            <a href="dashboard_treinador.php" class="btn-back">← Voltar ao Dashboard</a>
        </div>
    </div>

    <script>
        let contadorLinhas = 1;

        function adicionarLinha() {
            const tbody = document.getElementById('planilha-body');
            const novaLinha = document.createElement('tr');
            novaLinha.className = 'planilha-row';
            novaLinha.innerHTML = `
                <td>
                    <input type="text" name="exercicios[${contadorLinhas}][exercicio]" class="planilha-input" placeholder="Ex: Supino Reto" required>
                </td>
                <td>
                    <input type="number" name="exercicios[${contadorLinhas}][series]" class="planilha-input" placeholder="Ex: 4" min="1">
                </td>
                <td>
                    <input type="text" name="exercicios[${contadorLinhas}][repeticoes]" class="planilha-input" placeholder="Ex: 10-12">
                </td>
                <td>
                    <input type="text" name="exercicios[${contadorLinhas}][descricao]" class="planilha-input" placeholder="Observações...">
                </td>
                <td>
                    <button type="button" class="btn-remove" onclick="removerLinha(this)">Remover</button>
                </td>
            `;
            tbody.appendChild(novaLinha);
            contadorLinhas++;
        }

        function removerLinha(btn) {
            const tbody = document.getElementById('planilha-body');
            if (tbody.children.length > 1) {
                btn.closest('tr').remove();
            } else {
                alert('É necessário ter pelo menos um exercício na planilha.');
            }
        }
    </script>

</body>
</html>


