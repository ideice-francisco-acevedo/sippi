<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> v<?= APP_VERSION ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="brand">
            <i class="fas fa-chart-line"></i>
            <span><strong><?= APP_NAME ?></strong> - IDEICE</span>
        </div>
    </div>
    <div class="topbar-right">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span>Bienvenido, <strong><?= $_SESSION['nombre'] ?? 'Usuario' ?></strong></span>
        </div>
        <a href="../api/auth.php?action=logout" class="logout-btn" onclick="return confirm('¿Está seguro que desea cerrar la sesión?')">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.menu-toggle');
    if (window.innerWidth <= 768 && sidebar.classList.contains('active') && 
        !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
        sidebar.classList.remove('active');
    }
});
</script>

<style>
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 25px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.menu-toggle {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 10px;
    border-radius: 6px;
    transition: var(--transition);
}

.menu-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.25rem;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 25px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    transition: var(--transition);
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .menu-toggle {
        display: block;
    }
    
    .topbar-right .user-info span {
        display: none;
    }
    
    .logout-btn span {
        display: none;
    }
    
    .logout-btn {
        padding: 10px;
    }
    
    .brand span {
        font-size: 1rem;
    }
}
</style>