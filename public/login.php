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
    <style>
        /* 
         * Para usar sua própria imagem:
         * 1. Coloque a imagem na pasta assets/images/
         * 2. Renomeie para: login-wallpaper.jpg (ou .png)
         * 3. O código abaixo já está configurado para usar essa imagem
         */
        body {
            background-image: url('../assets/images/login-wallpaper.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
            min-height: 100vh;
        }
        
        /* Fallback caso a imagem não seja encontrada */
        body {
            background-color: #000000;
        }
        
        /* Overlay escuro para melhorar legibilidade do formulário */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.75) 100%);
            z-index: -1;
        }
        
        /* Card de login com transparência e blur */
        .login-card {
            background-color: rgba(24, 24, 27, 0.03);
            backdrop-filter: blur(10px);
            box-shadow: 0 0 50px rgba(6, 182, 212, 0.2);
        }
        
        /* Garantir que o conteúdo fique acima do overlay */
        main.container {
            position: relative;
            z-index: 1;
        }
    </style>
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

                    <form class="login-form" action="../actions/processa_login.php" method="post">
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
                            <a href="recuperar_senha.php" class="forgot-link">Esqueceu a senha?</a>
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
