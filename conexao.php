<?php
$host = 'localhost';
$username = 'root';
$password = '';
$db_name = 'academia_db';

try {

    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8";

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>