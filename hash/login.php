<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Receber dados JSON
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Validar dados recebidos
if (!isset($input['email']) || !isset($input['senha_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$email = trim($input['email']);
$senha_hash = strtolower(trim($input['senha_hash'])); // Garantir minúsculas

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Conectar ao banco
$conn = getConnection();

try {
    // Buscar usuário com email e senha_hash correspondentes
    $stmt = $conn->prepare("SELECT id, email, data_criacao FROM usuarios WHERE email = ? AND senha_hash = ?");
    $stmt->bind_param("ss", $email, $senha_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'data_criacao' => $user['data_criacao']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos!']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

closeConnection($conn);
?>