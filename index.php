<?php 
require_once 'config/config.php'; 
if (isset($_SESSION['id_usuario'])) {
    header("Location: modules/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIPPI-IDEICE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-left">
            <div class="login-brand">
                <div class="brand-logo">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1>SIPPI - IDEICE</h1>
                <p class="brand-subtitle">Sistema Integrado de Planificación, Presupuesto y Seguimiento</p>
                <p class="brand-description">
                    Plataforma institucional para la gestión integral del Plan Estratégico Institucional (PEI), 
                    Plan Operativo Anual (POA), Presupuesto y PACC del Instituto Dominicano de Evaluación 
                    e Investigación de la Calidad Educativa.
                </p>
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Gestión PEI 2025-2028</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Seguimiento POA por áreas</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Integración presupuestaria</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Control de compras PACC</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-card">
                <div class="login-header">
                    <h2>Iniciar Sesión</h2>
                    <p>Ingrese sus credenciales para acceder al sistema</p>
                </div>
                
                <form id="loginForm" class="login-form">
                    <div class="form-group">
                        <label for="usuario">
                            <i class="fas fa-user"></i>
                            <span>Usuario</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-user-circle"></i>
                            <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            <span>Contraseña</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox">
                            <input type="checkbox" name="remember">
                            <span>Recordar sesión</span>
                        </label>
                        <a href="#" class="forgot-password">¿Olvidó su contraseña?</a>
                    </div>
                    
                    <button type="submit" class="btn login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Iniciar Sesión</span>
                    </button>
                    
                    <div class="login-footer">
                        <p>¿Necesita ayuda? <a href="#">Contacte al administrador</a></p>
                        <div class="system-info">
                            <i class="fas fa-info-circle"></i>
                            <span>Versión <?= APP_VERSION ?> - IDEICE</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const STATUS_MESSAGE = {
        SUCCESS: 'success'
    };
    
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'login');
        
        const submitBtn = e.target.querySelector('.login-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Verificando...</span>';
        submitBtn.disabled = true;
        
        try {
            const res = await fetch('api/auth.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === STATUS_MESSAGE.SUCCESS) {
                submitBtn.innerHTML = '<i class="fas fa-check"></i><span>¡Acceso concedido!</span>';
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 500);
            } else {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showNotification(data.message, 'error');
            }
        } catch (error) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            showNotification('Error de conexión. Verifique su red.', 'error');
        }
    });
    
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    
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
    </script>
    
    <style>
    .login-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .login-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        max-width: 1200px;
        width: 100%;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow-hover);
        overflow: hidden;
        min-height: 700px;
    }
    
    .login-left {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        color: white;
        padding: 60px 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .login-brand {
        max-width: 500px;
    }
    
    .brand-logo {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 30px;
        font-size: 2.5rem;
    }
    
    .login-brand h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 15px;
        line-height: 1.2;
    }
    
    .brand-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 30px;
        font-weight: 500;
    }
    
    .brand-description {
        opacity: 0.8;
        line-height: 1.6;
        margin-bottom: 40px;
        font-size: 1.05rem;
    }
    
    .features {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 40px;
    }
    
    .feature {
        display: flex;
        align-items: center;
        gap: 12px;
        opacity: 0.9;
    }
    
    .feature i {
        color: var(--accent);
        font-size: 1.2rem;
    }
    
    .login-right {
        padding: 60px 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .login-card {
        max-width: 420px;
        width: 100%;
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .login-header h2 {
        font-size: 2rem;
        color: var(--primary-dark);
        margin-bottom: 10px;
        font-weight: 700;
    }
    
    .login-header p {
        color: var(--gray-600);
        font-size: 1.05rem;
    }
    
    .login-form .form-group {
        margin-bottom: 30px;
    }
    
    .login-form label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        color: var(--gray-700);
        font-weight: 600;
    }
    
    .login-form label i {
        color: var(--primary);
        font-size: 1.1rem;
    }
    
    .input-with-icon {
        position: relative;
    }
    
    .input-with-icon i {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-500);
        font-size: 1.2rem;
    }
    
    .input-with-icon input {
        padding-left: 55px;
        padding-right: 55px;
        height: 56px;
        border: 2px solid var(--gray-300);
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .input-with-icon input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(0, 86, 179, 0.1);
    }
    
    .toggle-password {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        padding: 5px;
        font-size: 1.2rem;
        transition: var(--transition);
    }
    
    .toggle-password:hover {
        color: var(--primary);
    }
    
    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        color: var(--gray-700);
        font-weight: 500;
    }
    
    .checkbox input {
        width: auto;
        margin: 0;
    }
    
    .forgot-password {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }
    
    .forgot-password:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }
    
    .login-btn {
        width: 100%;
        height: 56px;
        font-size: 1.1rem;
        margin-bottom: 30px;
    }
    
    .login-footer {
        text-align: center;
        padding-top: 30px;
        border-top: 1px solid var(--gray-200);
        color: var(--gray-600);
    }
    
    .login-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }
    
    .login-footer a:hover {
        text-decoration: underline;
    }
    
    .system-info {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 20px;
        padding: 10px 20px;
        background: var(--gray-100);
        border-radius: 20px;
        font-size: 0.9rem;
    }
    
    .system-info i {
        color: var(--primary);
    }
    
    .notification {
        position: fixed;
        top: 30px;
        right: 30px;
        background: white;
        padding: 20px 25px;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow-hover);
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        border-left: 4px solid var(--danger);
        max-width: 400px;
    }
    
    .notification-success {
        border-left-color: var(--success);
    }
    
    .notification-error {
        border-left-color: var(--danger);
    }
    
    .notification i {
        font-size: 1.4rem;
    }
    
    .notification-error i {
        color: var(--danger);
    }
    
    .notification-success i {
        color: var(--success);
    }
    
    .notification button {
        background: none;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        padding: 5px;
        margin-left: auto;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @media (max-width: 992px) {
        .login-container {
            grid-template-columns: 1fr;
            max-width: 500px;
        }
        
        .login-left {
            display: none;
        }
    }
    
    @media (max-width: 576px) {
        .login-right {
            padding: 40px 20px;
        }
        
        .login-brand h1 {
            font-size: 2rem;
        }
        
        .features {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html>