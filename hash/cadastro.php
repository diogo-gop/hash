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

// Validar hash (deve ter 64 caracteres - SHA-256)
if (strlen($senha_hash) !== 64) {
    echo json_encode(['success' => false, 'message' => 'Hash de senha inválido']);
    exit;
}

// Conectar ao banco
$conn = getConnection();

try {
    // Verificar se email já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email já cadastrado!']);
        $stmt->close();
        closeConnection($conn);
        exit;
    }
    $stmt->close();
    
    // Inserir novo usuário
    $stmt = $conn->prepare("INSERT INTO usuarios (email, senha_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $senha_hash);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Usuário cadastrado com sucesso!',
            'user_id' => $conn->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar usuário']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

closeConnection($conn);
?>