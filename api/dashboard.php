<?php
// Headers JSON primero
header('Content-Type: application/json; charset=utf-8');

require_once '../config/config.php';

// Solo usuarios logueados
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// ==================== DASHBOARD PEI MULTIANUAL ====================
if ($action === 'pei') {
    try {
        $desde = isset($_GET['desde']) ? intval($_GET['desde']) : 2025;
        $hasta = isset($_GET['hasta']) ? intval($_GET['hasta']) : 2028;
        
        // Validar años
        if ($desde < 2020 || $hasta > 2030 || $desde > $hasta) {
            $desde = 2025;
            $hasta = 2028;
        }
        
        $stmt = $mysqli->prepare("
            SELECT e.anio_inicio, COUNT(pe.id_prod_estr) as productos
            FROM ejes e
            LEFT JOIN resultados r ON e.id_eje = r.id_eje
            LEFT JOIN productos_estrategicos pe ON r.id_resultado = pe.id_resultado
            WHERE e.anio_inicio BETWEEN ? AND ?
            GROUP BY e.anio_inicio
            ORDER BY e.anio_inicio
        ");
        if (!$stmt) {
            throw new Exception('Error preparando consulta: ' . $mysqli->error);
        }
        
        $stmt->bind_param("ii", $desde, $hasta);
        if (!$stmt->execute()) {
            throw new Exception('Error ejecutando consulta: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $productos_por_ano = [];
        while ($row = $result->fetch_assoc()) {
            $productos_por_ano[$row['anio_inicio']] = $row['productos'];
        }
        
        $data = [];
        for ($anio = $desde; $anio <= $hasta; $anio++) {
            $productos = $productos_por_ano[$anio] ?? 0;
            // Simular avance basado en productos (ej: cada producto = 10% avance)
            $avance = min($productos * 10, 95);
            $sem = getSemaforo($avance);
            
            $data[] = [
                'anio' => $anio,
                'avance' => $avance,
                'semaforo' => $sem['color'],
                'obs' => $productos > 0 ? 'Productos definidos' : 'Sin productos'
            ];
        }
        
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error interno del servidor', 'message' => $e->getMessage()]);
        exit;
    }
}

// ==================== DASHBOARD POA POR ÁREAS ====================
if ($action === 'poa-areas') {
    $stmt = $mysqli->prepare("
        SELECT a.id_area, a.nombre,
               COALESCE(AVG(t.avance_fisico), 0) as avance_fisico,
               COALESCE(SUM(pa.monto_plan) / NULLIF(SUM(t.meta_financiera), 0) * 100, 0) as avance_financiero
        FROM areas a
        LEFT JOIN productos_poa pp ON pp.id_area = a.id_area
        LEFT JOIN actividades act ON act.id_producto = pp.id_producto
        LEFT JOIN tareas t ON t.id_actividad = act.id_actividad
        LEFT JOIN presupuesto_actividad pa ON pa.id_actividad = act.id_actividad
        GROUP BY a.id_area, a.nombre
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $areas = [];
    
    while ($row = $result->fetch_assoc()) {
        $fisico = round($row['avance_fisico'], 2);
        $financiero = round(min($row['avance_financiero'], 100), 2); // Cap at 100%
        $ponderacion = round(($fisico + $financiero) / 2, 1);
        $sem = getSemaforo($ponderacion);
        
        $areas[] = [
            'area_id' => $row['id_area'],
            'area' => $row['nombre'],
            'fisico' => $fisico,
            'financiero' => $financiero,
            'ponderacion' => $ponderacion,
            'semaforo' => $sem['color']
        ];
    }
    
    echo json_encode($areas);
    exit;
}

// ==================== ALERTAS ====================
if ($action === 'alertas') {
    $alertas = [
        [
            'codigo' => 'A01',
            'titulo' => 'Ventana Q2 abierta',
            'detalle' => 'Cierra en 12 días',
            'prioridad' => 'alta',
            'origen' => 'Sistema',
            'fecha' => '2026-04-20'
        ],
        [
            'codigo' => 'A02',
            'titulo' => 'Evidencias pendientes',
            'detalle' => '7 evidencias sin aprobar',
            'prioridad' => 'media',
            'origen' => 'POA',
            'fecha' => '2026-04-18'
        ],
        [
            'codigo' => 'A03',
            'titulo' => 'Actividades sin presupuesto',
            'detalle' => '3 actividades sin partidas asignadas',
            'prioridad' => 'alta',
            'origen' => 'Presupuesto',
            'fecha' => '2026-04-17'
        ]
    ];
    
    echo json_encode($alertas);
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
?>