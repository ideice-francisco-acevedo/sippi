<?php 
require_once '../config/config.php'; 
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="content">
    <h1>Módulo 5 – Informes</h1>
    
    <!-- Filtros -->
    <div class="card">
        <label>Tipo de Informe: <select id="select-tipo">
            <option value="pei">Avance PEI</option>
            <option value="poa">Avance POA</option>
            <option value="presupuesto">Presupuestos</option>
            <option value="pacc">PACC</option>
        </select></label>
        <label>Año: <select id="select-ano">
            <option value="2025">2025</option>
            <option value="2026">2026</option>
            <option value="2027">2027</option>
            <option value="2028">2028</option>
        </select></label>
        <label>Área: <select id="select-area"></select></label>
        <button onclick="generarInforme()">Generar Informe</button>
        <button onclick="exportarInforme()">Exportar a PDF</button>
    </div>

    <!-- Resultado del Informe -->
    <div class="card" id="resultado-informe" style="display:none;">
        <h2 id="titulo-informe"></h2>
        <div id="contenido-informe"></div>
    </div>
</div>

<script>
let entidades = { areas: [] };

async function cargarAreas() {
    const res = await fetch('../api/poa.php?action=listar_areas');
    const data = await res.json();
    entidades.areas = data;
    actualizarSelect('select-area', data, 'id_area', 'nombre');
}

function actualizarSelect(selectId, data, valueKey, textKey) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Todas</option>' + data.map(item => `<option value="${item[valueKey]}">${item[textKey]}</option>`).join('');
}

async function generarInforme() {
    const tipo = document.getElementById('select-tipo').value;
    const ano = document.getElementById('select-ano').value;
    const areaId = document.getElementById('select-area').value || '';
    const res = await fetch(`../api/informes.php?action=generar&tipo=${tipo}&ano=${ano}&area_id=${areaId}`);
    const data = await res.json();
    document.getElementById('titulo-informe').textContent = `Informe de ${tipo.toUpperCase()} - ${ano}`;
    document.getElementById('contenido-informe').innerHTML = generarTabla(data);
    document.getElementById('resultado-informe').style.display = 'block';
}

function generarTabla(data) {
    if (!data.length) return '<p>No hay datos disponibles.</p>';
    const headers = Object.keys(data[0]);
    const headerRow = headers.map(h => `<th>${h}</th>`).join('');
    const rows = data.map(row => `<tr>${headers.map(h => `<td>${row[h]}</td>`).join('')}</tr>`).join('');
    return `<table><thead><tr>${headerRow}</tr></thead><tbody>${rows}</tbody></table>`;
}

function exportarInforme() {
    // Simple export to CSV for now
    const table = document.querySelector('#contenido-informe table');
    if (!table) return alert('Genera el informe primero.');
    let csv = [];
    for (let row of table.rows) {
        let cols = [];
        for (let col of row.cells) {
            cols.push(col.innerText);
        }
        csv.push(cols.join(','));
    }
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'informe.csv';
    a.click();
}

window.onload = cargarAreas;
</script>

<?php require_once '../includes/footer.php'; ?></content>
<parameter name="filePath">C:\xampp\htdocs\sippi\modules\informes.php