<?php
require_once '../config/config.php';
if (!isset($_SESSION['id_usuario'])) exit(json_encode(['status' => 'error', 'message' => 'No autorizado']));

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'generar':
        $tipo = $_GET['tipo'];
        $ano = $_GET['ano'];
        $areaId = $_GET['area_id'] ?? '';
        $data = [];
        if ($tipo === 'pei') {
            // Informe PEI: Ejes con productos estratégicos
            $stmt = $mysqli->prepare("
                SELECT e.nombre as eje, COUNT(pe.id_producto) as productos
                FROM ejes e
                LEFT JOIN resultados r ON e.id_eje = r.id_eje
                LEFT JOIN productos_estrategicos pe ON r.id_resultado = pe.id_resultado
                GROUP BY e.id_eje, e.nombre
                ORDER BY e.nombre
            ");
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } elseif ($tipo === 'poa') {
            // Informe POA: Áreas con actividades y tareas
            $query = "
                SELECT a.nombre as area, COUNT(ac.id_actividad) as actividades, COUNT(t.id_tarea) as tareas,
                       AVG(CASE WHEN t.avance_fisico IS NOT NULL THEN t.avance_fisico ELSE 0 END) as avance_promedio
                FROM areas a
                LEFT JOIN productos_poa pp ON a.id_area = pp.id_area AND pp.anio = ?
                LEFT JOIN actividades ac ON pp.id_producto = ac.id_producto
                LEFT JOIN tareas t ON ac.id_actividad = t.id_actividad
            ";
            $params = [$ano];
            if ($areaId) {
                $query .= " WHERE a.id_area = ?";
                $params[] = $areaId;
            }
            $query .= " GROUP BY a.id_area, a.nombre ORDER BY a.nombre";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } elseif ($tipo === 'presupuesto') {
            // Informe Presupuesto: Actividades con total presupuesto
            $query = "
                SELECT ac.nombre as actividad, SUM(pa.monto_plan) as presupuesto_total
                FROM actividades ac
                JOIN productos_poa pp ON ac.id_producto = pp.id_producto AND pp.anio = ?
                LEFT JOIN presupuesto_actividad pa ON ac.id_actividad = pa.id_actividad
            ";
            $params = [$ano];
            if ($areaId) {
                $query .= " WHERE pp.id_area = ?";
                $params[] = $areaId;
            }
            $query .= " GROUP BY ac.id_actividad, ac.nombre ORDER BY ac.nombre";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } elseif ($tipo === 'pacc') {
            // Informe PACC: Renglones con hitos
            $query = "
                SELECT rp.nombre as renglon, rp.estado, COUNT(hp.id_hito) as hitos
                FROM renglones_pacc rp
                LEFT JOIN hitos_pacc hp ON rp.id_renglon = hp.id_renglon
                WHERE rp.ano = ?
            ";
            $params = [$ano];
            if ($areaId) {
                $query .= " AND rp.id_area = ?";
                $params[] = $areaId;
            }
            $query .= " GROUP BY rp.id_renglon, rp.nombre, rp.estado ORDER BY rp.nombre";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode($data);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}
?>