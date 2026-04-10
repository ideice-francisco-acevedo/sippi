// Cargar Dashboard completo
document.addEventListener('DOMContentLoaded', () => {
    actualizarDashboard();
    cargarProximosVencimientos();
});

async function actualizarDashboard() {
    try {
        await Promise.all([
            cargarResumenGeneral(),
            cargarPEIMultianual(),
            cargarPOAAreas(),
            cargarAlertas()
        ]);
        console.log('Dashboard actualizado');
    } catch (error) {
        console.error('Error actualizando dashboard:', error);
        showNotification('Error al cargar datos del dashboard', 'error');
    }
}

async function cargarResumenGeneral() {
    try {
        const [peiRes, poaRes, presupuestoRes, paccRes] = await Promise.all([
            fetch('../api/dashboard.php?action=pei').then(r => r.json()),
            fetch('../api/dashboard.php?action=poa-areas').then(r => r.json()),
            fetch('../api/dashboard.php?action=presupuesto').then(r => r.json()).catch(() => ({})),
            fetch('../api/dashboard.php?action=pacc').then(r => r.json()).catch(() => ({}))
        ]);
        
        // Calcular avance PEI general (promedio)
        const avancePEI = peiRes.length > 0 
            ? peiRes.reduce((sum, item) => sum + item.avance, 0) / peiRes.length 
            : 0;
        document.getElementById('pei-general').textContent = `${avancePEI.toFixed(1)}%`;
        document.getElementById('pei-progress').style.width = `${avancePEI}%`;
        
        // Calcular estadísticas POA
        const totalActividades = poaRes.length * 5; // Estimado
        const completadas = Math.round(totalActividades * 0.65);
        const enProceso = Math.round(totalActividades * 0.25);
        document.getElementById('poa-actividades').textContent = totalActividades;
        document.getElementById('poa-completadas').textContent = completadas;
        document.getElementById('poa-en-proceso').textContent = enProceso;
        
        // Datos de presupuesto (simulados si no hay API)
        const ejecucionPresupuesto = 68.5;
        const asignado = 12500000;
        const ejecutado = Math.round(asignado * (ejecucionPresupuesto / 100));
        document.getElementById('presupuesto-ejecucion').textContent = `${ejecucionPresupuesto.toFixed(1)}%`;
        document.getElementById('presupuesto-asignado').textContent = `$${(asignado / 1000000).toFixed(1)}M`;
        document.getElementById('presupuesto-ejecutado').textContent = `$${(ejecutado / 1000000).toFixed(1)}M`;
        
        // Datos PACC (simulados si no hay API)
        const totalProcesos = 42;
        const enTiempo = 28;
        const conRetraso = 14;
        document.getElementById('pacc-procesos').textContent = totalProcesos;
        document.getElementById('pacc-en-tiempo').textContent = enTiempo;
        document.getElementById('pacc-retraso').textContent = conRetraso;
        
    } catch (error) {
        console.error('Error cargando resumen general:', error);
    }
}

async function cargarPEIMultianual() {
    try {
        const res = await fetch('../api/dashboard.php?action=pei');
        const data = await res.json();
        
        // Actualizar tabla
        let tbodyHtml = '';
        data.forEach(item => {
            tbodyHtml += `
                <tr>
                    <td><strong>${item.anio}</strong></td>
                    <td>${item.avance}%</td>
                    <td><span class="semaforo-${item.semaforo}">${item.semaforo.toUpperCase()}</span></td>
                    <td>${item.obs}</td>
                </tr>`;
        });
        document.querySelector('#tabla-pei tbody').innerHTML = tbodyHtml;
        
        // Crear gráfico de barras
        crearGraficoPEI(data);
        
    } catch (error) {
        console.error('Error cargando PEI multianual:', error);
    }
}

function crearGraficoPEI(data) {
    const container = document.getElementById('pei-chart');
    if (!container) return;
    
    const maxAvance = Math.max(...data.map(d => d.avance), 100);
    const barWidth = 60;
    const gap = 20;
    const totalWidth = data.length * (barWidth + gap);
    const maxHeight = 150;
    
    let svg = `<svg width="100%" height="${maxHeight}" viewBox="0 0 ${totalWidth} ${maxHeight}">`;
    
    data.forEach((item, index) => {
        const x = index * (barWidth + gap);
        const height = (item.avance / maxAvance) * maxHeight;
        const y = maxHeight - height;
        const color = getSemaforoColor(item.semaforo);
        
        svg += `
            <g>
                <rect x="${x}" y="${y}" width="${barWidth}" height="${height}" 
                      fill="url(#gradient-${index})" rx="4" ry="4" />
                <text x="${x + barWidth/2}" y="${y - 10}" text-anchor="middle" 
                      fill="${color}" font-weight="600">${item.avance}%</text>
                <text x="${x + barWidth/2}" y="${maxHeight + 20}" text-anchor="middle" 
                      fill="#666" font-size="12">${item.anio}</text>
            </g>
            <defs>
                <linearGradient id="gradient-${index}" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="${color}" stop-opacity="0.8"/>
                    <stop offset="100%" stop-color="${color}" stop-opacity="0.4"/>
                </linearGradient>
            </defs>
        `;
    });
    
    svg += '</svg>';
    container.innerHTML = svg;
}

async function cargarPOAAreas() {
    try {
        const res = await fetch('../api/dashboard.php?action=poa-areas');
        const data = await res.json();
        
        // Actualizar tabla
        let tbodyHtml = '';
        data.forEach(item => {
            tbodyHtml += `
                <tr onclick="irAPOA(${item.area_id})" style="cursor:pointer">
                    <td>${item.area}</td>
                    <td>${item.fisico}%</td>
                    <td>${item.financiero}%</td>
                    <td><strong>${item.ponderacion}%</strong></td>
                    <td><span class="semaforo-${item.semaforo}">${item.semaforo.toUpperCase()}</span></td>
                </tr>`;
        });
        document.querySelector('#tabla-areas tbody').innerHTML = tbodyHtml;
        
        // Crear gráfico de barras horizontales
        crearGraficoPOA(data);
        
    } catch (error) {
        console.error('Error cargando POA por áreas:', error);
    }
}

function crearGraficoPOA(data) {
    const container = document.getElementById('poa-chart');
    if (!container) return;
    
    // Tomar solo los primeros 6 para mejor visualización
    const displayData = data.slice(0, 6);
    const maxPonderacion = Math.max(...displayData.map(d => d.ponderacion), 100);
    
    let html = '';
    displayData.forEach(item => {
        const width = (item.ponderacion / maxPonderacion) * 100;
        const color = getSemaforoColor(item.semaforo);
        
        html += `
            <div class="chart-bar horizontal" style="background: linear-gradient(to right, ${color}, ${color}88)">
                <span class="chart-bar-label">${item.area}</span>
                <span class="chart-bar-value">${item.ponderacion}%</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

async function cargarAlertas() {
    try {
        const res = await fetch('../api/dashboard.php?action=alertas');
        const data = await res.json();
        
        // Actualizar contador
        document.getElementById('alert-count').textContent = data.length;
        
        // Crear alertas con nuevo diseño
        let html = '';
        data.forEach(a => {
            const priorityClass = a.prioridad === 'alta' ? 'high' : a.prioridad === 'media' ? 'medium' : 'low';
            const priorityIcon = a.prioridad === 'alta' ? 'fa-exclamation-triangle' : 
                               a.prioridad === 'media' ? 'fa-exclamation-circle' : 'fa-info-circle';
            
            html += `
                <div class="alert-item ${priorityClass}">
                    <div class="alert-header">
                        <div class="alert-title">
                            <i class="fas ${priorityIcon}"></i>
                            <span>${a.titulo}</span>
                        </div>
                        <div class="alert-date">${a.fecha}</div>
                    </div>
                    <div class="alert-detail">${a.detalle}</div>
                    <div class="alert-meta">
                        <span class="badge">${a.codigo}</span>
                        <span>${a.origen}</span>
                    </div>
                </div>
            `;
        });
        
        document.getElementById('lista-alertas').innerHTML = html;
        
    } catch (error) {
        console.error('Error cargando alertas:', error);
    }
}

async function cargarProximosVencimientos() {
    // Datos simulados - en producción esto vendría de una API
    const vencimientos = [
        { id: 1, title: 'Cierre trimestral POA', date: '2026-04-30', description: 'Reporte de avance Q1 2026', status: 'pending' },
        { id: 2, title: 'Aprobación presupuesto Q3', date: '2026-05-15', description: 'Revisión y aprobación por dirección', status: 'in-progress' },
        { id: 3, title: 'Entrega evidencias PACC', date: '2026-04-25', description: 'Documentación de procesos de compra', status: 'pending' },
        { id: 4, title: 'Actualización PEI', date: '2026-06-01', description: 'Revisión de indicadores estratégicos', status: 'upcoming' },
        { id: 5, title: 'Capacitación usuarios', date: '2026-05-05', description: 'Sesión de formación nuevos usuarios', status: 'confirmed' }
    ];
    
    const timeline = document.getElementById('timeline');
    if (!timeline) return;
    
    let html = '';
    vencimientos.forEach(item => {
        const date = new Date(item.date);
        const day = date.getDate();
        const month = date.toLocaleString('es-ES', { month: 'short' });
        const statusClass = getStatusClass(item.status);
        const statusText = getStatusText(item.status);
        
        html += `
            <div class="timeline-item">
                <div class="timeline-date">
                    <div class="day">${day}</div>
                    <div class="month">${month}</div>
                </div>
                <div class="timeline-content">
                    <h4>${item.title}</h4>
                    <p>${item.description}</p>
                    <span class="timeline-status ${statusClass}">
                        <i class="fas ${getStatusIcon(item.status)}"></i>
                        ${statusText}
                    </span>
                </div>
            </div>
        `;
    });
    
    timeline.innerHTML = html;
}

function getSemaforoColor(semaforo) {
    const colors = {
        'rojo': '#dc3545',
        'amarillo': '#ffc107',
        'naranja': '#fd7e14',
        'verde': '#28a745'
    };
    return colors[semaforo] || '#666';
}

function getStatusClass(status) {
    const classes = {
        'pending': 'status-warning',
        'in-progress': 'status-success',
        'upcoming': 'status-indicator',
        'confirmed': 'status-success'
    };
    return classes[status] || '';
}

function getStatusText(status) {
    const texts = {
        'pending': 'Pendiente',
        'in-progress': 'En progreso',
        'upcoming': 'Próximo',
        'confirmed': 'Confirmado'
    };
    return texts[status] || status;
}

function getStatusIcon(status) {
    const icons = {
        'pending': 'fa-clock',
        'in-progress': 'fa-spinner',
        'upcoming': 'fa-calendar-plus',
        'confirmed': 'fa-check-circle'
    };
    return icons[status] || 'fa-circle';
}

function irAPOA(areaId) {
    // Implementar redirección real
    window.location.href = `poa.php?area=${areaId}`;
}

function toggleChart(chartType) {
    const chart = document.getElementById(`${chartType}-chart`);
    if (chart) {
        chart.classList.toggle('fullscreen');
    }
}

function generarReporte() {
    showNotification('Generando reporte... Esta funcionalidad está en desarrollo.', 'info');
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