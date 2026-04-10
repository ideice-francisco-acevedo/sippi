<?php
require_once '../config/config.php';
if (!isset($_SESSION['id_usuario'])) exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));

// Verificar permisos (asumir rol 1 puede editar)
if (!tienePermiso([1])) exit(json_encode(['status' => 'error', 'message' => 'Permisos insuficientes']));

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'listar_ejes':
        $stmt = $mysqli->prepare("SELECT id_eje, nombre, descripcion FROM ejes ORDER BY id_eje");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_eje':
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        if (empty($nombre)) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es obligatorio']);
            break;
        }
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE ejes SET nombre=?, descripcion=? WHERE id_eje=?");
            $stmt->bind_param("ssi", $nombre, $descripcion, $id);
            $antes = obtenerEje($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO ejes (nombre, descripcion) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $descripcion);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'ejes', $antes ?? null, obtenerEje($newId));
        echo json_encode(['status' => 'success', 'message' => 'Eje guardado']);
        break;

    case 'obtener_eje':
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID no especificado']);
            break;
        }
        echo json_encode(obtenerEje($id));
        break;

    case 'eliminar_eje':
        $id = $_POST['id'] ?? 0;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID no especificado']);
            break;
        }
        $antes = obtenerEje($id);
        $stmt = $mysqli->prepare("DELETE FROM ejes WHERE id_eje=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'ejes', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Eje eliminado']);
        break;

    case 'listar_resultados':
        $ejeId = $_GET['eje_id'] ?? 0;
        if (!$ejeId) {
            echo json_encode(['status' => 'error', 'message' => 'ID de eje no especificado']);
            break;
        }
        $stmt = $mysqli->prepare("SELECT r.id_resultado, r.nombre, r.indicador, r.meta, e.nombre as eje FROM resultados r JOIN ejes e ON r.id_eje = e.id_eje WHERE r.id_eje=? ORDER BY r.id_resultado");
        $stmt->bind_param("i", $ejeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_resultado':
        $id = $_POST['id'] ?? null;
        $id_eje = $_POST['id_eje'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $indicador = $_POST['indicador'] ?? '';
        $meta = $_POST['meta'] ?? 0;
        if (empty($id_eje) || empty($nombre)) {
            echo json_encode(['status' => 'error', 'message' => 'Campos obligatorios faltantes']);
            break;
        }
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE resultados SET id_eje=?, nombre=?, indicador=?, meta=? WHERE id_resultado=?");
            $stmt->bind_param("issdi", $id_eje, $nombre, $indicador, $meta, $id);
            $antes = obtenerResultado($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO resultados (id_eje, nombre, indicador, meta) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $id_eje, $nombre, $indicador, $meta);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'resultados', $antes ?? null, obtenerResultado($newId));
        echo json_encode(['status' => 'success', 'message' => 'Resultado guardado']);
        break;

    case 'obtener_resultado':
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID no especificado']);
            break;
        }
        echo json_encode(obtenerResultado($id));
        break;

    case 'eliminar_resultado':
        $id = $_POST['id'] ?? 0;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID no especificado']);
            break;
        }
        $antes = obtenerResultado($id);
        $stmt = $mysqli->prepare("DELETE FROM resultados WHERE id_resultado=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'resultados', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Resultado eliminado']);
        break;

    case 'listar_productos':
        if (isset($_GET['all'])) {
            $stmt = $mysqli->prepare("SELECT id_prod_estr as id, nombre FROM productos_estrategicos ORDER BY nombre");
        } else {
            $resultadoId = $_GET['resultado_id'] ?? 0;
            if (!$resultadoId) {
                echo json_encode(['status' => 'error', 'message' => 'ID de resultado no especificado']);
                break;
            }
            $stmt = $mysqli->prepare("SELECT p.id_prod_estr as id, p.nombre, p.indicador, p.linea_base, p.meta, p.medio_verificacion, r.nombre as resultado FROM productos_estrategicos p JOIN resultados r ON p.id_resultado = r.id_resultado WHERE p.id_resultado=? ORDER BY p.id_prod_estr");
            $stmt->bind_param("i", $resultadoId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_producto':
        $id = $_POST['id'] ?? null;
        $id_resultado = $_POST['id_resultado'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $ano_inicio = $_POST['ano_inicio'] ?? date('Y');
        $ano_fin = $_POST['ano_fin'] ?? date('Y') + 1;
        if (empty($id_resultado) || empty($nombre)) {
            echo json_encode(['status' => 'error', 'message' => 'Campos obligatorios faltantes']);
            break;
        }
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE productos_estrategicos SET id_resultado=?, nombre=?, descripcion=?, ano_inicio=?, ano_fin=? WHERE id_producto=?");
            $stmt->bind_param("issiii", $id_resultado, $nombre, $descripcion, $ano_inicio, $ano_fin, $id);
            $antes = obtenerProducto($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO productos_estrategicos (id_resultado, nombre, descripcion, ano_inicio, ano_fin) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issii", $id_resultado, $nombre, $descripcion, $ano_inicio, $ano_fin);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'productos_estrategicos', $antes ?? null, obtenerProducto($newId));
        echo json_encode(['status' => 'success', 'message' => 'Producto guardado']);
        break;

    case 'obtener_producto':
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID no especificado']);
            break;
        }
        echo json_encode(obtenerProducto($id));
        break;

    case 'eliminar_producto':
        $id = $_POST['id'] ?? 0;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID no especificado']);
            break;
        }
        $antes = obtenerProducto($id);
        $stmt = $mysqli->prepare("DELETE FROM productos_estrategicos WHERE id_producto=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'productos_estrategicos', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Producto eliminado']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

function obtenerEje($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM ejes WHERE id_eje=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function obtenerResultado($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM resultados WHERE id_resultado=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function obtenerProducto($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM productos_estrategicos WHERE id_producto=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>