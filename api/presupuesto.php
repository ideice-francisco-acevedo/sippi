<?php
require_once '../config/config.php';
if (!isset($_SESSION['id_usuario'])) exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));

// Verificar permisos
if (!tienePermiso([1])) exit(json_encode(['status' => 'error', 'message' => 'Permisos insuficientes']));

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'listar_partidas':
        $stmt = $mysqli->prepare("SELECT id_partida, descripcion as nombre FROM partidas ORDER BY descripcion");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'listar_presupuestos':
        $ano = $_GET['ano'];
        $areaId = $_GET['area_id'];
        $stmt = $mysqli->prepare("
            SELECT a.id_actividad, a.nombre as actividad, COALESCE(SUM(pa.monto_plan), 0) as total_presupuesto
            FROM actividades a
            JOIN productos_poa pp ON a.id_producto = pp.id_producto
            LEFT JOIN presupuesto_actividad pa ON a.id_actividad = pa.id_actividad
            WHERE pp.anio = ? AND pp.id_area = ?
            GROUP BY a.id_actividad, a.nombre
            ORDER BY a.nombre
        ");
        $stmt->bind_param("ii", $ano, $areaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'listar_detalle':
        $idActividad = $_GET['id_actividad'];
        $stmt = $mysqli->prepare("SELECT pa.id_pres as id_presupuesto, p.descripcion as partida, pa.monto_plan as monto_planeado FROM presupuesto_actividad pa JOIN partidas p ON pa.id_partida = p.id_partida WHERE pa.id_actividad=? ORDER BY pa.id_pres");
        $stmt->bind_param("i", $idActividad);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'guardar_presupuesto':
        $id = $_POST['id'] ?? null;
        $id_actividad = $_POST['id_actividad'];
        $id_partida = $_POST['id_partida'];
        $monto_planeado = $_POST['monto_planeado'];
        if ($id) {
            $stmt = $mysqli->prepare("UPDATE presupuesto_actividad SET id_partida=?, monto_plan=? WHERE id_pres=?");
            $stmt->bind_param("idi", $id_partida, $monto_planeado, $id);
            $antes = obtenerPresupuesto($id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO presupuesto_actividad (id_actividad, id_partida, monto_plan) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $id_actividad, $id_partida, $monto_planeado);
        }
        $stmt->execute();
        $newId = $id ?: $mysqli->insert_id;
        registrarBitacora($id ? 'update' : 'insert', 'presupuesto_actividad', $antes ?? null, obtenerPresupuesto($newId));
        echo json_encode(['status' => 'success', 'message' => 'Presupuesto guardado']);
        break;

    case 'obtener_presupuesto':
        $id = $_GET['id'];
        echo json_encode(obtenerPresupuesto($id));
        break;

    case 'eliminar_presupuesto':
        $id = $_POST['id'];
        $antes = obtenerPresupuesto($id);
        $stmt = $mysqli->prepare("DELETE FROM presupuesto_actividad WHERE id_pres=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        registrarBitacora('delete', 'presupuesto_actividad', $antes, null);
        echo json_encode(['status' => 'success', 'message' => 'Presupuesto eliminado']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

function obtenerPresupuesto($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id_pres as id_presupuesto, id_actividad, id_partida, monto_plan as monto_planeado, monto_ejec FROM presupuesto_actividad WHERE id_pres=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>