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
    <div class="module-header">
        <div>
            <h1><i class="fas fa-tasks"></i> Plan Operativo Anual</h1>
            <p class="module-subtitle">Gestión de Productos, Actividades y Tareas por Área y Año</p>
        </div>
        <div class="module-actions">
            <button class="btn" onclick="mostrarFormulario('producto_poa')">
                <i class="fas fa-plus"></i> Nuevo Producto POA
            </button>
            <button class="btn success" onclick="generarReportePOA()">
                <i class="fas fa-file-export"></i> Reporte
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="filter-bar">
            <div class="filter-group">
                <label>Año</label>
                <select id="select-ano" onchange="cargarProductos()" class="modern-select">
                    <option value="2025">2025</option>
                    <option value="2026" selected>2026</option>
                    <option value="2027">2027</option>
                    <option value="2028">2028</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Área</label>
                <select id="select-area" onchange="cargarProductos()" class="modern-select"></select>
            </div>
            <div class="filter-group">
                <button class="btn secondary" onclick="cargarProductos()">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Productos POA -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-cube"></i> Productos POA</h2>
            <div class="card-actions">
                <button class="btn-icon" onclick="mostrarFormulario('producto_poa')" title="Agregar Producto POA">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="modern-table" id="tabla-productos-poa">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto Estratégico</th>
                        <th>Área</th>
                        <th>Año</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Actividades -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-project-diagram"></i> Actividades</h2>
            <div class="card-actions">
                <button class="btn-icon" onclick="mostrarFormulario('actividad')" title="Agregar Actividad">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="filter-group">
                <label>Producto POA</label>
                <select id="select-producto-actividades" onchange="cargarActividades()" class="modern-select">
                    <option value="">Seleccionar Producto POA</option>
                </select>
            </div>
            <table class="modern-table" id="tabla-actividades">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto POA</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Tareas -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-tasks"></i> Tareas</h2>
            <div class="card-actions">
                <button class="btn-icon" onclick="mostrarFormulario('tarea')" title="Agregar Tarea">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="filter-group">
                <label>Actividad</label>
                <select id="select-actividad-tareas" onchange="cargarTareas()" class="modern-select">
                    <option value="">Seleccionar Actividad</option>
                </select>
            </div>
            <table class="modern-table" id="tabla-tareas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Actividad</th>
                        <th>Nombre</th>
                        <th>Indicador</th>
                        <th>Meta Física</th>
                        <th>Unidad</th>
                        <th>Meta Financiera</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Formulario Modal -->
    <div id="modal-formulario">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-titulo">Formulario</h3>
                <button class="btn-icon modal-close" onclick="cerrarModal()" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-entidad">
                    <input type="hidden" id="entidad-tipo" name="tipo">
                    <input type="hidden" id="entidad-id" name="id">
                    <div id="campos-formulario" class="form-fields"></div>
                    <div class="form-actions">
                        <button type="button" class="btn secondary" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="btn success">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let entidades = { productos_poa: [], actividades: [], tareas: [], areas: [] };

async function cargarAreas() {
    const res = await fetch('../api/poa.php?action=listar_areas');
    const data = await res.json();
    entidades.areas = data;
    actualizarSelect('select-area', data, 'id_area', 'nombre');
}

async function cargarProductos() {
    const ano = document.getElementById('select-ano').value;
    const areaId = document.getElementById('select-area').value;
    if (!ano || !areaId) return;
    const res = await fetch(`../api/poa.php?action=listar_productos_poa&ano=${ano}&area_id=${areaId}`);
    const data = await res.json();
    entidades.productos_poa = data;
    actualizarTabla('productos-poa', data);
    actualizarSelect('select-producto-actividades', data, 'id_producto_poa', 'producto_estrategico');
}

async function cargarActividades() {
    const productoId = document.getElementById('select-producto-actividades').value;
    if (!productoId) return;
    const res = await fetch(`../api/poa.php?action=listar_actividades&producto_poa_id=${productoId}`);
    const data = await res.json();
    entidades.actividades = data;
    actualizarTabla('actividades', data);
    actualizarSelect('select-actividad-tareas', data, 'id_actividad', 'nombre');
}

async function cargarTareas() {
    const actividadId = document.getElementById('select-actividad-tareas').value;
    if (!actividadId) return;
    const res = await fetch(`../api/poa.php?action=listar_tareas&actividad_id=${actividadId}`);
    const data = await res.json();
    entidades.tareas = data;
    actualizarTabla('tareas', data);
}

function actualizarTabla(tipo, data) {
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    if (tipo === 'productos-poa') {
        tbody.innerHTML = data.map(item => `
            <tr>
                <td>${item.id_producto_poa}</td>
                <td>${item.producto_estrategico}</td>
                <td>${item.area}</td>
                <td>${item.ano}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm secondary" onclick="editar('${tipo}', ${item.id_producto_poa})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm danger" onclick="eliminar('${tipo}', ${item.id_producto_poa})">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } else if (tipo === 'actividades') {
        tbody.innerHTML = data.map(item => `
            <tr>
                <td>${item.id_actividad}</td>
                <td>${item.producto_poa}</td>
                <td>${item.nombre}</td>
                <td>${item.descripcion}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm secondary" onclick="editar('${tipo}', ${item.id_actividad})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm danger" onclick="eliminar('${tipo}', ${item.id_actividad})">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } else if (tipo === 'tareas') {
        tbody.innerHTML = data.map(item => `
            <tr>
                <td>${item.id_tarea}</td>
                <td>${item.actividad}</td>
                <td>${item.nombre}</td>
                <td>${item.indicador}</td>
                <td>${item.meta_fisica}</td>
                <td>${item.unidad}</td>
                <td>${item.meta_financiera}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm secondary" onclick="editar('${tipo}', ${item.id_tarea})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm danger" onclick="eliminar('${tipo}', ${item.id_tarea})">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
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
    
    const titulos = {
        producto_poa: 'Nuevo Producto POA',
        actividad: 'Nueva Actividad',
        tarea: 'Nueva Tarea'
    };
    document.getElementById('modal-titulo').textContent = titulos[tipo] || 'Formulario';
    
    const campos = {
        producto_poa: `
            <div class="form-group">
                <label>Producto Estratégico</label>
                <select name="id_producto_estrategico" class="modern-select" required></select>
            </div>
            <div class="form-group">
                <label>Área</label>
                <select name="id_area" class="modern-select" required>${document.getElementById('select-area').innerHTML}</select>
            </div>
            <div class="form-group">
                <label>Año</label>
                <input type="number" name="ano" class="modern-input" value="${document.getElementById('select-ano').value}" required>
            </div>
        `,
        actividad: `
            <div class="form-group">
                <label>Producto POA</label>
                <select name="id_producto_poa" class="modern-select" required>${document.getElementById('select-producto-actividades').innerHTML}</select>
            </div>
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="modern-input" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="modern-textarea" rows="3" required></textarea>
            </div>
        `,
        tarea: `
            <div class="form-group">
                <label>Actividad</label>
                <select name="id_actividad" class="modern-select" required>${document.getElementById('select-actividad-tareas').innerHTML}</select>
            </div>
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="modern-input" required>
            </div>
            <div class="form-group">
                <label>Indicador</label>
                <input type="text" name="indicador" class="modern-input" required>
            </div>
            <div class="form-group">
                <label>Meta Física</label>
                <input type="number" name="meta_fisica" step="0.01" class="modern-input" required>
            </div>
            <div class="form-group">
                <label>Unidad</label>
                <input type="text" name="unidad" class="modern-input" required>
            </div>
            <div class="form-group">
                <label>Meta Financiera</label>
                <input type="number" name="meta_financiera" step="0.01" class="modern-input" required>
            </div>
            <div class="form-group">
                <label>Evidencia (opcional)</label>
                <input type="file" name="evidencia" accept=".pdf,.doc,.docx,.jpg,.png">
            </div>
        `
    };
    document.getElementById('campos-formulario').innerHTML = campos[tipo];
    if (tipo === 'producto_poa') {
        cargarProductosEstrategicos();
    }
    abrirModal();
}

async function cargarProductosEstrategicos() {
    const res = await fetch('../api/pei.php?action=listar_productos&all=1');
    const data = await res.json();
    const select = document.querySelector('[name="id_producto_estrategico"]');
    select.innerHTML = data.map(item => `<option value="${item.id_producto}">${item.nombre}</option>`).join('');
}

function cerrarModal() {
    window.cerrarModal();
}

document.getElementById('form-entidad').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'guardar_' + formData.get('tipo'));
    if (formData.get('tipo') === 'renglon' || formData.get('tipo') === 'hito') {
        const ano = document.getElementById('select-ano').value;
        const areaId = document.getElementById('select-area').value;
        formData.append('ano', ano);
        formData.append('area_id', areaId);
    }
    const res = await fetch('../api/poa.php', { method: 'POST', body: formData });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') {
        cerrarModal();
        if (formData.get('tipo').startsWith('producto') || formData.get('tipo') === 'actividad' || formData.get('tipo') === 'tarea') {
            cargarProductos();
        } else {
            cargarRenglones();
        }
    }
});

async function editar(tipo, id) {
    const res = await fetch(`../api/poa.php?action=obtener_${tipo}&id=${id}`);
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
    const res = await fetch('../api/poa.php', { method: 'POST', body: new URLSearchParams({ action: 'eliminar_' + tipo, id }) });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') cargarProductos();
}

window.onload = () => {
    cargarAreas();
    document.getElementById('select-ano').value = new Date().getFullYear();
};
</script>

<?php require_once '../includes/footer.php'; ?></content>
<parameter name="filePath">C:\xampp\htdocs\sippi\modules\poa.php