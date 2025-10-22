<?php
    require_once 'conexao.php';
    require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEON GYM - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <main class="container">
      
        <div id="login-page" class="page active">
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" x2="3" y1="12" y2="12"/>
                            </svg>
                        </div>
                        <h2>Bem-vindo de volta</h2>
                        <p>Entre para continuar seu treino</p>
                    </div>

                    <form class="login-form" action="processa_login.php" method="post">
                        <!-- Email Input -->
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                        </div>

                        <!-- Password Input -->
                        <div class="form-group">
                            <label for="password">Senha</label>
                            <input type="password" id="password" name="senha" placeholder="••••••••" required>
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox">
                                <span>Lembrar-me</span>
                            </label>
                            <a href="#" class="forgot-link">Esqueceu a senha?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-submit">Entrar</button>
                    </form>

                    <!-- Sign Up Link -->
                    <div class="signup-link">
                        <span>Não tem uma conta? </span>
                        <a href="cadastro.php">Cadastre-se</a>
                    </div>
                </div>

                <!-- Additional Info -->
                <p class="terms-text">
                    Ao fazer login, você concorda com nossos Termos de Serviço
                </p>
            </div>
        </div>
    </main>

</body>
</html>
