<?php
require_once '../config/config.php';
if (!isset($_SESSION['id_usuario'])) exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));

// Verificar permisos admin
if (!tienePermiso([1])) exit(json_encode(['status' => 'error', 'message' => 'Permisos insuficientes']));

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'listar_usuarios':
        $stmt = $mysqli->prepare("SELECT u.id_usuario, u.usuario, u.nombre_completo as nombre, r.nombre as rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.nombre_completo");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_usuario':
        $id = $_POST['id'] ?? null;
        $usuario = $_POST['usuario'];
        $nombre = $_POST['nombre'];
        $id_rol = $_POST['id_rol'];
        $password = $_POST['password'];
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE usuarios SET usuario=?, nombre_completo=?, id_rol=? WHERE id_usuario=?");
            $stmt->bind_param("ssii", $usuario, $nombre, $id_rol, $id);
            $antes = obtenerUsuario($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO usuarios (usuario, nombre_completo, id_rol, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $usuario, $nombre, $id_rol, $hashed);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'usuarios', $antes ?? null, obtenerUsuario($newId));
        echo json_encode(['status' => 'success', 'message' => 'Usuario guardado']);
        break;

    case 'obtener_usuario':
        $id = $_GET['id'];
        echo json_encode(obtenerUsuario($id));
        break;

    case 'eliminar_usuario':
        $id = $_POST['id'];
        $antes = obtenerUsuario($id);
        $stmt = $mysqli->prepare("DELETE FROM usuarios WHERE id_usuario=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'usuarios', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado']);
        break;

    case 'listar_roles':
        $stmt = $mysqli->prepare("SELECT id_rol, nombre, descripcion FROM roles ORDER BY nombre");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_rol':
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE roles SET nombre=?, descripcion=? WHERE id_rol=?");
            $stmt->bind_param("ssi", $nombre, $descripcion, $id);
            $antes = obtenerRol($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $descripcion);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'roles', $antes ?? null, obtenerRol($newId));
        echo json_encode(['status' => 'success', 'message' => 'Rol guardado']);
        break;

    case 'obtener_rol':
        $id = $_GET['id'];
        echo json_encode(obtenerRol($id));
        break;

    case 'eliminar_rol':
        $id = $_POST['id'];
        $antes = obtenerRol($id);
        $stmt = $mysqli->prepare("DELETE FROM roles WHERE id_rol=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'roles', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Rol eliminado']);
        break;

    case 'listar_bitacora':
        $stmt = $mysqli->prepare("
            SELECT b.id_log as id_bitacora, u.nombre_completo as usuario, b.accion, b.entidad, b.fecha
            FROM bitacora b
            JOIN usuarios u ON b.usuario_id = u.id_usuario
            ORDER BY b.fecha DESC LIMIT 100
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

function obtenerUsuario($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id_usuario, usuario, nombre_completo as nombre, id_rol FROM usuarios WHERE id_usuario=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function obtenerRol($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM roles WHERE id_rol=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>