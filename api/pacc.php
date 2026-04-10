<?php
require_once '../config/config.php';
if (!isset($_SESSION['id_usuario'])) exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));

// Verificar permisos
if (!tienePermiso([1])) exit(json_encode(['status' => 'error', 'message' => 'Permisos insuficientes']));

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'listar_renglones':
        $ano = $_GET['ano'];
        $areaId = $_GET['area_id'];
        $stmt = $mysqli->prepare("SELECT id_renglon, nombre, descripcion, estado FROM renglones_pacc WHERE ano=? AND id_area=? ORDER BY id_renglon");
        $stmt->bind_param("ii", $ano, $areaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_renglon':
        $id = $_POST['id'] ?? null;
        $ano = $_POST['ano'];
        $areaId = $_POST['area_id'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $estado = $_POST['estado'];
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE renglones_pacc SET nombre=?, descripcion=?, estado=? WHERE id_renglon=?");
            $stmt->bind_param("sssi", $nombre, $descripcion, $estado, $id);
            $antes = obtenerRenglon($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO renglones_pacc (ano, id_area, nombre, descripcion, estado) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $ano, $areaId, $nombre, $descripcion, $estado);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'renglones_pacc', $antes ?? null, obtenerRenglon($newId));
        echo json_encode(['status' => 'success', 'message' => 'Renglón guardado']);
        break;

    case 'obtener_renglon':
        $id = $_GET['id'];
        echo json_encode(obtenerRenglon($id));
        break;

    case 'eliminar_renglon':
        $id = $_POST['id'];
        $antes = obtenerRenglon($id);
        $stmt = $mysqli->prepare("DELETE FROM renglones_pacc WHERE id_renglon=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'renglones_pacc', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Renglón eliminado']);
        break;

    case 'listar_hitos':
        $renglonId = $_GET['renglon_id'];
        $stmt = $mysqli->prepare("SELECT id_hito, nombre, fecha_inicio, fecha_fin, estado FROM hitos_pacc WHERE id_renglon=? ORDER BY id_hito");
        $stmt->bind_param("i", $renglonId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_hito':
        $id = $_POST['id'] ?? null;
        $id_renglon = $_POST['id_renglon'];
        $nombre = $_POST['nombre'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        $estado = $_POST['estado'];
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE hitos_pacc SET nombre=?, fecha_inicio=?, fecha_fin=?, estado=? WHERE id_hito=?");
            $stmt->bind_param("ssssi", $nombre, $fecha_inicio, $fecha_fin, $estado, $id);
            $antes = obtenerHito($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO hitos_pacc (id_renglon, nombre, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $id_renglon, $nombre, $fecha_inicio, $fecha_fin, $estado);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'hitos_pacc', $antes ?? null, obtenerHito($newId));
        echo json_encode(['status' => 'success', 'message' => 'Hito guardado']);
        break;

    case 'obtener_hito':
        $id = $_GET['id'];
        echo json_encode(obtenerHito($id));
        break;

    case 'eliminar_hito':
        $id = $_POST['id'];
        $antes = obtenerHito($id);
        $stmt = $mysqli->prepare("DELETE FROM hitos_pacc WHERE id_hito=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'hitos_pacc', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Hito eliminado']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

function obtenerRenglon($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM renglones_pacc WHERE id_renglon=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function obtenerHito($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM hitos_pacc WHERE id_hito=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>