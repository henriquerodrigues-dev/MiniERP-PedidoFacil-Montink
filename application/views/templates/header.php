<?php
/**
 * Cabeçalho HTML padrão com tema personalizado
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>MiniERP - PedidoFácil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
        /* Reset básico e layout flexível para corpo */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Cores principais do tema */
        :root {
            --color1: #2E8B57; /* Verde vibrante */
            --color2: #FFD700; /* Amarelo dourado */
            --color3: #4682B4; /* Azul médio */
            --color4: #F5F5DC; /* Bege claro */
            --color5: #8B4513; /* Marrom discreto */
        }

        body {
            background-color: var(--color4);
            color: var(--color5);
        }

        /* Classes utilitárias para cores */
        .color1 { color: var(--color1); }
        .color2 { color: var(--color2); }
        .color3 { color: var(--color3); }
        .color4 { color: var(--color4); }
        .color5 { color: var(--color5); }

        /* Navbar customizada */
        .navbar-custom {
            background-color: var(--color1);
        }
        .navbar-brand,
        .nav-link {
            color: #fff !important;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: var(--color2) !important;
            text-decoration: underline;
        }

        /* Botões personalizados */
        .btn-primary {
            background-color: var(--color3);
            border: none;
        }
        .btn-primary:hover {
            background-color: #3b6f9e;
        }

        .btn-danger {
            background-color: var(--color5);
            border: none;
        }
        .btn-danger:hover {
            background-color: #5c2e13;
        }

        /* Títulos */
        h2, h3 {
            color: var(--color1);
        }

        /* Container principal para flex-grow */
        .container {
            flex: 1;
        }

        /* Contador do carrinho */
        #cart-count {
            margin-top: 5%;
            font-size: 0.75rem;
            padding: 4px 6px;
            z-index: 1;
        }

        /* Estilo para alertas de erro */
        .alert-danger {
            background-color: #ff4d4f !important;
            color: white !important;
            font-weight: 500;
            text-align: center;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container-fluid d-flex align-items-center justify-content-between">

        <!-- Grupo à esquerda: título e menu -->
        <div class="d-flex align-items-center gap-4">
            <h1 class="navbar-brand fw-bold mb-0">PedidoFácil - Montink</h1>

            <ul class="navbar-nav d-flex flex-row gap-3 mb-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('products') ?>">
                        <i class="bi bi-basket"></i> Fazer Compras
                    </a>
                </li>
                <?php
                    $cart_total = 0;
                    if (!empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            $cart_total += $item['quantity'];
                        }
                    }
                ?>
                <li class="nav-item position-relative">
                    <a class="nav-link" href="<?= site_url('products/cart') ?>">
                        <i class="bi bi-cart"></i> Carrinho
                        <span id="cart-count"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="<?= $cart_total > 0 ? '' : 'display:none;' ?>">
                            +<?= $cart_total ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Grupo à direita: botão modo editar e cadastrar produto -->
        <div class="d-flex align-items-center gap-3">
            <?php
                $currentPath = uri_string();
                $showEditModeButton = ($currentPath === 'products' || $currentPath === 'products/index');
            ?>
            <?php if ($showEditModeButton): ?>
                <a href="#" id="toggleEditMode" class="btn btn-outline-light">
                    <i class="bi bi-pencil-square"></i> Entrar no modo editar
                </a>
            <?php endif; ?>

            <a class="nav-link text-white" href="<?= site_url('coupons') ?>">
                <i class="bi bi-ticket-perforated"></i> Cupons
            </a>

            <a class="nav-link btn btn-link text-white" href="<?= site_url('products/create') ?>">
                <i class="bi bi-plus-square"></i> Cadastrar Produto
            </a>
        </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('toggleEditMode');
    if (!btn) return; // Evita erros caso botão não exista

    const url = new URL(window.location);
    let editMode = url.searchParams.get('edit') === '1';

    function updateButton() {
        if (editMode) {
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Sair do modo editar';
            btn.classList.remove('btn-outline-light');
            btn.classList.add('btn-danger');
        } else {
            btn.innerHTML = '<i class="bi bi-pencil-square"></i> Entrar no modo editar';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-outline-light');
        }
    }

    updateButton();

    btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (editMode) {
            url.searchParams.delete('edit');
        } else {
            url.searchParams.set('edit', '1');
        }
        window.location.href = url.toString();
    });
});
</script>
