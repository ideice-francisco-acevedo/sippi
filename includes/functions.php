<?php
function getSemaforo($porcentaje) {
    if ($porcentaje < 70)  return ['color' => 'rojo',    'texto' => '🔴 Rojo'];
    if ($porcentaje < 80)  return ['color' => 'amarillo','texto' => '🟡 Amarillo'];
    if ($porcentaje < 90)  return ['color' => 'naranja', 'texto' => '🟠 Naranja'];
    return ['color' => 'verde', 'texto' => '🟢 Verde'];
}

function generarHash($archivo) {
    return hash_file('sha256', $archivo);
}

function tienePermiso($rolesPermitidos) {
    if (!isset($_SESSION['id_rol'])) return false;
    return in_array($_SESSION['id_rol'], $rolesPermitidos);
}

function registrarBitacora($accion, $entidad, $antes = null, $despues = null) {
    global $mysqli;
    $usuario_id = $_SESSION['id_usuario'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    $antes = $antes ? json_encode($antes) : null;
    $despues = $despues ? json_encode($despues) : null;
    
    $stmt = $mysqli->prepare("INSERT INTO bitacora (usuario_id, accion, entidad, antes, despues, ip) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $usuario_id, $accion, $entidad, $antes, $despues, $ip);
    $stmt->execute();
}
?>