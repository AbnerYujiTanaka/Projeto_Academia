<?php
session_start();

// Verificação se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?erro=2"); 
    exit(); 
}

require_once '../config/conexao.php';

$treino_id = intval($_GET['id'] ?? 0);

if ($treino_id <= 0) {
    header("Location: ver_treinos.php");
    exit();
}

// Buscar treino e verificar permissão
try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.id as aluno_id 
        FROM treinos t 
        INNER JOIN usuarios u ON t.aluno_id = u.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$treino_id]);
    $treino = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$treino) {
        header("Location: ver_treinos.php");
        exit();
    }
    
    // Verificar se o usuário tem permissão (é o aluno ou é treinador)
    $tem_permissao = false;
    
    if ($_SESSION['tipo_usuario'] === 'aluno' && $treino['aluno_id'] == $_SESSION['usuario_id']) {
        $tem_permissao = true;
    } elseif ($_SESSION['tipo_usuario'] === 'treinador' && $treino['treinador_id'] == $_SESSION['usuario_id']) {
        $tem_permissao = true;
    }
    
    if (!$tem_permissao) {
        header("Location: dashboard.php");
        exit();
    }
    
    if (empty($treino['arquivo_planilha'])) {
        header("Location: ver_treinos.php");
        exit();
    }
    
    $file_path = '../uploads/treinos/' . $treino['arquivo_planilha'];
    
    if (!file_exists($file_path)) {
        header("Location: ver_treinos.php");
        exit();
    }
    
    // Determinar tipo MIME
    $file_extension = strtolower(pathinfo($treino['arquivo_planilha'], PATHINFO_EXTENSION));
    $mime_types = [
        'pdf' => 'application/pdf',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    $mime_type = $mime_types[$file_extension] ?? 'application/octet-stream';
    
    // Enviar arquivo
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . htmlspecialchars($treino['nome_treino']) . '.' . $file_extension . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    readfile($file_path);
    exit();
    
} catch (PDOException $e) {
    header("Location: ver_treinos.php");
    exit();
}
?>


