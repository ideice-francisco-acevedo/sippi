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
    <div class="dashboard-header">
        <h1>Panel Principal</h1>
        <p class="dashboard-subtitle">Seguimiento integral del PEI 2025-2028, POA, Presupuesto y PACC</p>
        <div class="dashboard-actions">
            <button class="btn" onclick="actualizarDashboard()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
            <button class="btn secondary" onclick="generarReporte()">
                <i class="fas fa-file-export"></i> Generar Reporte
            </button>
            <div class="date-selector">
                <i class="fas fa-calendar-alt"></i>
                <select id="dashboard-year">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2027">2027</option>
                    <option value="2028">2028</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-icon">
                <i class="fas fa-bullseye"></i>
            </div>
            <h3>Avance PEI General</h3>
            <div class="card-value" id="pei-general">0%</div>
            <div class="progress-bar">
                <div class="progress-fill" id="pei-progress" style="width: 0%"></div>
            </div>
            <div class="card-trend trend up">
                <i class="fas fa-arrow-up"></i>
                <span>+2.5% vs anterior</span>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <h3>Actividades POA</h3>
            <div class="card-value" id="poa-actividades">0</div>
            <div class="card-stats">
                <div class="stat">
                    <span class="stat-label">Completadas</span>
                    <span class="stat-value" id="poa-completadas">0</span>
                </div>
                <div class="stat">
                    <span class="stat-label">En proceso</span>
                    <span class="stat-value" id="poa-en-proceso">0</span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <h3>Ejecución Presupuestaria</h3>
            <div class="card-value" id="presupuesto-ejecucion">0%</div>
            <div class="card-stats">
                <div class="stat">
                    <span class="stat-label">Asignado</span>
                    <span class="stat-value" id="presupuesto-asignado">$0</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Ejecutado</span>
                    <span class="stat-value" id="presupuesto-ejecutado">$0</span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3>PACC</h3>
            <div class="card-value" id="pacc-procesos">0</div>
            <div class="card-stats">
                <div class="stat">
                    <span class="stat-label">En tiempo</span>
                    <span class="stat-value" id="pacc-en-tiempo">0</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Con retraso</span>
                    <span class="stat-value" id="pacc-retraso">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y Detalles -->
    <div class="dashboard-row">
        <div class="dashboard-col">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-line"></i> Avance PEI Multianual 2025–2028</h2>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="toggleChart('pei')">
                            <i class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-bars" id="pei-chart">
                        <!-- SVG chart will be inserted by JS -->
                    </div>
                </div>
                <table class="modern-table" id="tabla-pei">
                    <thead>
                        <tr>
                            <th>Año</th>
                            <th>Avance</th>
                            <th>Semáforo</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        
        <div class="dashboard-col">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-bar"></i> Avance POA por Áreas</h2>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="toggleChart('poa')">
                            <i class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-bars horizontal" id="poa-chart">
                        <!-- Horizontal bars will be inserted by JS -->
                    </div>
                </div>
                <table class="modern-table" id="tabla-areas">
                    <thead>
                        <tr>
                            <th>Área</th>
                            <th>Físico</th>
                            <th>Financiero</th>
                            <th>Ponderación</th>
                            <th>Semáforo</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Alertas y Notificaciones -->
    <div class="dashboard-row">
        <div class="dashboard-col">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-bell"></i> Alertas y Notificaciones</h2>
                    <span class="badge" id="alert-count">0</span>
                </div>
                <div class="alerts-container" id="lista-alertas">
                    <!-- Alerts will be loaded here -->
                </div>
            </div>
        </div>
        
        <div class="dashboard-col">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-clock"></i> Próximos Vencimientos</h2>
                </div>
                <div class="timeline" id="timeline">
                    <!-- Timeline items will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    margin-bottom: 40px;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    color: var(--primary-dark);
    margin-bottom: 10px;
    font-weight: 700;
}

.dashboard-subtitle {
    color: var(--gray-600);
    font-size: 1.1rem;
    margin-bottom: 25px;
    max-width: 800px;
}

.dashboard-actions {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.date-selector {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 12px 20px;
    border-radius: 8px;
    border: 2px solid var(--gray-300);
    margin-left: auto;
}

.date-selector select {
    border: none;
    padding: 0;
    width: auto;
    font-weight: 600;
    color: var(--primary-dark);
    background: transparent;
}

.date-selector select:focus {
    box-shadow: none;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.dashboard-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border: 1px solid var(--gray-200);
    position: relative;
    overflow: hidden;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-hover);
}

.dashboard-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(to right, var(--primary), var(--accent));
}

.card-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: white;
    font-size: 1.8rem;
}

.card-icon.warning {
    background: linear-gradient(135deg, var(--warning) 0%, #ffd761 100%);
}

.card-icon.danger {
    background: linear-gradient(135deg, var(--danger) 0%, #e35d6a 100%);
}

.card-icon.success {
    background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
}

.dashboard-card h3 {
    color: var(--gray-600);
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-value {
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary-dark);
    margin: 15px 0;
    line-height: 1;
}

.card-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 25px;
}

.stat {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--gray-600);
    margin-bottom: 5px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.stat-value.success {
    color: var(--success);
}

.stat-value.warning {
    color: var(--warning);
}

.stat-value.danger {
    color: var(--danger);
}

.card-trend {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    margin-top: 15px;
}

.trend.up {
    background: rgba(40, 167, 69, 0.1);
    color: var(--success);
}

.trend.down {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger);
}

.dashboard-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.dashboard-col {
    display: flex;
    flex-direction: column;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
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

.card-header h2::after {
    display: none;
}

.card-actions {
    display: flex;
    gap: 10px;
}

.btn-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: var(--gray-100);
    border: none;
    color: var(--gray-700);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.btn-icon:hover {
    background: var(--gray-200);
    color: var(--primary);
}

.badge {
    background: var(--danger);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    min-width: 30px;
    text-align: center;
}

.chart-container {
    height: 200px;
    margin: 20px 0;
    padding: 20px;
    background: var(--gray-100);
    border-radius: var(--border-radius);
}

.chart-bars {
    display: flex;
    align-items: flex-end;
    gap: 20px;
    height: 100%;
    padding: 20px 0;
}

.chart-bar {
    flex: 1;
    background: linear-gradient(to top, var(--primary), var(--primary-light));
    border-radius: 6px 6px 0 0;
    min-height: 10px;
    position: relative;
    transition: var(--transition);
}

.chart-bar:hover {
    transform: scale(1.05);
}

.chart-bar-label {
    position: absolute;
    bottom: -30px;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 0.9rem;
    color: var(--gray-700);
    font-weight: 500;
}

.chart-bar-value {
    position: absolute;
    top: -30px;
    left: 0;
    right: 0;
    text-align: center;
    font-weight: 600;
    color: var(--primary-dark);
}

.chart-bars.horizontal {
    flex-direction: column;
    gap: 15px;
}

.chart-bar.horizontal {
    width: 100%;
    height: 30px;
    border-radius: 0 6px 6px 0;
    display: flex;
    align-items: center;
    padding-right: 15px;
}

.chart-bar.horizontal .chart-bar-label {
    position: static;
    width: 150px;
    text-align: left;
    padding-left: 15px;
    color: var(--gray-800);
}

.chart-bar.horizontal .chart-bar-value {
    position: static;
    margin-left: auto;
    color: white;
}

.modern-table {
    margin-top: 25px;
}

.modern-table th {
    background: var(--gray-100);
    color: var(--gray-700);
    border-bottom: 2px solid var(--gray-300);
}

.modern-table th:first-child {
    border-radius: var(--border-radius) 0 0 0;
}

.modern-table th:last-child {
    border-radius: 0 var(--border-radius) 0 0;
}

.modern-table tbody tr:hover {
    background: rgba(0, 86, 179, 0.03);
}

.alerts-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.alert-item {
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid var(--warning);
    background: var(--gray-100);
    transition: var(--transition);
}

.alert-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.alert-item.high {
    border-left-color: var(--danger);
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(255, 255, 255, 1) 100%);
}

.alert-item.medium {
    border-left-color: var(--warning);
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.05) 0%, rgba(255, 255, 255, 1) 100%);
}

.alert-item.low {
    border-left-color: var(--success);
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(255, 255, 255, 1) 100%);
}

.alert-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.alert-title {
    font-weight: 600;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-date {
    font-size: 0.85rem;
    color: var(--gray-600);
}

.alert-detail {
    color: var(--gray-700);
    font-size: 0.95rem;
    line-height: 1.5;
}

.timeline {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.timeline-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    border-radius: 10px;
    background: var(--gray-100);
    transition: var(--transition);
    position: relative;
}

.timeline-item:hover {
    transform: translateX(5px);
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.timeline-date {
    min-width: 80px;
    text-align: center;
    padding: 8px;
    background: white;
    border-radius: 8px;
    border: 1px solid var(--gray-300);
}

.timeline-date .day {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    line-height: 1;
}

.timeline-date .month {
    font-size: 0.9rem;
    color: var(--gray-600);
    text-transform: uppercase;
    margin-top: 5px;
}

.timeline-content {
    flex: 1;
}

.timeline-content h4 {
    margin: 0 0 8px 0;
    color: var(--gray-800);
    font-size: 1.1rem;
}

.timeline-content p {
    margin: 0;
    color: var(--gray-600);
    font-size: 0.95rem;
}

.timeline-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    margin-top: 10px;
}

@media (max-width: 1200px) {
    .dashboard-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .date-selector {
        margin-left: 0;
        justify-content: space-between;
    }
    
    .card-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="../assets/js/dashboard.js"></script>

<?php require_once '../includes/footer.php'; ?>