/**
 * E-Commerce PHP - JavaScript Principal
 * 
 * Funções gerais para manipulação do DOM e interações
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializa componentes
    initMobileMenu();
    initAlerts();
    initQuantityControls();
    initFormValidation();
});

/**
 * Menu Mobile Toggle
 */
function initMobileMenu() {
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('.main-nav');
    
    if (toggleBtn && nav) {
        toggleBtn.addEventListener('click', function() {
            nav.classList.toggle('active');
            toggleBtn.textContent = nav.classList.contains('active') ? '✕' : '☰';
        });
        
        // Fechar menu ao clicar em um link
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                nav.classList.remove('active');
                toggleBtn.textContent = '☰';
            });
        });
    }
}

/**
 * Auto-dismiss de alertas
 */
function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Auto-dismiss após 5 segundos
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

/**
 * Controles de quantidade
 */
function initQuantityControls() {
    document.querySelectorAll('.quantity-control').forEach(control => {
        const minusBtn = control.querySelector('[data-action="minus"]');
        const plusBtn = control.querySelector('[data-action="plus"]');
        const input = control.querySelector('input');
        
        if (minusBtn && plusBtn && input) {
            minusBtn.addEventListener('click', function() {
                const currentValue = parseInt(input.value) || 1;
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
            
            plusBtn.addEventListener('click', function() {
                const currentValue = parseInt(input.value) || 1;
                const max = parseInt(input.getAttribute('max')) || 99;
                if (currentValue < max) {
                    input.value = currentValue + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
}

/**
 * Validação de formulários
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showFieldError(field, 'Este campo é obrigatório');
                } else {
                    clearFieldError(field);
                }
            });
            
            // Validação de email
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    showFieldError(field, 'Email inválido');
                }
            });
            
            // Validação de senha (mínimo 6 caracteres)
            const passwordFields = form.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => {
                if (field.value && field.value.length < 6) {
                    isValid = false;
                    showFieldError(field, 'A senha deve ter pelo menos 6 caracteres');
                }
            });
            
            // Validação de confirmação de senha
            const senha = form.querySelector('input[name="senha"]');
            const confirmaSenha = form.querySelector('input[name="confirma_senha"]');
            if (senha && confirmaSenha && senha.value !== confirmaSenha.value) {
                isValid = false;
                showFieldError(confirmaSenha, 'As senhas não conferem');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Exibe erro em campo do formulário
 */
function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

/**
 * Limpa erro do campo
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const existingError = field.parentNode.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Valida formato de email
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Valida formato de CPF
 */
function isValidCPF(cpf) {
    cpf = cpf.replace(/[^\d]/g, '');
    
    if (cpf.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(cpf)) return false;
    
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(9))) return false;
    
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(10))) return false;
    
    return true;
}

/**
 * Máscara para CPF
 */
function maskCPF(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    
    input.value = value;
}

/**
 * Máscara para CEP
 */
function maskCEP(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 8) value = value.slice(0, 8);
    
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    
    input.value = value;
}

/**
 * Máscara para telefone
 */
function maskPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 10) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (value.length > 6) {
        value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
    }
    
    input.value = value;
}

/**
 * Busca CEP via API ViaCEP
 */
async function buscarCEP(cep) {
    cep = cep.replace(/\D/g, '');
    
    if (cep.length !== 8) {
        return { erro: true, message: 'CEP inválido' };
    }
    
    try {
        // Usa o proxy PHP para evitar problemas de CORS
        const response = await fetch(`api/cep.php?cep=${encodeURIComponent(cep)}`);
        const data = await response.json();
        
        if (data.erro) {
            return { erro: true, message: 'CEP não encontrado' };
        }
        
        return data;
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
        return { erro: true, message: 'Erro ao buscar CEP' };
    }
}

/**
 * Preenche campos de endereço com dados do CEP
 */
function preencherEndereco(data) {
    const campos = {
        'endereco': data.logradouro || '',
        'bairro': data.bairro || '',
        'cidade': data.localidade || '',
        'estado': data.uf || ''
    };
    
    for (const [nome, valor] of Object.entries(campos)) {
        const campo = document.querySelector(`[name="${nome}"]`);
        if (campo) {
            campo.value = valor;
        }
    }
}

/**
 * Calcula frete baseado no CEP
 * Regra: R$ 10 + R$ 2 por cada dígito par
 */
function calcularFrete(cep) {
    cep = cep.replace(/\D/g, '');
    let frete = 10.00;
    
    for (let i = 0; i < cep.length; i++) {
        if (parseInt(cep[i]) % 2 === 0) {
            frete += 2.00;
        }
    }
    
    return frete;
}

/**
 * Formata valor para moeda brasileira
 */
function formatMoney(value) {
    return 'R$ ' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/**
 * Exibe notificação toast
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 1001; min-width: 250px; animation: slideIn 0.3s ease;';
    toast.innerHTML = `
        ${message}
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Confirma ação
 */
function confirmAction(message) {
    return confirm(message);
}
