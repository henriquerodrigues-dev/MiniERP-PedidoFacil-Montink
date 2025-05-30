<?php $this->load->view('templates/header'); ?>

<div class="container mt-4">
    <h2 class="mb-3"><i class="bi bi-ticket-perforated"></i> Cupons Cadastrados</h2>

    <a href="<?= site_url('coupons/create') ?>" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Criar Novo Cupom
    </a>

    <?php if (empty($coupons)): ?>
        <div class="alert alert-info">Nenhum cupom cadastrado.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Desconto (R$)</th>
                        <th>Valor Mínimo (R$)</th>
                        <th>Validade</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td><?= htmlspecialchars($coupon->code) ?></td>
                            <td><?= number_format($coupon->discount, 2, ',', '.') ?></td>
                            <td><?= number_format($coupon->min_value, 2, ',', '.') ?></td>
                            <td><?= date('d/m/Y', strtotime($coupon->valid_until)) ?></td>
                            <td class="text-center">
                                <a href="<?= site_url('coupons/edit/' . $coupon->id) ?>" 
                                   class="btn btn-sm btn-warning me-1" 
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?= site_url('coupons/delete/' . $coupon->id) ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Excluir"
                                   onclick="return confirm('Tem certeza que deseja excluir este cupom?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $this->load->view('templates/footer'); ?>
