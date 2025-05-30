<?php $this->load->view('templates/header'); ?>

<div class="container mt-4">
    <h2><i class="bi bi-pencil-square"></i> Editar Cupom</h2>

    <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>

    <?= form_open('coupons/edit/' . $coupon->id) ?>

    <div class="mb-3">
        <label for="code" class="form-label">Código</label>
        <input type="text" name="code" id="code" class="form-control" 
               value="<?= set_value('code', $coupon->code) ?>" 
               required maxlength="50">
    </div>

    <div class="mb-3">
        <label for="discount" class="form-label">Desconto (R$)</label>
        <input type="text" name="discount" id="discount" 
               pattern="^\d+(,\d{1,2})?$" 
               class="form-control" 
               value="<?= set_value('discount', number_format($coupon->discount, 2, ',', '')) ?>" 
               required>
        <small class="text-muted">Use vírgula para separar centavos (ex: 10,00)</small>
    </div>

    <div class="mb-3">
        <label for="min_value" class="form-label">Valor Mínimo do Carrinho (R$)</label>
        <input type="text" name="min_value" id="min_value" 
               pattern="^\d+(,\d{1,2})?$" 
               class="form-control" 
               value="<?= set_value('min_value', number_format($coupon->min_value, 2, ',', '')) ?>" 
               required>
        <small class="text-muted">Use vírgula para separar centavos (ex: 50,00)</small>
    </div>

    <div class="mb-3">
        <label for="valid_until" class="form-label">Validade</label>
        <input type="date" name="valid_until" id="valid_until" 
               class="form-control" 
               value="<?= set_value('valid_until', $coupon->valid_until) ?>" 
               required>
    </div>

    <button type="submit" class="btn btn-success">
        <i class="bi bi-check-circle"></i> Salvar
    </button>
    <a href="<?= site_url('coupons') ?>" class="btn btn-secondary">
        <i class="bi bi-x-circle"></i> Cancelar
    </a>

    <?= form_close() ?>
</div>

<?php $this->load->view('templates/footer'); ?>
