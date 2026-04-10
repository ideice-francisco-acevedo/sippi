<?php
require_once '../config/config.php';
if (!isset($_SESSION['id_usuario'])) exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));

// Verificar permisos
if (!tienePermiso([1])) exit(json_encode(['status' => 'error', 'message' => 'Permisos insuficientes']));

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'listar_areas':
        $stmt = $mysqli->prepare("SELECT id_area, nombre FROM areas ORDER BY nombre");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'listar_productos_poa':
        $ano = $_GET['ano'];
        $areaId = $_GET['area_id'];
        $stmt = $mysqli->prepare("SELECT pp.id_producto as id_producto_poa, pe.nombre as producto_estrategico, a.nombre as area, pp.anio as ano FROM productos_poa pp JOIN productos_estrategicos pe ON pp.id_prod_estr = pe.id_prod_estr JOIN areas a ON pp.id_area = a.id_area WHERE pp.anio=? AND pp.id_area=? ORDER BY pp.id_producto");
        $stmt->bind_param("ii", $ano, $areaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_producto_poa':
        $id = $_POST['id'] ?? null;
        $id_producto_estrategico = $_POST['id_producto_estrategico'];
        $id_area = $_POST['id_area'];
        $ano = $_POST['ano'];
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE productos_poa SET id_prod_estr=?, id_area=?, anio=? WHERE id_producto=?");
            $stmt->bind_param("iiii", $id_producto_estrategico, $id_area, $ano, $id);
            $antes = obtenerProductoPoa($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO productos_poa (id_prod_estr, id_area, anio) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id_producto_estrategico, $id_area, $ano);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'productos_poa', $antes ?? null, obtenerProductoPoa($newId));
        echo json_encode(['status' => 'success', 'message' => 'Producto POA guardado']);
        break;

    case 'obtener_producto_poa':
        $id = $_GET['id'];
        echo json_encode(obtenerProductoPoa($id));
        break;

    case 'eliminar_producto_poa':
        $id = $_POST['id'];
        $antes = obtenerProductoPoa($id);
        $stmt = $mysqli->prepare("DELETE FROM productos_poa WHERE id_producto_poa=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'productos_poa', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Producto POA eliminado']);
        break;

    case 'listar_actividades':
        $productoPoaId = $_GET['producto_poa_id'];
        $stmt = $mysqli->prepare("SELECT a.id_actividad, a.nombre, a.descripcion, pp.id_producto as id_producto_poa, pe.nombre as producto_poa FROM actividades a JOIN productos_poa pp ON a.id_producto = pp.id_producto JOIN productos_estrategicos pe ON pp.id_prod_estr = pe.id_prod_estr WHERE a.id_producto=? ORDER BY a.id_actividad");
        $stmt->bind_param("i", $productoPoaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_actividad':
        $id = $_POST['id'] ?? null;
        $id_producto_poa = $_POST['id_producto_poa'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE actividades SET id_producto=?, nombre=?, descripcion=? WHERE id_actividad=?");
            $stmt->bind_param("issi", $id_producto_poa, $nombre, $descripcion, $id);
            $antes = obtenerActividad($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO actividades (id_producto, nombre, descripcion) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_producto_poa, $nombre, $descripcion);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'actividades', $antes ?? null, obtenerActividad($newId));
        echo json_encode(['status' => 'success', 'message' => 'Actividad guardada']);
        break;

    case 'obtener_actividad':
        $id = $_GET['id'];
        echo json_encode(obtenerActividad($id));
        break;

    case 'eliminar_actividad':
        $id = $_POST['id'];
        $antes = obtenerActividad($id);
        $stmt = $mysqli->prepare("DELETE FROM actividades WHERE id_actividad=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'actividades', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Actividad eliminada']);
        break;

    case 'listar_tareas':
        $actividadId = $_GET['actividad_id'];
        $stmt = $mysqli->prepare("SELECT t.id_tarea, t.nombre, t.indicador, t.meta_fisica, t.unidad, t.meta_financiera, a.nombre as actividad FROM tareas t JOIN actividades a ON t.id_actividad = a.id_actividad WHERE t.id_actividad=? ORDER BY t.id_tarea");
        $stmt->bind_param("i", $actividadId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_tarea':
        $id = $_POST['id'] ?? null;
        $id_actividad = $_POST['id_actividad'];
        $nombre = $_POST['nombre'];
        $indicador = $_POST['indicador'];
        $meta_fisica = $_POST['meta_fisica'];
        $unidad = $_POST['unidad'];
        $meta_financiera = $_POST['meta_financiera'];
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE tareas SET id_actividad=?, nombre=?, indicador=?, meta_fisica=?, unidad=?, meta_financiera=? WHERE id_tarea=?");
            $stmt->bind_param("issdsdi", $id_actividad, $nombre, $indicador, $meta_fisica, $unidad, $meta_financiera, $id);
            $antes = obtenerTarea($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO tareas (id_actividad, nombre, indicador, meta_fisica, unidad, meta_financiera) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdsd", $id_actividad, $nombre, $indicador, $meta_fisica, $unidad, $meta_financiera);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;

        // Handle file upload if present
        if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] == 0) {
            $file = $_FILES['evidencia'];
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = uniqid() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $hash = generarHash($filePath);
                $stmt = $mysqli->prepare("INSERT INTO evidencias (id_tarea, ruta_archivo, hash) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $newId, $fileName, $hash);
                $stmt->execute();
            }
        }

        registrarBitacora($id ? 'update' : 'insert', 'tareas', $antes ?? null, obtenerTarea($newId));
        echo json_encode(['status' => 'success', 'message' => 'Tarea guardada']);
        break;

    case 'obtener_tarea':
        $id = $_GET['id'];
        echo json_encode(obtenerTarea($id));
        break;

    case 'eliminar_tarea':
        $id = $_POST['id'];
        $antes = obtenerTarea($id);
        $stmt = $mysqli->prepare("DELETE FROM tareas WHERE id_tarea=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'tareas', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Tarea eliminada']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

function obtenerProductoPoa($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM productos_poa WHERE id_producto=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function obtenerActividad($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM actividades WHERE id_actividad=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function obtenerTarea($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM tareas WHERE id_tarea=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>