<?php
$this->load->view('templates/header');

$editMode = $this->input->get('edit') === '1';
?>

<div class="container mb-5">
    <h1 class="mb-4 color1">Produtos Disponíveis</h1>

    <!-- Mensagem de erro da sessão -->
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($products as $product): ?>
            <?php $stocks = $this->Product_model->get_stock_by_product($product->id); ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-4 d-flex">
                <div 
                    class="card p-3 w-100 h-100 position-relative overflow-hidden product-card-wrapper"
                    <?= $editMode ? "style='cursor:pointer;'" : '' ?>
                    <?= $editMode ? "onclick=\"location.href='" . site_url('products/edit/' . $product->id) . "'\"" : "" ?>
                >

                    <?php if (!empty($product->image_path)): ?>
                        <img 
                            src="<?= base_url('uploads/' . $product->image_path) ?>" 
                            class="card-img-top mb-2 product-image" 
                            alt="<?= htmlspecialchars($product->name) ?>"
                        >
                    <?php endif; ?>

                    <h5 class="card-title"><?= htmlspecialchars($product->name) ?></h5>
                    <p class="card-text">R$ <?= number_format($product->price, 2, ',', '.') ?></p>

                    <!-- Formulário para adicionar ao carrinho (exibe apenas se não estiver no modo editar) -->
                    <?php if (!$editMode): ?>
                        <form 
                            method="post" 
                            action="<?= site_url('products/add_to_cart/' . $product->id) ?>" 
                            onClick="event.stopPropagation()"
                        >
                            <div class="mb-2">
                                <select name="variation" class="form-select" required>
                                    <?php foreach ($stocks as $stock): ?>
                                        <option value="<?= htmlspecialchars($stock->variation) ?>">
                                            <?= htmlspecialchars($stock->variation) ?> (<?= $stock->quantity ?> em estoque)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-2">
                                <input 
                                    type="number" 
                                    name="quantity" 
                                    min="1" 
                                    value="1" 
                                    class="form-control" 
                                    required 
                                />
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Adicionar ao Carrinho</button>
                        </form>
                    <?php endif; ?>

                    <!-- Overlay para edição e exclusão no modo editar -->
                    <?php if ($editMode): ?>
                        <div class="edit-overlay d-flex justify-content-center align-items-center">
                            <i class="bi bi-pencil-square text-white fs-1"></i>
                            <button 
                                class="btn btn-sm btn-danger ms-3 remove-product-btn" 
                                title="Remover produto" 
                                type="button"
                                onclick="event.stopPropagation(); confirmRemoveProduct(<?= $product->id ?>)"
                            >
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal genérico para mensagens -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Mensagem</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body" id="messageModalBody">
        <!-- Mensagem vai aqui -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
    function showMessage(message) {
      const modalBody = document.getElementById('messageModalBody');
      modalBody.textContent = message;

      const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
      messageModal.show();
    }

    // Exemplo: intercepta os forms de adicionar ao carrinho
    document.querySelectorAll('form[action^="<?= site_url('products/add_to_cart') ?>"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // previne envio padrão

            const url = this.action;
            const formData = new FormData(this);

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('cart-count');
                    if (badge) {
                        badge.textContent = '+' + data.cart_total;
                        badge.style.display = data.cart_total > 0 ? 'inline-block' : 'none';
                    }
                    this.querySelector('input[name="quantity"]').value = 1;
                } else {
                    showMessage('Erro: ' + (data.error || 'Não foi possível adicionar o produto.'));
                }
            })
            .catch(() => showMessage('Erro na comunicação com o servidor.'));
        });
    });

    // Confirma remoção do produto
    function confirmRemoveProduct(productId) {
        if (confirm('Tem certeza que deseja remover este produto? Esta ação não pode ser desfeita.')) {
            window.location.href = '<?= site_url('products/delete') ?>/' + productId;
        }
    }
</script>

<style>
    body {
        overflow-x: hidden;
    }

    .product-image {
        width: auto;
        height: 128px;
        object-fit: cover;
        margin: 0 auto;
        display: block;
    }

    .edit-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        opacity: 0.8;
        z-index: 10;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        transition: opacity 0.3s ease;
    }

    .product-card-wrapper:hover .edit-overlay {
        opacity: 0.9;
    }

    .remove-product-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
    }

    .remove-product-btn i {
        font-size: 1.25rem;
        pointer-events: none;
    }
</style>



<?php $this->load->view('templates/footer'); ?>
