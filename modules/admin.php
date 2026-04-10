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
    <h1>Módulo 6 – Administración</h1>
    
    <!-- Pestañas -->
    <div class="tabs">
        <button class="tab-button active" onclick="mostrarTab('usuarios')">Usuarios</button>
        <button class="tab-button" onclick="mostrarTab('roles')">Roles</button>
        <button class="tab-button" onclick="mostrarTab('bitacora')">Bitácora</button>
    </div>

    <!-- Usuarios -->
    <div class="tab-content" id="tab-usuarios">
        <h2>Gestión de Usuarios</h2>
        <button onclick="mostrarFormulario('usuario')">Agregar Usuario</button>
        <table id="tabla-usuarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Roles -->
    <div class="tab-content" id="tab-roles" style="display:none;">
        <h2>Gestión de Roles</h2>
        <button onclick="mostrarFormulario('rol')">Agregar Rol</button>
        <table id="tabla-roles">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Bitácora -->
    <div class="tab-content" id="tab-bitacora" style="display:none;">
        <h2>Bitácora de Actividades</h2>
        <table id="tabla-bitacora">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Entidad</th>
                    <th>Fecha</th>
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
let entidades = { usuarios: [], roles: [], bitacora: [] };

async function cargarUsuarios() {
    const res = await fetch('../api/admin.php?action=listar_usuarios');
    const data = await res.json();
    entidades.usuarios = data;
    actualizarTabla('usuarios', data);
}

async function cargarRoles() {
    const res = await fetch('../api/admin.php?action=listar_roles');
    const data = await res.json();
    entidades.roles = data;
    actualizarTabla('roles', data);
}

async function cargarBitacora() {
    const res = await fetch('../api/admin.php?action=listar_bitacora');
    const data = await res.json();
    entidades.bitacora = data;
    actualizarTabla('bitacora', data);
}

function actualizarTabla(tipo, data) {
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    tbody.innerHTML = data.map(item => {
        if (tipo === 'usuarios') {
            return `
                <tr>
                    <td>${item.id_usuario}</td>
                    <td>${item.usuario}</td>
                    <td>${item.nombre}</td>
                    <td>${item.rol}</td>
                    <td>
                        <button onclick="editar('${tipo}', ${item.id_usuario})">Editar</button>
                        <button onclick="eliminar('${tipo}', ${item.id_usuario})">Eliminar</button>
                    </td>
                </tr>
            `;
        } else if (tipo === 'roles') {
            return `
                <tr>
                    <td>${item.id_rol}</td>
                    <td>${item.nombre}</td>
                    <td>${item.descripcion}</td>
                    <td>
                        <button onclick="editar('${tipo}', ${item.id_rol})">Editar</button>
                        <button onclick="eliminar('${tipo}', ${item.id_rol})">Eliminar</button>
                    </td>
                </tr>
            `;
        } else if (tipo === 'bitacora') {
            return `
                <tr>
                    <td>${item.id_bitacora}</td>
                    <td>${item.usuario}</td>
                    <td>${item.accion}</td>
                    <td>${item.entidad}</td>
                    <td>${item.fecha}</td>
                </tr>
            `;
        }
    }).join('');
}

function mostrarTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
    document.getElementById(`tab-${tab}`).style.display = 'block';
    document.querySelector(`button[onclick="mostrarTab('${tab}')"]`).classList.add('active');
    if (tab === 'usuarios') cargarUsuarios();
    else if (tab === 'roles') cargarRoles();
    else if (tab === 'bitacora') cargarBitacora();
}

function mostrarFormulario(tipo) {
    document.getElementById('entidad-tipo').value = tipo;
    document.getElementById('entidad-id').value = '';
    const campos = {
        usuario: `
            <label>Usuario: <input type="text" name="usuario" required></label>
            <label>Nombre: <input type="text" name="nombre" required></label>
            <label>Rol: <select name="id_rol" required></select></label>
            <label>Contraseña: <input type="password" name="password" required></label>
        `,
        rol: `
            <label>Nombre: <input type="text" name="nombre" required></label>
            <label>Descripción: <textarea name="descripcion" required></textarea></label>
        `
    };
    document.getElementById('campos-formulario').innerHTML = campos[tipo];
    if (tipo === 'usuario') cargarRolesParaSelect();
    abrirModal();
}

async function cargarRolesParaSelect() {
    const res = await fetch('../api/admin.php?action=listar_roles');
    const data = await res.json();
    const select = document.querySelector('[name="id_rol"]');
    select.innerHTML = data.map(item => `<option value="${item.id_rol}">${item.nombre}</option>`).join('');
}

function cerrarModal() {
    window.cerrarModal();
}

document.getElementById('form-entidad').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'guardar_' + formData.get('tipo'));
    const res = await fetch('../api/admin.php', { method: 'POST', body: formData });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') {
        cerrarModal();
        if (formData.get('tipo') === 'usuario') cargarUsuarios();
        else if (formData.get('tipo') === 'rol') cargarRoles();
    }
});

async function editar(tipo, id) {
    const res = await fetch(`../api/admin.php?action=obtener_${tipo}&id=${id}`);
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
    const res = await fetch('../api/admin.php', { method: 'POST', body: new URLSearchParams({ action: 'eliminar_' + tipo, id }) });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') {
        if (tipo === 'usuario') cargarUsuarios();
        else if (tipo === 'rol') cargarRoles();
    }
}

window.onload = () => mostrarTab('usuarios');
</script>

<?php require_once '../includes/footer.php'; ?></content>
<parameter name="filePath">C:\xampp\htdocs\sippi\modules\admin.php