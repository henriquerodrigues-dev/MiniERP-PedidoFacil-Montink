<?php $this->load->view('templates/header'); ?>

<div class="container mt-4">

    <!-- Botão de Voltar -->
    <div class="mb-3">
        <a href="<?= site_url('products') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar para Produtos
        </a>
    </div>

    <h2 class="mb-4 color1">Cadastrar Novo Produto</h2>

    <?= form_open_multipart('products/create') ?>

    <!-- Nome do Produto -->
    <div class="mb-3 row">
        <label for="name" class="col-sm-2 col-form-label">Nome do Produto</label>
        <div class="col-sm-10">
            <input 
                type="text" 
                name="name" 
                id="name" 
                class="form-control" 
                required
            >
        </div>
    </div>

    <!-- Preço -->
    <div class="mb-3 row">
        <label for="price" class="col-sm-2 col-form-label">Preço</label>
        <div class="col-sm-10">
            <input 
                type="text" 
                name="price" 
                id="price" 
                class="form-control" 
                required 
                pattern="^\d+([,]\d{1,2})?$" 
                title="Use vírgula para decimais, ex: 99,90"
            >
        </div>
    </div>

    <!-- Imagem do Produto -->
    <div class="mb-3 row">
        <label for="image" class="col-sm-2 col-form-label">Imagem do Produto</label>
        <div class="col-sm-10">
            <input 
                type="file" 
                name="image" 
                id="image" 
                class="form-control" 
                accept="image/*" 
                onchange="previewImage(event)"
            >
            <img 
                id="imagePreview" 
                class="image-preview mt-2 d-none" 
                alt="Pré-visualização da imagem"
            >
        </div>
    </div>

    <hr>

    <h5 class="color3">Variações e Estoque</h5>

    <!-- Área para variações iniciais -->
    <div id="variations">
        <div class="row mb-2 g-2 align-items-center">
            <div class="col-sm-7">
                <input 
                    type="text" 
                    name="variation[]" 
                    placeholder="Variação (Ex: Tamanho P)" 
                    class="form-control" 
                    required
                >
            </div>
            <div class="col-sm-3">
                <input 
                    type="number" 
                    name="quantity[]" 
                    placeholder="Quantidade" 
                    min="0" 
                    class="form-control" 
                    required
                >
            </div>
            <div class="col-sm-2">
                <button 
                    type="button" 
                    class="btn btn-outline-danger w-100" 
                    onclick="removeVariation(this)"
                >
                    <i class="bi bi-x-circle"></i> Remover
                </button>
            </div>
        </div>
    </div>

    <!-- Botão para adicionar variação -->
    <button 
        type="button" 
        onclick="addVariation()" 
        class="btn btn-outline-primary btn-sm mb-4"
    >
        <i class="bi bi-plus-circle"></i> Adicionar Variação
    </button>

    <!-- Botão para salvar produto -->
    <div>
        <button 
            type="submit" 
            class="btn btn-success px-5"
        >
            <i class="bi bi-save"></i> Salvar Produto
        </button>
    </div>

    <?= form_close() ?>
</div>

<style>
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        border: 1px solid #ddd;
        object-fit: contain;
    }
</style>

<script>
    // Exibe pré-visualização da imagem selecionada
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('imagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.classList.add('d-none');
        }
    }

    // Adiciona uma nova variação no formulário
    function addVariation() {
        const container = document.getElementById('variations');

        const div = document.createElement('div');
        div.className = "row mb-2 g-2 align-items-center";

        div.innerHTML = `
            <div class="col-sm-7">
                <input 
                    type="text" 
                    name="variation[]" 
                    placeholder="Variação (Ex: Cor Azul)" 
                    class="form-control" 
                    required
                >
            </div>
            <div class="col-sm-3">
                <input 
                    type="number" 
                    name="quantity[]" 
                    placeholder="Quantidade" 
                    min="0" 
                    class="form-control" 
                    required
                >
            </div>
            <div class="col-sm-2">
                <button 
                    type="button" 
                    class="btn btn-outline-danger w-100" 
                    onclick="removeVariation(this)"
                >
                    <i class="bi bi-x-circle"></i> Remover
                </button>
            </div>
        `;

        container.appendChild(div);
    }

    // Remove uma variação do formulário
    function removeVariation(button) {
        const div = button.closest('.row');
        if (div) div.remove();
    }
</script>
