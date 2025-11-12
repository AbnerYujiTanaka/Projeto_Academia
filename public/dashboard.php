<?php
session_start();

// Verificação se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?erro=2"); 
    exit(); 
}

// Redirecionar baseado no tipo de usuário
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 'aluno';

if ($tipo_usuario === 'treinador') {
    header("Location: dashboard_treinador.php");
    exit();
} else {
    header("Location: dashboard_aluno.php");
    exit();
}
?>