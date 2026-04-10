<?php
require_once '../config/config.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'login') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $stmt = $mysqli->prepare("SELECT id_usuario, usuario, nombre_completo, id_rol, password FROM usuarios WHERE usuario = ? AND estado = 'activo'");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password===$row['password']) {
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['nombre'] = $row['nombre_completo'];
            $_SESSION['id_rol'] = $row['id_rol'];

            registrarBitacora('LOGIN', 'usuarios');

            echo json_encode(['status' => 'success', 'redirect' => 'modules/dashboard.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    }
    exit;
}

if ($action === 'logout') {
    try {
        if (isset($_SESSION['id_usuario'])) {
            registrarBitacora('LOGOUT', 'usuarios');
        }
    } catch (Exception $e) {
        // Log error but continue with logout
        error_log('Error al registrar bitácora de logout: ' . $e->getMessage());
    }
    
    session_destroy();
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}
?>