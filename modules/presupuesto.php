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
    <h1>Módulo 3 – Presupuesto</h1>

    <!-- Filtros -->
    <div class="card">
        <label>Año: <select id="select-ano" onchange="cargarPresupuestos()">
            <option value="2025">2025</option>
            <option value="2026">2026</option>
            <option value="2027">2027</option>
            <option value="2028">2028</option>
        </select></label>
        <label>Área: <select id="select-area" onchange="cargarPresupuestos()"></select></label>
    </div>

    <!-- Presupuestos por Actividad -->
    <div class="card">
        <h2>Presupuestos por Actividad</h2>
        <table id="tabla-presupuestos">
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Presupuesto Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Detalle de Presupuesto -->
    <div class="card" id="detalle-presupuesto" style="display:none;">
        <h2>Detalle de Presupuesto para: <span id="actividad-nombre"></span></h2>
        <button onclick="mostrarFormulario('presupuesto')">Agregar Línea</button>
        <button onclick="volver()">Volver</button>
        <table id="tabla-detalle">
            <thead>
                <tr>
                    <th>Partida</th>
                    <th>Monto Planeado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Formulario Modal -->
    <div id="modal-formulario">
        <div class="modal-content">
            <span onclick="cerrarModal()" style="cursor:pointer; float:right;">&times;</span>
            <form id="form-entidad">
                <input type="hidden" id="entidad-tipo" name="tipo">
                <input type="hidden" id="entidad-id" name="id">
                <input type="hidden" id="actividad-id" name="id_actividad">
                <div id="campos-formulario"></div>
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>
</div>
</div>

<script>
let entidades = { areas: [], presupuestos: [], detalles: [] };
let actividadActual = null;

async function cargarAreas() {
    const res = await fetch('../api/poa.php?action=listar_areas');
    const data = await res.json();
    entidades.areas = data;
    actualizarSelect('select-area', data, 'id_area', 'nombre');
}

async function cargarPresupuestos() {
    const ano = document.getElementById('select-ano').value;
    const areaId = document.getElementById('select-area').value;
    if (!ano || !areaId) return;
    const res = await fetch(`../api/presupuesto.php?action=listar_presupuestos&ano=${ano}&area_id=${areaId}`);
    const data = await res.json();
    entidades.presupuestos = data;
    actualizarTablaPresupuestos(data);
    document.getElementById('detalle-presupuesto').style.display = 'none';
}

function actualizarTablaPresupuestos(data) {
    const tbody = document.querySelector('#tabla-presupuestos tbody');
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${item.actividad}</td>
            <td>$${parseFloat(item.total_presupuesto).toLocaleString()}</td>
            <td>
                <button onclick="verDetalle(${item.id_actividad}, '${item.actividad}')">Ver Detalle</button>
            </td>
        </tr>
    `).join('');
}

async function verDetalle(idActividad, nombre) {
    actividadActual = idActividad;
    document.getElementById('actividad-nombre').textContent = nombre;
    document.getElementById('actividad-id').value = idActividad;
    const res = await fetch(`../api/presupuesto.php?action=listar_detalle&id_actividad=${idActividad}`);
    const data = await res.json();
    entidades.detalles = data;
    actualizarTablaDetalle(data);
    document.getElementById('detalle-presupuesto').style.display = 'block';
    document.querySelector('.content > .card:nth-child(2)').style.display = 'none';
}

function actualizarTablaDetalle(data) {
    const tbody = document.querySelector('#tabla-detalle tbody');
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${item.partida}</td>
            <td>$${parseFloat(item.monto_planeado).toLocaleString()}</td>
            <td>
                <button onclick="editar('presupuesto', ${item.id_presupuesto})">Editar</button>
                <button onclick="eliminar('presupuesto', ${item.id_presupuesto})">Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function volver() {
    document.getElementById('detalle-presupuesto').style.display = 'none';
    actividadActual = null;
}

function actualizarSelect(selectId, data, valueKey, textKey) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Seleccionar</option>' + data.map(item => `<option value="${item[valueKey]}">${item[textKey]}</option>`).join('');
}

function mostrarFormulario(tipo) {
    document.getElementById('entidad-tipo').value = tipo;
    document.getElementById('entidad-id').value = '';
    const campos = {
        presupuesto: `
            <label>Partida: <select name="id_partida" required></select></label>
            <label>Monto Planeado: <input type="number" name="monto_planeado" step="0.01" required></label>
        `
    };
    document.getElementById('campos-formulario').innerHTML = campos[tipo];
    cargarPartidas();
    abrirModal();
}

async function cargarPartidas() {
    const res = await fetch('../api/presupuesto.php?action=listar_partidas');
    const data = await res.json();
    const select = document.querySelector('[name="id_partida"]');
    select.innerHTML = data.map(item => `<option value="${item.id_partida}">${item.nombre}</option>`).join('');
}

function cerrarModal() {
    window.cerrarModal();
}

document.getElementById('form-entidad').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'guardar_' + formData.get('tipo'));
    formData.append('id_actividad', actividadActual);
    const res = await fetch('../api/presupuesto.php', { method: 'POST', body: formData });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') {
        cerrarModal();
        verDetalle(actividadActual, document.getElementById('actividad-nombre').textContent);
    }
});

async function editar(tipo, id) {
    const res = await fetch(`../api/presupuesto.php?action=obtener_${tipo}&id=${id}`);
    const item = await res.json();
    mostrarFormulario(tipo);
    document.getElementById('entidad-id').value = id;
    Object.keys(item).forEach(key => {
        const el = document.querySelector(`[name="${key}"]`);
        if (el) el.value = item[key];
    });
}

async function eliminar(tipo, id) {
    if (!confirm('¿Eliminar?')) return;
    const res = await fetch('../api/presupuesto.php', { method: 'POST', body: new URLSearchParams({ action: 'eliminar_' + tipo, id }) });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') verDetalle(actividadActual, document.getElementById('actividad-nombre').textContent);
}

window.onload = () => {
    cargarAreas();
    document.getElementById('select-ano').value = new Date().getFullYear();
};
</script>

<?php require_once '../includes/footer.php'; ?></content>
<parameter name="filePath">C:\xampp\htdocs\sippi\modules\presupuesto.php