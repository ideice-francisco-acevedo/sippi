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
            <h1><i class="fas fa-chart-line"></i> Plan Estratégico Institucional</h1>
            <p class="module-subtitle">Gestión de Ejes, Resultados y Productos Estratégicos 2025-2028</p>
        </div>
        <div class="module-actions">
            <button class="btn" onclick="mostrarFormulario('eje')">
                <i class="fas fa-plus"></i> Nuevo Eje
            </button>
            <button class="btn success" onclick="generarReportePEI()">
                <i class="fas fa-file-export"></i> Reporte
            </button>
        </div>
    </div>

    <div class="module-grid">
        <!-- Ejes Estratégicos -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-bullseye"></i> Ejes Estratégicos</h2>
                <div class="card-actions">
                    <button class="btn-icon" onclick="mostrarFormulario('eje')" title="Agregar Eje">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="modern-table" id="tabla-ejes">
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
        </div>

        <!-- Resultados -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-flag"></i> Resultados</h2>
                <div class="card-actions">
                    <select id="select-eje-resultados" onchange="cargarResultados()" class="modern-select">
                        <option value="">Seleccionar Eje</option>
                    </select>
                    <button class="btn-icon" onclick="mostrarFormulario('resultado')" title="Agregar Resultado">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="modern-table" id="tabla-resultados">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Eje</th>
                            <th>Nombre</th>
                            <th>Indicador</th>
                            <th>Meta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Productos Estratégicos -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-cube"></i> Productos Estratégicos</h2>
                <div class="card-actions">
                    <select id="select-resultado-productos" onchange="cargarProductos()" class="modern-select">
                        <option value="">Seleccionar Resultado</option>
                    </select>
                    <button class="btn-icon" onclick="mostrarFormulario('producto')" title="Agregar Producto">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="modern-table" id="tabla-productos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Resultado</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Año Inicio</th>
                            <th>Año Fin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Formulario Modal -->
    <div id="modal-formulario">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Nuevo Elemento</h3>
                <span class="modal-close" onclick="cerrarModal()">&times;</span>
            </div>
            <form id="form-entidad" class="modal-form">
                <input type="hidden" id="entidad-tipo" name="tipo">
                <input type="hidden" id="entidad-id" name="id">
                <div id="campos-formulario" class="form-grid"></div>
                <div class="form-actions">
                    <button type="button" class="btn secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.module-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 40px;
    padding-bottom: 25px;
    border-bottom: 1px solid var(--gray-200);
}

.module-header h1 {
    font-size: 2.2rem;
    color: var(--primary-dark);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.module-subtitle {
    color: var(--gray-600);
    font-size: 1.1rem;
    max-width: 600px;
}

.module-actions {
    display: flex;
    gap: 15px;
    align-items: center;
}

.module-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--gray-200);
}

.card-header h2 {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.5rem;
    color: var(--primary-dark);
    margin: 0;
}

.card-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.modern-select {
    padding: 10px 15px;
    border: 2px solid var(--gray-300);
    border-radius: 8px;
    background: white;
    color: var(--gray-800);
    font-size: 0.95rem;
    min-width: 200px;
}

.modern-select:focus {
    outline: none;
    border-color: var(--primary);
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0;
}

.modern-table th {
    background: var(--gray-100);
    color: var(--gray-700);
    padding: 16px;
    font-weight: 600;
    text-align: left;
    border-bottom: 2px solid var(--gray-300);
}

.modern-table td {
    padding: 16px;
    border-bottom: 1px solid var(--gray-200);
    color: var(--gray-800);
}

.modern-table tbody tr:hover {
    background: rgba(0, 86, 179, 0.03);
}

.modern-table button {
    padding: 8px 16px;
    margin: 0 4px;
    font-size: 0.9rem;
}

#modal-formulario {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
    z-index: 2000;
    backdrop-filter: blur(5px);
}

#modal-formulario.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--box-shadow-hover);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 30px;
    border-bottom: 1px solid var(--gray-200);
}

.modal-header h3 {
    margin: 0;
    color: var(--primary-dark);
    font-size: 1.5rem;
}

.modal-close {
    font-size: 28px;
    cursor: pointer;
    color: var(--gray-500);
    transition: var(--transition);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.modal-close:hover {
    color: var(--danger);
    background: var(--gray-100);
}

.modal-form {
    padding: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 30px;
}

.form-grid label {
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-weight: 500;
    color: var(--gray-700);
}

.form-grid input,
.form-grid select,
.form-grid textarea {
    padding: 14px;
    border: 2px solid var(--gray-300);
    border-radius: 8px;
    font-size: 1rem;
    transition: var(--transition);
}

.form-grid input:focus,
.form-grid select:focus,
.form-grid textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(0, 86, 179, 0.1);
}

.form-grid textarea {
    min-height: 120px;
    resize: vertical;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding-top: 25px;
    border-top: 1px solid var(--gray-200);
}

@media (max-width: 992px) {
    .module-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .module-header {
        flex-direction: column;
        gap: 20px;
    }
    
    .module-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .card-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .modern-select {
        min-width: auto;
        flex: 1;
    }
}
</style>

<script>
let entidades = { ejes: [], resultados: [], productos: [] };

async function cargarEjes() {
    const res = await fetch('../api/pei.php?action=listar_ejes');
    const data = await res.json();
    entidades.ejes = data;
    actualizarTabla('ejes', data);
    actualizarSelect('select-eje-resultados', data, 'id_eje', 'nombre');
}

async function cargarResultados() {
    const ejeId = document.getElementById('select-eje-resultados').value;
    if (!ejeId) return;
    const res = await fetch(`../api/pei.php?action=listar_resultados&eje_id=${ejeId}`);
    const data = await res.json();
    entidades.resultados = data;
    actualizarTabla('resultados', data);
    actualizarSelect('select-resultado-productos', data, 'id_resultado', 'nombre');
}

async function cargarProductos() {
    const resultadoId = document.getElementById('select-resultado-productos').value;
    if (!resultadoId) return;
    const res = await fetch(`../api/pei.php?action=listar_productos&resultado_id=${resultadoId}`);
    const data = await res.json();
    entidades.productos = data;
    actualizarTabla('productos', data);
}

function actualizarTabla(tipo, data) {
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    const getRowData = (item) => {
        const base = `<td><strong>#${item.id}</strong></td><td>${item.nombre}</td>`;
        if (tipo === 'resultados') {
            return base + `<td>${item.eje}</td><td>${item.indicador}</td><td><span class="badge">${item.meta}%</span></td>`;
        } else if (tipo === 'productos') {
            return base + `<td>${item.resultado}</td><td>${item.descripcion}</td><td>${item.ano_inicio}</td><td>${item.ano_fin}</td>`;
        } else {
            return base + `<td class="text-muted">${item.descripcion}</td>`;
        }
    };

    tbody.innerHTML = data.map(item => `
        <tr>
            ${getRowData(item)}
            <td>
                <div class="action-buttons">
                    <button class="btn-icon small" onclick="editar('${tipo}', ${item.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon small danger" onclick="eliminar('${tipo}', ${item.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function actualizarSelect(selectId, data, valueKey, textKey) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Seleccionar</option>' + data.map(item => `<option value="${item[valueKey]}">${item[textKey]}</option>`).join('');
}

function mostrarFormulario(tipo) {
    document.getElementById('entidad-tipo').value = tipo;
    document.getElementById('entidad-id').value = '';
    
    const titulos = {
        eje: 'Nuevo Eje Estratégico',
        resultado: 'Nuevo Resultado',
        producto: 'Nuevo Producto Estratégico'
    };
    
    document.getElementById('modal-title').textContent = titulos[tipo] || 'Nuevo Elemento';
    
    const campos = {
        eje: `
            <label>
                <span>Nombre</span>
                <input type="text" name="nombre" placeholder="Ingrese el nombre del eje" required>
            </label>
            <label>
                <span>Descripción</span>
                <textarea name="descripcion" placeholder="Describa el eje estratégico" required></textarea>
            </label>
        `,
        resultado: `
            <label>
                <span>Eje Estratégico</span>
                <select name="id_eje" required>
                    ${document.getElementById('select-eje-resultados').innerHTML}
                </select>
            </label>
            <label>
                <span>Nombre</span>
                <input type="text" name="nombre" placeholder="Nombre del resultado" required>
            </label>
            <label>
                <span>Indicador</span>
                <input type="text" name="indicador" placeholder="Indicador de medición" required>
            </label>
            <label>
                <span>Meta (%)</span>
                <input type="number" name="meta" step="0.01" min="0" max="100" placeholder="100" required>
            </label>
        `,
        producto: `
            <label>
                <span>Resultado</span>
                <select name="id_resultado" required>
                    ${document.getElementById('select-resultado-productos').innerHTML}
                </select>
            </label>
            <label>
                <span>Nombre</span>
                <input type="text" name="nombre" placeholder="Nombre del producto" required>
            </label>
            <label>
                <span>Descripción</span>
                <textarea name="descripcion" placeholder="Describa el producto estratégico" required></textarea>
            </label>
            <label>
                <span>Año Inicio</span>
                <input type="number" name="ano_inicio" min="2025" max="2028" value="2025" required>
            </label>
            <label>
                <span>Año Fin</span>
                <input type="number" name="ano_fin" min="2025" max="2028" value="2028" required>
            </label>
        `
    };
    
    document.getElementById('campos-formulario').innerHTML = campos[tipo];
    document.getElementById('modal-formulario').classList.add('active');
}

function cerrarModal() {
    document.getElementById('modal-formulario').classList.remove('active');
    document.getElementById('form-entidad').reset();
}

document.getElementById('form-entidad').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'guardar_' + formData.get('tipo'));
    const res = await fetch('../api/pei.php', { method: 'POST', body: formData });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') {
        cerrarModal();
        cargarEjes(); // Reload all
    }
});

async function editar(tipo, id) {
    const res = await fetch(`../api/pei.php?action=obtener_${tipo}&id=${id}`);
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
    const res = await fetch('../api/pei.php', { method: 'POST', body: new URLSearchParams({ action: 'eliminar_' + tipo, id }) });
    const data = await res.json();
    alert(data.message);
    if (data.status === 'success') cargarEjes();
}

function generarReportePEI() {
    showNotification('Generando reporte del PEI... Esta funcionalidad está en desarrollo.', 'info');
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

window.onload = cargarEjes;
</script>

<?php require_once '../includes/footer.php'; ?></content>
<parameter name="filePath">C:\xampp\htdocs\sippi\modules\pei.php