<?php
    require_once '../config/conexao.php';
    require_once '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEON GYM - Recuperar Senha</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

    <main class="container">
      
        <div id="recover-page" class="page active">
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <h2>Recuperar Senha</h2>
                        <p>Digite seu email para receber o link de redefinição</p>
                    </div>

                    <?php if (isset($_GET['erro'])): ?>
                        <div class="error-message" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
                            <?php
                            switch($_GET['erro']) {
                                case 'email_invalido':
                                    echo 'Email inválido. Por favor, verifique o email informado.';
                                    break;
                                case 'email_nao_encontrado':
                                    echo 'Email não encontrado em nosso sistema.';
                                    break;
                                case 'erro_servidor':
                                    echo 'Erro no servidor. Tente novamente mais tarde.';
                                    break;
                                default:
                                    echo 'Ocorreu um erro. Tente novamente.';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['sucesso'])): ?>
                        <div class="success-message" style="background-color: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); color: #86efac; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
                            Link de recuperação enviado! Verifique sua caixa de entrada.
                        </div>
                    <?php endif; ?>

                    <form class="login-form" action="../actions/processa_recuperacao.php" method="post">
                        <!-- Email Input -->
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-submit">Enviar Link de Recuperação</button>
                    </form>

                    <!-- Back to Login Link -->
                    <div class="signup-link">
                        <span>Lembrou sua senha? </span>
                        <a href="login.php">Voltar ao Login</a>
                    </div>
                </div>

                <!-- Additional Info -->
                <p class="terms-text">
                    Caso não encontre o email, verifique sua caixa de spam
                </p>
            </div>
        </div>
    </main>

</body>
</html>

