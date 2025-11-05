<?php
/*
 * =========================================
 * ARQUIVO DE PROCESSAMENTO DE CADASTRO
 * =========================================
 * * Recebe os dados do formulário (cadastro.php).
 * * Inclui a conexão (conexao.php).
 * * CRIPTOGRAFA a senha (password_hash).
 * * Insere o novo usuário no banco de dados.
 */

// 1. Inclui o arquivo de conexão
require '../config/conexao.php';

// 2. Verifica se o formulário foi enviado (via POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. Pega os dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_pura = trim($_POST['senha'] ?? ''); // A senha que o usuário digitou

    // 4. VALIDAÇÃO BÁSICA (Exemplo: verificar se campos estão vazios)
    // (Você pode adicionar mais validações aqui, como força da senha)
    if (empty($nome) || empty($email) || empty($senha_pura)) {
        // Redireciona de volta com erro
        header("Location: ../public/cadastro.php?erro=campos");
        exit;
    }
    
    // Validação adicional: garante que o nome não seja igual ao email
    if ($nome === $email) {
        header("Location: ../public/cadastro.php?erro=nome_invalido");
        exit;
    }

    // 5. *** O PASSO MAIS IMPORTANTE: CRIPTOGRAFAR A SENHA ***
    // Nunca, jamais, em hipótese alguma salve a senha pura no banco!
    $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

    // 6. Bloco try...catch para inserir no banco
    try {
        
        // 7. Prepara o SQL para INSERIR os dados
        $sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // 8. Executa o SQL, passando os dados
        $stmt->execute([$nome, $email, $senha_hash]);

        // 9. SUCESSO! Redireciona para o login com msg de sucesso
        header("Location: ../public/login.php?cadastro=sucesso");
        exit;

    } catch (PDOException $e) {
        
        // 10. TRATAMENTO DE ERROS
        // O erro '23000' é o código para "violação de integridade"
        // (como um email que já existe, pois definimos como UNIQUE)
        if ($e->getCode() == '23000') {
            // Email duplicado
            header("Location: ../public/cadastro.php?erro=email_existe");
            exit;
        } else {
            // Outro erro de banco de dados
            die("Erro ao cadastrar usuário: " . $e->getMessage());
        }
    }

} else {
    // Se tentarem acessar o arquivo diretamente
    header("Location: ../public/cadastro.php");
    exit;
}
?>