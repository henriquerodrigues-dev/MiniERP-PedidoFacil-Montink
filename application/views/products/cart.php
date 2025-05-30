<?php
// Inclui o cabeçalho da página
$this->load->view('templates/header');
?>

<!-- Título principal da página -->
<h2 class="color1 mb-4">Carrinho de Compras</h2>

<!-- Exibe alerta caso haja erro relacionado ao CEP -->
<?php if (!empty($cep_error)): ?>
    <div class="alert alert-warning text-center fw-bold fs-5">
        <?= htmlspecialchars($cep_error) ?>
    </div>
<?php endif; ?>

<!-- Verifica se o carrinho está vazio -->
<?php if (empty($cart)): ?>
    <div class="alert alert-info">Seu carrinho está vazio.</div>
<?php else: ?>

    <!-- Lista os itens do carrinho em cards -->
    <div class="row g-4">
        <?php foreach ($cart as $key => $item): ?>
            <div class="col-12">
                <div class="card shadow-sm d-flex flex-row align-items-center p-3">

                    <!-- Imagem do produto ou imagem padrão caso não exista -->
                    <div class="me-3" style="width: 60px; height: 60px;">
                        <?php if (!empty($item['image'])): ?>
                            <img 
                                src="<?= base_url('uploads/' . $item['image']) ?>" 
                                alt="<?= htmlspecialchars($item['name']) ?>" 
                                class="img-fluid rounded" 
                                style="object-fit: cover; width: auto; height: 100%;"
                            >
                        <?php else: ?>
                            <img 
                                src="<?= base_url('assets/no-image.png') ?>" 
                                alt="Sem imagem" 
                                class="img-fluid rounded" 
                                style="object-fit: cover; width: 100%; height: 100%;"
                            >
                        <?php endif; ?>
                    </div>

                    <!-- Informações do produto -->
                    <div class="flex-grow-1">
                        <h5 class="mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                        <small class="text-muted">Variação: <?= htmlspecialchars($item['variation']) ?></small>
                        <div class="mt-2 d-flex flex-wrap align-items-center gap-3">
                            <div><strong>Preço:</strong> R$ <?= number_format($item['price'], 2, ',', '.') ?></div>
                            <div><strong>Quantidade:</strong> <?= $item['quantity'] ?></div>
                            <div><strong>Subtotal:</strong> R$ <?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?></div>
                        </div>
                    </div>

                    <!-- Botão para remover o item do carrinho -->
                    <div class="ms-3">
                        <a 
                            href="<?= site_url('products/remove_from_cart/' . urlencode($key)) ?>" 
                            class="btn btn-outline-danger btn-sm" 
                            title="Remover do carrinho" 
                            onclick="return confirm('Remover este item do carrinho?');"
                        >
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Botão para limpar todo o carrinho -->
    <form method="post" action="<?= site_url('products/clear_cart') ?>" class="mt-3 mb-3 d-flex justify-content-end me-3">
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-x-circle"></i> Limpar Carrinho
        </button>
    </form>

    <!-- Resumo do pedido, cupom, frete e finalização -->
    <div class="text-center mt-4 mx-auto" style="max-width: 400px;">

        <!-- Resumo com subtotal, cupom aplicado, frete e total -->
        <div class="mb-4 p-3 border rounded bg-light text-start">
            <p class="mb-2"><strong>Subtotal:</strong> R$ <?= number_format($subtotal, 2, ',', '.') ?></p>

            <!-- Mensagem de cupom aplicado ou inválido -->
            <?php if (!empty($coupon_applied)): ?>
                <?php if (!empty($coupon_applied['valid'])): ?>
                    <div class="alert alert-success p-2 mb-2">
                        Cupom <strong><?= htmlspecialchars($coupon_applied['code']) ?></strong> aplicado!<br>
                        Desconto de <strong>R$ <?= number_format($coupon_applied['discount'], 2, ',', '.') ?></strong>.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning p-2 mb-2">
                        Cupom <strong><?= htmlspecialchars($coupon_applied['code']) ?></strong> inválido para este valor.<br>
                        Mínimo necessário: <strong>R$ <?= number_format($coupon_applied['min_value'], 2, ',', '.') ?></strong>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Exibe o valor do frete, podendo ser grátis -->
            <?php if ($shipping !== null): ?>
                <p class="mb-2">
                    <strong>Frete:</strong>
                    <span class="<?= $shipping_text_class ?>">
                        <?= ($shipping == 0) ? "Grátis" : "R$ " . number_format($shipping, 2, ',', '.') ?>
                    </span>
                </p>
            <?php endif; ?>

            <!-- Valor total após desconto -->
            <p class="fs-5 mb-0">
                <strong>Total:</strong> R$ <?= number_format($total, 2, ',', '.') ?>
                <?php if (!empty($coupon_applied) && $coupon_applied['valid']): ?>
                    - desconto de R$ <?= number_format($coupon_applied['discount'], 2, ',', '.') ?>
                <?php endif; ?>
            </p>
        </div>

        <!-- Formulário para aplicar cupom -->
        <form method="post" action="<?= site_url('products/apply_coupon') ?>" class="mb-4 d-flex gap-2 justify-content-center">
            <input type="text" name="coupon_code" placeholder="Digite seu cupom" class="form-control" required style="max-width: 200px;">
            <button type="submit" class="btn btn-secondary">Aplicar Cupom</button>
        </form>

        <!-- Formulário para cálculo de frete via CEP -->
        <form method="post" action="<?= site_url('products/cart') ?>" class="mb-3 d-flex flex-column align-items-center" style="max-width: 400px; margin: 0 auto;">

            <!-- Campo para entrada do CEP com validação -->
            <div class="input-group mb-3" style="max-width: 300px;">
                <input 
                    type="text" 
                    id="cep" 
                    name="cep" 
                    value="<?= htmlspecialchars($cep ?? '') ?>" 
                    placeholder="00000-000" 
                    class="form-control" 
                    required 
                    pattern="\d{5}-?\d{3}"
                >
                <button type="submit" class="btn btn-primary">Calcular</button>
            </div>

            <!-- Exibe informações do endereço referente ao CEP informado -->
            <?php if (!empty($cep_info)): ?>
                <small class="text-muted d-block fs-9 mt-2 text-center" style="max-width: 300px;">
                    Frete para: <?= htmlspecialchars($cep_info->logradouro ?? '') ?>,
                    <?= htmlspecialchars($cep_info->bairro ?? '') ?>,
                    <?= htmlspecialchars($cep_info->localidade ?? '') ?>
                </small>
            <?php endif; ?>

            <!-- Botão para finalizar pedido somente aparece se CEP válido -->
            <?php if (!empty($cep) && empty($cep_error)): ?>
                <button id="btn-finalizar" name="btn_finalizar" class="btn btn-success mt-3 w-100" style="max-width: 400px;">
                    Finalizar Pedido
                </button>
            <?php else: ?>
                <div class="text-center text-danger fw-bold mt-3 mb-3">
                    É necessário preencher o CEP para finalizar o pedido.
                </div>
            <?php endif; ?>

        </form>
    </div>

<?php endif; ?>

<script>
    // Máscara simples para formatar o CEP no padrão 00000-000 enquanto o usuário digita
    document.getElementById('cep').addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        if (v.length > 5) {
            v = v.slice(0,5) + '-' + v.slice(5,8);
        }
        e.target.value = v;
    });

    // Ao carregar a página, faz scroll suave até o botão "Finalizar Pedido", se existir
    window.addEventListener('DOMContentLoaded', () => {
        const btnFinalizar = document.getElementById('btn-finalizar');
        if (btnFinalizar) {
            btnFinalizar.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Quando o formulário de finalização for submetido via botão, mostra spinner e desabilita botão por 3 segundos
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action="<?= site_url('products/cart') ?>"]');
        const btnFinalizar = document.getElementById('btn-finalizar');

        if (form && btnFinalizar) {
            form.addEventListener('submit', function(e) {
                if (document.activeElement === btnFinalizar) {
                    e.preventDefault();

                    // Desabilita botão e mostra indicador de carregamento
                    btnFinalizar.disabled = true;
                    btnFinalizar.innerHTML = `
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...
                    `;

                    // Após 3 segundos, envia o formulário
                    setTimeout(() => {
                        form.submit();
                    }, 3000);
                }
            });
        }
    });
</script>

<?php
// Inclui o rodapé da página
$this->load->view('templates/footer');
?>
