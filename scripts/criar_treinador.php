<?php
/*
 * =========================================
 * SCRIPT PARA CRIAR CONTA DE TREINADOR
 * =========================================
 * Este script permite criar contas de treinador no sistema.
 * 
 * IMPORTANTE: 
 * - Após criar os treinadores necessários, considere remover ou proteger este arquivo
 * - Use senhas fortes para contas de treinador
 * - Este script é apenas para uso administrativo
 */

// Inclui o arquivo de conexão
require_once '../config/conexao.php';

// Variáveis para mensagens
$mensagem = '';
$tipo_mensagem = ''; // 'sucesso' ou 'erro'

// Processa o formulário se foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Pega os dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'erro';
    } elseif ($senha !== $confirmar_senha) {
        $mensagem = 'As senhas não coincidem.';
        $tipo_mensagem = 'erro';
    } elseif (strlen($senha) < 6) {
        $mensagem = 'A senha deve ter pelo menos 6 caracteres.';
        $tipo_mensagem = 'erro';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Email inválido.';
        $tipo_mensagem = 'erro';
    } elseif ($nome === $email) {
        $mensagem = 'O nome não pode ser igual ao email.';
        $tipo_mensagem = 'erro';
    } else {
        // Tudo validado, tenta criar o treinador
        try {
            // Verifica se o email já existe
            $sql_check = "SELECT id FROM usuarios WHERE email = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$email]);
            
            if ($stmt_check->fetch()) {
                $mensagem = 'Este email já está cadastrado no sistema.';
                $tipo_mensagem = 'erro';
            } else {
                // Criptografa a senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Insere o treinador no banco
                $sql = "INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario) VALUES (?, ?, ?, 'treinador')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $senha_hash]);
                
                $mensagem = "Treinador criado com sucesso! ID: " . $pdo->lastInsertId();
                $tipo_mensagem = 'sucesso';
                
                // Limpa os campos do formulário após sucesso
                $nome = '';
                $email = '';
            }
        } catch (PDOException $e) {
            $mensagem = "Erro ao criar treinador: " . $e->getMessage();
            $tipo_mensagem = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta de Treinador - NEON GYM</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #000000;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background-color: #18181b;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #06b6d4;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .header p {
            color: #9ca3af;
            font-size: 0.95rem;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-sucesso {
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }
        .alert-erro {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #fff;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background-color: #27272a;
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #06b6d4;
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            background-color: #22d3ee;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
        }
        .warning-box {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #ef4444;
            font-size: 0.85rem;
        }
        .warning-box strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏋️ Criar Treinador</h1>
            <p>Cadastre uma nova conta de treinador no sistema</p>
        </div>

        <div class="warning-box">
            <strong>⚠️ Atenção:</strong>
            Este script é para uso administrativo. Após criar os treinadores necessários, 
            considere remover ou proteger este arquivo por segurança.
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    placeholder="Nome do treinador" 
                    value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="treinador@neongym.com" 
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="senha">Senha *</label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    placeholder="Mínimo 6 caracteres" 
                    required
                    minlength="6"
                >
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha *</label>
                <input 
                    type="password" 
                    id="confirmar_senha" 
                    name="confirmar_senha" 
                    placeholder="Digite a senha novamente" 
                    required
                    minlength="6"
                >
            </div>

            <button type="submit" class="btn-submit">Criar Treinador</button>
        </form>
    </div>
</body>
</html>
