<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Deixe vazio se não tiver senha no XAMPP
define('DB_NAME', 'sistema_login');

// Criar conexão
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Verificar conexão
        if ($conn->connect_error) {
            throw new Exception("Falha na conexão: " . $conn->connect_error);
        }
        
        // Definir charset
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        die(json_encode([
            'success' => false,
            'message' => 'Erro ao conectar com o banco de dados: ' . $e->getMessage()
        ]));
    }
}

// Fechar conexão
function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>