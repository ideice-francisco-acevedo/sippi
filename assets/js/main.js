// JavaScript global del sistema
console.log('%cSIPPI-IDEICE v2.0 cargado correctamente', 'color:#003C71; font-weight:bold');

// ============================================================================
// UTILIDADES GLOBALES DEL SISTEMA
// ============================================================================

const SippiApp = (() => {
    const config = {
        apiBase: '../api/',
        notificationDuration: 5000,
        modalAnimationDuration: 300
    };

    // ------------------------------------------------------------------------
    // Sistema de notificaciones
    // ------------------------------------------------------------------------
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), config.notificationDuration);
    }

    // ------------------------------------------------------------------------
    // Helper para peticiones API
    // ------------------------------------------------------------------------
    async function apiRequest(endpoint, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const finalOptions = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(config.apiBase + endpoint, finalOptions);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
        } catch (error) {
            console.error('Error en API request:', error);
            showNotification(`Error de conexión: ${error.message}`, 'error');
            throw error;
        }
    }

    // ------------------------------------------------------------------------
    // Gestión de modales
    // ------------------------------------------------------------------------
    const ModalManager = {
        activeModal: null,
        
        open(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                console.warn(`Modal #${modalId} no encontrado`);
                return;
            }
            
            if (this.activeModal && this.activeModal !== modal) {
                this.close(this.activeModal);
            }
            
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
            
            this.activeModal = modal;
            document.body.style.overflow = 'hidden';
        },
        
        close(modalId) {
            const modal = typeof modalId === 'string' 
                ? document.getElementById(modalId) 
                : modalId;
                
            if (!modal) return;
            
            modal.classList.remove('active');
            
            setTimeout(() => {
                modal.style.display = 'none';
                if (this.activeModal === modal) {
                    this.activeModal = null;
                    document.body.style.overflow = '';
                }
            }, config.modalAnimationDuration);
        },
        
        closeActive() {
            if (this.activeModal) {
                this.close(this.activeModal);
            }
        }
    };

    // ------------------------------------------------------------------------
    // Gestión de formularios
    // ------------------------------------------------------------------------
    const FormHelper = {
        serialize(form) {
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            return data;
        },
        
        validate(form, rules) {
            const errors = [];
            const elements = form.elements;
            
            for (let element of elements) {
                if (element.name && rules[element.name]) {
                    const value = element.value.trim();
                    const rule = rules[element.name];
                    
                    if (rule.required && !value) {
                        errors.push(`El campo ${element.name} es obligatorio`);
                        element.classList.add('error');
                    } else if (rule.pattern && !rule.pattern.test(value)) {
                        errors.push(rule.message || `Formato inválido en ${element.name}`);
                        element.classList.add('error');
                    }
                }
            }
            
            return errors;
        },
        
        clearErrors(form) {
            const elements = form.elements;
            for (let element of elements) {
                element.classList.remove('error');
            }
        },
        
        setLoading(form, isLoading) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                if (isLoading) {
                    submitButton.setAttribute('data-original-text', submitButton.innerHTML);
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                    submitButton.disabled = true;
                } else {
                    const originalText = submitButton.getAttribute('data-original-text');
                    if (originalText) {
                        submitButton.innerHTML = originalText;
                    }
                    submitButton.disabled = false;
                }
            }
        }
    };

    // ------------------------------------------------------------------------
    // Gestión de estados de carga
    // ------------------------------------------------------------------------
    const LoadingManager = {
        show(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.add('loading');
            }
        },
        
        hide(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.remove('loading');
            }
        },
        
        showGlobal() {
            let overlay = document.getElementById('global-loading-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'global-loading-overlay';
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.8);
                    backdrop-filter: blur(5px);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                `;
                overlay.innerHTML = `
                    <div class="loading" style="width: 60px; height: 60px; border-width: 5px;"></div>
                `;
                document.body.appendChild(overlay);
            } else {
                overlay.style.display = 'flex';
            }
        },
        
        hideGlobal() {
            const overlay = document.getElementById('global-loading-overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        }
    };

    // ------------------------------------------------------------------------
    // Utilidades de fechas
    // ------------------------------------------------------------------------
    const DateUtils = {
        format(date, format = 'dd/mm/yyyy') {
            const d = new Date(date);
            const day = d.getDate().toString().padStart(2, '0');
            const month = (d.getMonth() + 1).toString().padStart(2, '0');
            const year = d.getFullYear();
            
            return format.replace('dd', day).replace('mm', month).replace('yyyy', year);
        },
        
        daysBetween(start, end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const diffTime = Math.abs(endDate - startDate);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },
        
        addDays(date, days) {
            const result = new Date(date);
            result.setDate(result.getDate() + days);
            return result;
        }
    };

    // ------------------------------------------------------------------------
    // Utilidades de números y moneda
    // ------------------------------------------------------------------------
    const NumberUtils = {
        formatCurrency(amount, currency = 'USD') {
            return new Intl.NumberFormat('es-DO', {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: 2
            }).format(amount);
        },
        
        formatPercent(value, decimals = 1) {
            return `${value.toFixed(decimals)}%`;
        },
        
        round(value, decimals = 2) {
            const factor = Math.pow(10, decimals);
            return Math.round(value * factor) / factor;
        }
    };

    // ------------------------------------------------------------------------
    // Event listeners globales
    // ------------------------------------------------------------------------
    function initGlobalListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-modal-open]')) {
                const modalId = e.target.getAttribute('data-modal-open');
                ModalManager.open(modalId);
            }
            
            if (e.target.matches('[data-modal-close]')) {
                const modalId = e.target.getAttribute('data-modal-close');
                ModalManager.close(modalId);
            }
            
            if (e.target.matches('[data-action="submit"]')) {
                const form = e.target.closest('form');
                if (form) {
                    form.dispatchEvent(new Event('submit', { bubbles: true }));
                }
            }
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && ModalManager.activeModal) {
                ModalManager.closeActive();
            }
        });
    }

    // ------------------------------------------------------------------------
    // Inicialización
    // ------------------------------------------------------------------------
    function init() {
        initGlobalListeners();
        console.log('Utilidades globales SIPPI cargadas');
    }

    // ------------------------------------------------------------------------
    // API pública
    // ------------------------------------------------------------------------
    return {
        init,
        showNotification,
        apiRequest,
        modal: ModalManager,
        form: FormHelper,
        loading: LoadingManager,
        date: DateUtils,
        number: NumberUtils,
        config
    };
})();

// Inicializar al cargar el DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', SippiApp.init);
} else {
    SippiApp.init();
}

// Funciones globales (backward compatibility)
window.showNotification = SippiApp.showNotification;
window.apiRequest = SippiApp.apiRequest;
window.abrirModal = (modalId = 'modal-formulario') => SippiApp.modal.open(modalId);
window.cerrarModal = (modalId = 'modal-formulario') => SippiApp.modal.close(modalId);
window.Sippi = SippiApp;