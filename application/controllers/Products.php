<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Carrega helpers e modelos necessários para o controller
        $this->load->helper(['url', 'form']);
        $this->load->model('Product_model');
        $this->load->library('session');
    }

    /**
     * Exibe a lista de todos os produtos.
     */
    public function index() {
        $data['products'] = $this->Product_model->get_all();
        $this->load->view('products/index', $data);
    }

    /**
     * Lida com a criação de novos produtos.
     * Exibe o formulário de criação ou processa os dados enviados via POST.
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->handle_product_form();
        } else {
            $this->load->view('products/create');
        }
    }

    /**
     * Lida com a edição de produtos existentes.
     * Exibe o formulário de edição pré-preenchido ou processa os dados enviados via POST.
     *
     * @param int $id O ID do produto a ser editado.
     */
    public function edit($id) {
        $product = $this->Product_model->get_by_id($id);
        if (!$product) {
            show_404(); // Mostra página 404 se o produto não for encontrado
        }

        if ($this->input->method() === 'post') {
            $this->handle_product_update($id);
        } else {
            $data['product'] = $product;
            $data['stocks'] = $this->Product_model->get_stock_by_product($id);
            $this->load->view('products/edit', $data);
        }
    }

    /**
     * Remove um produto do sistema.
     * Redireciona para a página de produtos com uma mensagem de sucesso ou erro.
     *
     * @param int $id O ID do produto a ser removido.
     */
    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            show_error('ID inválido.');
        }

        $success = $this->Product_model->delete_product($id);
        $this->session->set_flashdata(
            $success ? 'success' : 'error',
            $success ? 'Produto removido com sucesso.' : 'Erro ao remover o produto.'
        );

        redirect('products?edit=1');
    }

    /**
     * Aplica um cupom de desconto ao carrinho de compras.
     * Valida o cupom e atualiza os dados na sessão.
     */
    public function apply_coupon() {
        $this->load->model('Coupon_model');

        $coupon_code = $this->input->post('coupon_code', true);
        $cart = $this->session->userdata('cart') ?? [];

        $subtotal = $this->calculate_subtotal(); // Reutiliza o método privado para subtotal

        $coupon = $this->Coupon_model->get_by_code($coupon_code);

        $coupon_applied = [
            'code'      => $coupon_code,
            'valid'     => false,
            'discount'  => 0,
            'min_value' => 0
        ];

        if ($coupon) {
            $today = date('Y-m-d');
            $min_value = $coupon->min_value;

            if ($subtotal >= $min_value && $coupon->valid_until >= $today) {
                $coupon_applied['valid']     = true;
                $coupon_applied['discount']  = $coupon->discount;
                $coupon_applied['min_value'] = $min_value;
            } else {
                $coupon_applied['min_value'] = $min_value; // Para exibir o valor mínimo necessário
            }
        }

        $this->session->set_userdata('applied_coupon', $coupon_applied);
        redirect('products/cart');
    }

    /**
     * Adiciona um item ao carrinho de compras via requisição AJAX.
     *
     * @param int $product_id O ID do produto a ser adicionado.
     */
    public function add_to_cart($product_id) {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $variation = $this->input->post('variation');
        $quantity = (int) $this->input->post('quantity');

        $stock = $this->Product_model->get_stock_by_product_and_variation($product_id, $variation);

        if (!$stock) {
            $this->json_response(false, 'Variação inválida.');
            return;
        }

        if ($quantity > $stock->quantity) {
            $this->json_response(false, "Quantidade solicitada maior que o estoque disponível ({$stock->quantity}).");
            return;
        }

        $this->add_item_to_session_cart($product_id, $variation, $quantity, $stock->quantity);
        $cart_total_quantity = $this->calculate_cart_total_quantity();

        $this->json_response(true, null, ['cart_total' => $cart_total_quantity]);
    }

    /**
     * Exibe a página do carrinho de compras, calcula frete e processa a finalização do pedido.
     */
    public function cart() {
        $data = $this->prepare_cart_data();

        // Processa a finalização do pedido se o botão 'btn_finalizar' foi clicado
        if ($this->input->post('btn_finalizar')) {
            if (empty($data['cep_error'])) {
                $client_email = $this->input->post('email_cliente');

                if (!empty($client_email)) {
                    $cart = $this->session->userdata('cart') ?? [];
                    $subtotal = $this->calculate_subtotal();
                    $shipping = $data['shipping'] ?? 0;
                    $discount = $data['discount'] ?? 0;
                    $total = $subtotal + $shipping - $discount;

                    // Garante que o total não seja negativo
                    if ($total < 0) {
                        $total = 0;
                    }

                    // Remover envio de e-mail
                    // Apenas finaliza o pedido e limpa os dados da sessão
                    $this->session->set_flashdata('email_status', 'success');
                    $this->session->set_flashdata('email_message', 'Pedido finalizado com sucesso!');

                    // Limpa o carrinho e o cupom após a finalização do pedido
                    $this->session->unset_userdata(['cart', 'applied_coupon']);
                }

                redirect('products/cart'); // Redireciona para evitar re-submissão do formulário
            }
        }

        $this->load->view('products/cart', $data);
    }

    /**
     * Limpa o carrinho de compras e todos os dados relacionados na sessão.
     */
    public function clear_cart() {
        $this->session->unset_userdata(['cart', 'cep', 'applied_coupon']);
        redirect('products');
    }

    /**
     * Remove um item específico do carrinho de compras.
     *
     * @param string $key A chave hash do item a ser removido do carrinho.
     */
    public function remove_from_cart($key) {
        $cart = $this->session->userdata('cart') ?? [];
        if (isset($cart[$key])) {
            unset($cart[$key]);
            $this->session->set_userdata('cart', $cart);
        }
        redirect('products/cart');
    }

    // --- MÉTODOS PRIVADOS ---

    /**
     * Lida com o processamento do formulário de criação/edição de produtos.
     * Inclui upload de imagem e salvamento de variações/estoque.
     */
    private function handle_product_form() {
        $price = floatval(str_replace(',', '.', $this->input->post('price')));
        $image_path = $this->upload_image('image');

        if ($image_path === false) {
            redirect('products/create');
            return;
        }

        $product_data = [
            'name'       => $this->input->post('name'),
            'price'      => $price,
            'image_path' => $image_path
        ];

        $product_id = $this->Product_model->insert($product_data);
        $this->save_variations_and_stock($product_id);

        redirect('products');
    }

    /**
     * Lida com a atualização dos dados de um produto existente.
     *
     * @param int $id O ID do produto a ser atualizado.
     */
    private function handle_product_update($id) {
        $name = $this->input->post('name');
        $price = floatval(str_replace(',', '.', $this->input->post('price')));

        $this->Product_model->update($id, [
            'name'  => $name,
            'price' => $price
        ]);

        $this->save_variations_and_stock($id);

        redirect('products');
    }

    /**
     * Realiza o upload de uma imagem para o servidor.
     *
     * @param string $field_name O nome do campo de input do arquivo no formulário.
     * @return string|null|false O nome do arquivo salvo, null se nenhum arquivo foi enviado, ou false em caso de erro.
     */
    private function upload_image($field_name) {
        if (empty($_FILES[$field_name]['name'])) {
            return null;
        }

        $this->load->library('upload');

        $config = [
            'upload_path'   => './uploads/',
            'allowed_types' => 'gif|jpg|png|jpeg|webp',
            'file_name'     => time() . '_' . $_FILES[$field_name]['name'],
            'overwrite'     => false,
        ];

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            $data = $this->upload->data();
            return $data['file_name'];
        } else {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            return false;
        }
    }

    /**
     * Salva ou atualiza as variações e o estoque de um produto.
     * Remove variações que não estão mais presentes no formulário.
     *
     * @param int $product_id O ID do produto.
     */
    private function save_variations_and_stock($product_id) {
        $variations_post = $this->input->post('variation');
        $quantities_post = $this->input->post('quantity');

        // Filtra variações vazias do POST
        $variations_post = array_filter($variations_post);

        $variations_db = $this->Product_model->get_variations_by_product($product_id);
        $variations_db_names = array_map(function($v) {
            return $v->variation;
        }, $variations_db);

        // Remove variações que foram excluídas pelo usuário no formulário
        foreach ($variations_db_names as $variation_db) {
            if (!in_array($variation_db, $variations_post)) {
                $this->Product_model->delete_stock($product_id, $variation_db);
            }
        }

        // Insere ou atualiza as variações e suas quantidades
        if ($variations_post && $quantities_post) {
            foreach ($variations_post as $index => $variation) {
                $quantity = isset($quantities_post[$index]) ? intval($quantities_post[$index]) : 0;
                if (!empty($variation)) {
                    $this->Product_model->update_stock($product_id, $variation, $quantity);
                }
            }
        }
    }

    /**
     * Calcula o subtotal do carrinho de compras.
     *
     * @return float O subtotal do carrinho.
     */
    private function calculate_subtotal() {
        $cart = $this->session->userdata('cart') ?? [];
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    /**
     * Adiciona ou atualiza um item no carrinho de compras armazenado na sessão.
     *
     * @param int    $product_id     O ID do produto.
     * @param string $variation      A variação do produto.
     * @param int    $quantity       A quantidade a ser adicionada.
     * @param int    $stock_quantity O estoque disponível para a variação.
     */
    private function add_item_to_session_cart($product_id, $variation, $quantity, $stock_quantity) {
        $cart = $this->session->userdata('cart') ?? [];
        $key = md5($product_id . $variation);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
            // Limita a quantidade à disponível em estoque
            if ($cart[$key]['quantity'] > $stock_quantity) {
                $cart[$key]['quantity'] = $stock_quantity;
            }
        } else {
            $cart[$key] = [
                'product_id' => $product_id,
                'variation'  => $variation,
                'quantity'   => $quantity,
                'price'      => $this->Product_model->get_price($product_id),
                'name'       => $this->Product_model->get_name($product_id),
                'image'      => $this->Product_model->get_image($product_id)
            ];
        }

        $this->session->set_userdata('cart', $cart);
    }

    /**
     * Calcula a quantidade total de itens no carrinho.
     *
     * @return int A soma das quantidades de todos os itens no carrinho.
     */
    private function calculate_cart_total_quantity() {
        $cart = $this->session->userdata('cart') ?? [];
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Prepara os dados do carrinho para serem exibidos na view.
     * Calcula subtotal, frete, valida CEP e aplica cupom.
     *
     * @return array Os dados processados do carrinho.
     */
    private function prepare_cart_data() {
        $data['cart'] = $this->session->userdata('cart') ?? [];
        $data['subtotal'] = $this->calculate_subtotal();

        $cep = $this->input->post('cep') ?? $this->session->userdata('cep');
        $data['cep'] = $cep;
        $data['cep_error'] = null;
        $cep_valid = false;

        if (!empty($cep)) {
            $clean_cep = preg_replace('/[^0-9]/', '', $cep);

            if (strlen($clean_cep) === 8) {
                $url = "https://viacep.com.br/ws/{$clean_cep}/json/";
                $response = @file_get_contents($url);

                if ($response === FALSE) {
                    $data['cep_error'] = 'Não foi possível consultar o CEP. Tente novamente.';
                } else {
                    $cep_info = json_decode($response);
                    if (isset($cep_info->erro) && $cep_info->erro) {
                        $data['cep_error'] = 'CEP inválido.';
                    } else {
                        $cep_valid = true;
                        $this->session->set_userdata('cep', $clean_cep);
                        $data['cep_info'] = $cep_info;
                    }
                }
            } else {
                $data['cep_error'] = 'Formato de CEP inválido.';
            }
        } else if ($this->input->post('btn_finalizar')) {
            $data['cep_error'] = 'Informe um CEP válido para finalizar o pedido.';
        }

        // Define o frete com base no subtotal e validação do CEP
        if (!$cep_valid) {
            $data['shipping'] = null;
            $data['shipping_text_class'] = '';
        } else {
            if ($data['subtotal'] > 200) {
                $data['shipping'] = 0;
                $data['shipping_text_class'] = 'text-success'; // Frete grátis
            } elseif ($data['subtotal'] >= 52 && $data['subtotal'] <= 166.59) {
                $data['shipping'] = 15;
                $data['shipping_text_class'] = '';
            } else {
                $data['shipping'] = 20;
                $data['shipping_text_class'] = '';
            }
        }

        // Recupera e aplica o cupom de desconto da sessão
        $coupon = $this->session->userdata('applied_coupon');
        $data['discount'] = 0;

        if ($coupon && isset($coupon['valid']) && $coupon['valid'] === true) {
            $data['discount'] = $coupon['discount'];
        }

        // Calcula o total final do pedido
        $data['total'] = $data['subtotal'] + ($data['shipping'] ?? 0) - $data['discount'];
        if ($data['total'] < 0) {
            $data['total'] = 0; // Evita que o total seja negativo
        }

        $data['coupon_applied'] = $coupon;

        return $data;
    }

    /**
     * Retorna uma resposta JSON.
     *
     * @param bool        $success Se a operação foi bem-sucedida.
     * @param string|null $error   Mensagem de erro, se houver.
     * @param array       $extra   Dados adicionais a serem incluídos na resposta.
     */
    private function json_response($success, $error = null, $extra = []) {
        $response = ['success' => $success];
        if ($error) $response['error'] = $error;
        if ($extra) $response = array_merge($response, $extra);

        header('Content-Type: application/json'); // Garante que a resposta é JSON
        echo json_encode($response);
    }
}