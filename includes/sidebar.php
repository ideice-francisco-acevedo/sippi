<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-sliders-h"></i> Navegación</h3>
        <p class="sidebar-subtitle">Sistema Integrado</p>
    </div>
    
    <div class="sidebar-menu">
        <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="pei.php" <?= basename($_SERVER['PHP_SELF']) == 'pei.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-chart-line"></i>
            <span>PEI</span>
            <small>Plan Estratégico</small>
        </a>
        <a href="poa.php" <?= basename($_SERVER['PHP_SELF']) == 'poa.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-tasks"></i>
            <span>POA</span>
            <small>Plan Operativo</small>
        </a>
        <a href="presupuesto.php" <?= basename($_SERVER['PHP_SELF']) == 'presupuesto.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-money-bill-wave"></i>
            <span>Presupuesto</span>
            <small>Gestión Financiera</small>
        </a>
        <a href="pacc.php" <?= basename($_SERVER['PHP_SELF']) == 'pacc.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-shopping-cart"></i>
            <span>PACC</span>
            <small>Compras y Contrataciones</small>
        </a>
        <a href="informes.php" <?= basename($_SERVER['PHP_SELF']) == 'informes.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-file-alt"></i>
            <span>Informes</span>
            <small>Reportes Automáticos</small>
        </a>
        <a href="admin.php" <?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-cog"></i>
            <span>Administración</span>
            <small>Configuración y Seguridad</small>
        </a>
    </div>
    
    <div class="sidebar-footer">
        <div class="system-status">
            <div class="status-indicator status-success">
                <i class="fas fa-circle"></i>
                Sistema Activo
            </div>
            <small>v<?= APP_VERSION ?></small>
        </div>
    </div>
</div>

<style>
.sidebar-header {
    padding: 0 30px 30px;
    border-bottom: 1px solid var(--gray-200);
    margin-bottom: 20px;
}

.sidebar-header h3 {
    color: var(--primary-dark);
    font-size: 1.2rem;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-subtitle {
    color: var(--gray-600);
    font-size: 0.9rem;
    font-weight: 500;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 0 20px;
}

.sidebar-menu a {
    display: grid;
    grid-template-columns: 40px 1fr;
    grid-template-rows: auto auto;
    gap: 5px 15px;
    padding: 18px 20px;
    align-items: center;
    border-left: 4px solid transparent;
    margin: 0;
}

.sidebar-menu a i {
    grid-row: span 2;
    font-size: 1.3rem;
    text-align: center;
    opacity: 0.8;
}

.sidebar-menu a span {
    font-weight: 600;
    font-size: 1rem;
    color: inherit;
}

.sidebar-menu a small {
    grid-column: 2;
    font-size: 0.85rem;
    opacity: 0.7;
    color: inherit;
}

.sidebar-menu a.active small {
    opacity: 0.9;
}

.sidebar-footer {
    margin-top: auto;
    padding: 30px 20px 20px;
    border-top: 1px solid var(--gray-200);
}

.system-status {
    text-align: center;
}

.system-status .status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    padding: 8px 16px;
}

.system-status small {
    color: var(--gray-600);
    font-size: 0.85rem;
}
</style>