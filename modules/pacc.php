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
    <h1>Módulo 4 – PACC (Plan Anual de Control y Cumplimiento)</h1>

    <!-- Filtros -->
    <div class="card">
        <label>Año: <select id="select-ano" onchange="cargarRenglones()">
            <option value="2025">2025</option>
            <option value="2026">2026</option>
            <option value="2027">2027</option>
            <option value="2028">2028</option>
        </select></label>
        <label>Área: <select id="select-area" onchange="cargarRenglones()"></select></label>
    </div>

    <!-- Renglones PACC -->
    <div class="card">
        <h2>Renglones de Procesos</h2>
        <button onclick="mostrarFormulario('renglon')">Agregar Renglón</button>
        <table id="tabla-renglones">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Hitos -->
    <div class="card">
        <h2>Hitos por Renglón</h2>
        <select id="select-renglon-hitos" onchange="cargarHitos()">
            <option value="">Seleccionar Renglón</option>
        </select>
        <button onclick="mostrarFormulario('hito')">Agregar Hito</button>
        <table id="tabla-hitos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Estado</th>
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
                <div id="campos-formulario"></div>
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>
</div>

<script>
let entidades = { areas: [], renglones: [], hitos: [] };

async function cargarAreas() {
    const res = await fetch('../api/poa.php?action=listar_areas');
    const data = await res.json();
    entidades.areas = data;
    actualizarSelect('select-area', data, 'id_area', 'nombre');
}

async function cargarRenglones() {
    const ano = document.getElementById('select-ano').value;
    const areaId = document.getElementById('select-area').value;
    if (!ano || !areaId) return;
    const res = await fetch(`../api/pacc.php?action=listar_renglones&ano=${ano}&area_id=${areaId}`);
    const data = await res.json();
    entidades.renglones = data;
    actualizarTabla('renglones', data);
    actualizarSelect('select-renglon-hitos', data, 'id_renglon', 'nombre');
}

async function cargarHitos() {
    const renglonId = document.getElementById('select-renglon-hitos').value;
    if (!renglonId) return;
    const res = await fetch(`../api/pacc.php?action=listar_hitos&renglon_id=${renglonId}`);
    const data = await res.json();
    entidades.hitos = data;
    actualizarTabla('hitos', data);
}

function actualizarTabla(tipo, data) {
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    if (tipo === 'renglones') {
        tbody.innerHTML = data.map(item => `
            <tr>
                <td>${item.id_renglon}</td>
                <td>${item.nombre}</td>
                <td>${item.descripcion}</td>
                <td>${item.estado}</td>
                <td>
                    <button onclick="editar('${tipo}', ${item.id_renglon})">Editar</button>
                    <button onclick="eliminar('${tipo}', ${item.id_renglon})">Eliminar</button>
                </td>
            </tr>
        `).join('');
    } else if (tipo === 'hitos') {
        tbody.innerHTML = data.map(item => `
            <tr>
                <td>${item.id_hito}</td>
                <td>${item.nombre}</td>
                <td>${item.fecha_inicio}</td>
                <td>${item.fecha_fin}</td>
                <td>${item.estado}</td>
                <td>
                    <button onclick="editar('${tipo}', ${item.id_hito})">Editar</button>
                    <button onclick="eliminar('${tipo}', ${item.id_hito})">Eliminar</button>
                </td>
            </tr>
        `).join('');
    }
}

function actualizarSelect(selectId, data, valueKey, textKey) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Seleccionar</option>' + data.map(item => `<option value="${item[valueKey]}">${item[textKey]}</option>`).join('');
}

function mostrarFormulario(tipo) {
    document.getElementById('entidad-tipo').value = tipo;
    document.getElementById('entidad-id').value = '';
    const campos = {
        renglon: `
            <label>Nombre: <input type="text" name="nombre" required></label>
            <label>Descripción: <textarea name="descripcion" required></textarea></label>
            <label>Estado: <select name="estado" required>
                <option value="Pendiente">Pendiente</option>
                <option value="En Progreso">En Progreso</option>
                <option value="Completado">Completado</option>
            </select></label>
        `,
        hito: `
            <label>Renglón: <select name="id_renglon" required>${document.getElementById('select-renglon-hitos').innerHTML}</select></label>
            <label>Nombre: <input type="text" name="nombre" required></label>
            <label>Fecha Inicio: <input type="date" name="fecha_inicio" required></label>
            <label>Fecha Fin: <input type="date" name="fecha_fin" required></label>
            <label>Estado: <select name="estado" required>
                <option value="Pendiente">Pendiente</option>
                <option value="En Progreso">En Progreso</option>
                <option value="Completado">Completado</option>
            </select></label>
        `
    };
    document.getElementById('campos-formulario').innerHTML = campos[tipo];
    abrirModal();
}

function cerrarModal() {
    window.cerrarModal();
}

document.getElementById('form-entidad').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'guardar_' + formData.get('tipo'));
    const ano = document.getElementById('select-ano').value;
    const areaId = document.getElementById('select-area').value;
    formData.append('ano', ano);
    formData.append('area_id', areaId);
    const res = await fetch('../api/pacc.php', { method: 'POST', body: formData });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') {
        cerrarModal();
        cargarRenglones();
    }
});

async function editar(tipo, id) {
    const res = await fetch(`../api/pacc.php?action=obtener_${tipo}&id=${id}`);
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
    const res = await fetch('../api/pacc.php', { method: 'POST', body: new URLSearchParams({ action: 'eliminar_' + tipo, id }) });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') cargarRenglones();
}

window.onload = () => {
    cargarAreas();
    document.getElementById('select-ano').value = new Date().getFullYear();
};
</script>

<?php require_once '../includes/footer.php'; ?></content>
<parameter name="filePath">C:\xampp\htdocs\sippi\modules\pacc.php