/**
 * E-Commerce PHP - JavaScript do Carrinho
 * 
 * Funções específicas para manipulação do carrinho de compras
 */

document.addEventListener('DOMContentLoaded', function() {
    initAddToCart();
    initCartQuantityUpdate();
    initRemoveFromCart();
    initCEPSearch();
});

/**
 * Adicionar ao carrinho
 */
function initAddToCart() {
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const produtoId = this.dataset.produtoId;
            const quantidade = 1;
            
            if (!produtoId) return;
            
            try {
                this.disabled = true;
                this.innerHTML = '<span class="spinner" style="width: 16px; height: 16px;"></span>';
                
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('produto_id', produtoId);
                formData.append('quantidade', quantidade);
                
                const response = await fetch('carrinho.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Produto adicionado ao carrinho!', 'success');
                    updateCartCounter(data.cartCount);
                } else {
                    showToast(data.message || 'Erro ao adicionar produto', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao adicionar produto ao carrinho', 'error');
            } finally {
                this.disabled = false;
                this.innerHTML = '🛒 Adicionar';
            }
        });
    });
}

/**
 * Atualizar quantidade no carrinho
 */
function initCartQuantityUpdate() {
    document.querySelectorAll('.cart-quantity').forEach(input => {
        input.addEventListener('change', async function() {
            const produtoId = this.dataset.produtoId;
            const quantidade = parseInt(this.value);
            
            if (!produtoId || quantidade < 1) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('produto_id', produtoId);
                formData.append('quantidade', quantidade);
                
                const response = await fetch('carrinho.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Atualiza subtotal do item
                    const subtotalEl = document.querySelector(`.cart-item-subtotal[data-produto-id="${produtoId}"]`);
                    if (subtotalEl) {
                        subtotalEl.textContent = data.itemSubtotal;
                    }
                    
                    // Atualiza totais
                    updateCartTotals(data);
                } else {
                    showToast(data.message || 'Erro ao atualizar quantidade', 'error');
                    this.value = this.dataset.originalValue || 1;
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar quantidade', 'error');
            }
        });
        
        // Salva valor original
        input.dataset.originalValue = input.value;
    });
}

/**
 * Remover do carrinho
 */
function initRemoveFromCart() {
    document.querySelectorAll('.btn-remove-cart').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const produtoId = this.dataset.produtoId;
            
            if (!produtoId) return;
            
            if (!confirmAction('Deseja remover este item do carrinho?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'remove');
                formData.append('produto_id', produtoId);
                
                const response = await fetch('carrinho.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove o item da DOM
                    const cartItem = this.closest('.cart-item');
                    if (cartItem) {
                        cartItem.style.opacity = '0';
                        setTimeout(() => {
                            cartItem.remove();
                            
                            // Verifica se o carrinho está vazio
                            if (data.cartCount === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                    
                    // Atualiza totais
                    updateCartTotals(data);
                    showToast('Item removido do carrinho', 'success');
                } else {
                    showToast(data.message || 'Erro ao remover item', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao remover item do carrinho', 'error');
            }
        });
    });
}

/**
 * Busca de CEP na página de checkout
 */
function initCEPSearch() {
    const cepInput = document.querySelector('input[name="cep"]');
    const btnBuscarCEP = document.querySelector('.btn-buscar-cep');
    
    if (cepInput) {
        // Máscara para CEP
        cepInput.addEventListener('input', function() {
            maskCEP(this);
        });
        
        // Buscar CEP automaticamente ao completar 9 caracteres (XXXXX-XXX)
        cepInput.addEventListener('input', async function() {
            if (this.value.length === 9) {
                await buscarEPreencherCEP(this.value);
            }
        });
    }
    
    if (btnBuscarCEP) {
        btnBuscarCEP.addEventListener('click', async function(e) {
            e.preventDefault();
            const cep = cepInput.value;
            await buscarEPreencherCEP(cep);
        });
    }
}

/**
 * Busca e preenche dados do CEP
 */
async function buscarEPreencherCEP(cep) {
    const cepInput = document.querySelector('input[name="cep"]');
    const btnBuscarCEP = document.querySelector('.btn-buscar-cep');
    
    if (btnBuscarCEP) {
        btnBuscarCEP.disabled = true;
        btnBuscarCEP.innerHTML = '<span class="spinner" style="width: 16px; height: 16px;"></span>';
    }
    
    try {
        const data = await buscarCEP(cep);
        
        if (data.erro) {
            showToast(data.message || 'CEP não encontrado', 'error');
            return;
        }
        
        // Preenche os campos
        preencherEndereco(data);
        
        // Calcula e exibe o frete
        const frete = calcularFrete(cep);
        atualizarFrete(frete);
        
        showToast('Endereço encontrado!', 'success');
        
        // Foca no campo número
        const numeroInput = document.querySelector('input[name="numero"]');
        if (numeroInput) {
            numeroInput.focus();
        }
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
        showToast('Erro ao buscar CEP', 'error');
    } finally {
        if (btnBuscarCEP) {
            btnBuscarCEP.disabled = false;
            btnBuscarCEP.innerHTML = 'Buscar';
        }
    }
}

/**
 * Atualiza o contador do carrinho no header
 */
function updateCartCounter(count) {
    const counter = document.querySelector('.cart-count');
    
    if (count > 0) {
        if (counter) {
            counter.textContent = count;
        } else {
            // Cria o contador se não existir
            const cartLink = document.querySelector('.cart-link');
            if (cartLink) {
                const newCounter = document.createElement('span');
                newCounter.className = 'cart-count';
                newCounter.textContent = count;
                cartLink.appendChild(newCounter);
            }
        }
    } else {
        if (counter) {
            counter.remove();
        }
    }
}

/**
 * Atualiza totais do carrinho
 */
function updateCartTotals(data) {
    // Atualiza contador no header
    updateCartCounter(data.cartCount);
    
    // Atualiza valor dos produtos
    const totalProdutosEl = document.querySelector('.total-produtos');
    if (totalProdutosEl && data.totalProdutos) {
        totalProdutosEl.textContent = data.totalProdutos;
    }
    
    // Atualiza valor total
    const totalGeralEl = document.querySelector('.total-geral');
    if (totalGeralEl && data.totalGeral) {
        totalGeralEl.textContent = data.totalGeral;
    }
}

/**
 * Atualiza o frete exibido
 */
function atualizarFrete(valor) {
    const freteEl = document.querySelector('.valor-frete');
    if (freteEl) {
        freteEl.textContent = formatMoney(valor);
    }
    
    // Atualiza campo hidden com o valor do frete
    const freteInput = document.querySelector('input[name="valor_frete"]');
    if (freteInput) {
        freteInput.value = valor.toFixed(2);
    }
    
    // Recalcula total geral
    const totalProdutosEl = document.querySelector('.total-produtos');
    if (totalProdutosEl) {
        const totalProdutos = parseFloat(totalProdutosEl.dataset.valor || 0);
        const totalGeral = totalProdutos + valor;
        
        const totalGeralEl = document.querySelector('.total-geral');
        if (totalGeralEl) {
            totalGeralEl.textContent = formatMoney(totalGeral);
        }
    }
}
