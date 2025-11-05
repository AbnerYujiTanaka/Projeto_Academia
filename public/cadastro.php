<?php
    require_once '../config/conexao.php';
    require_once '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEON GYM - Login</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
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
                        <h2>Bem-vindo</h2>
                        <p>Cadastre para começar seu treino</p>
                    </div>

                    <form class="login-form" action="../actions/processa_cadastro.php" method="post">
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" placeholder="Seu nome completo" required>
                        </div>

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

                       

                        <!-- Submit Button -->
                        <button type="submit" class="btn-submit">Cadastrar</button>
                    </form>

                    <!-- Sign Up Link -->
                    <div class="signup-link">
                        <span>Já tem uma conta? </span>
                        <a href="login.php">Entrar</a>
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



