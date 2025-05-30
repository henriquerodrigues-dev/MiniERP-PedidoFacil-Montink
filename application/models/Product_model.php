<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Retorna todos os produtos
     *
     * @return array
     */
    public function get_all(): array {
        return $this->db->get('products')->result();
    }

    /**
     * Retorna um produto pelo ID
     *
     * @param int $id
     * @return object|null
     */
    public function get_by_id(int $id) {
        return $this->db->where('id', $id)->get('products')->row();
    }

    /**
     * Insere um novo produto e retorna o ID inserido
     *
     * @param array $data
     * @return int
     */
    public function insert(array $data): int {
        $this->db->insert('products', $data);
        return $this->db->insert_id();
    }

    /**
     * Atualiza os dados de um produto pelo ID
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        return $this->db->where('id', $id)->update('products', $data);
    }

    /**
     * Deleta um produto pelo ID (apenas da tabela products)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        return $this->db->where('id', $id)->delete('products');
    }

    /**
     * Deleta produto e estoque relacionado em transação
     *
     * @param int $id
     * @return bool Retorna true se sucesso, false caso contrário
     */
    public function delete_product(int $id): bool {
        $this->db->trans_start();

        // Deleta estoque relacionado (caso FK não esteja configurada com ON DELETE CASCADE)
        $this->db->where('product_id', $id)->delete('stock');

        // Deleta produto
        $this->db->where('id', $id)->delete('products');

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Retorna o estoque do produto
     *
     * @param int $product_id
     * @return array
     */
    public function get_stock_by_product(int $product_id): array {
        return $this->db->where('product_id', $product_id)->get('stock')->result();
    }

    /**
     * Retorna o estoque do produto por variação
     *
     * @param int $product_id
     * @param string $variation
     * @return object|null
     */
    public function get_stock_by_product_and_variation(int $product_id, string $variation) {
        return $this->db
            ->where('product_id', $product_id)
            ->where('variation', $variation)
            ->get('stock')
            ->row();
    }

    /**
     * Retorna o preço do produto
     *
     * @param int $product_id
     * @return float|null
     */
    public function get_price(int $product_id): ?float {
        $query = $this->db->select('price')->from('products')->where('id', $product_id)->get();

        if ($query->num_rows() === 1) {
            return (float) $query->row()->price;
        }

        return null;
    }

    /**
     * Retorna o nome do produto
     *
     * @param int $product_id
     * @return string|null
     */
    public function get_name(int $product_id): ?string {
        $query = $this->db->select('name')->from('products')->where('id', $product_id)->get();

        if ($query->num_rows() === 1) {
            return $query->row()->name;
        }

        return null;
    }

    /**
     * Retorna a imagem do produto
     *
     * @param int $product_id
     * @return string|null
     */
    public function get_image(int $product_id): ?string {
        $query = $this->db->select('image_path')->from('products')->where('id', $product_id)->get();

        if ($query->num_rows() === 1) {
            return $query->row()->image_path;
        }

        return null;
    }

    /**
     * Retorna as variações de estoque do produto
     *
     * @param int $product_id
     * @return array
     */
    public function get_variations_by_product(int $product_id): array {
        return $this->db->where('product_id', $product_id)->get('stock')->result();
    }

    /**
     * Deleta estoque de uma variação do produto
     *
     * @param int $product_id
     * @param string $variation
     * @return bool
     */
    public function delete_stock(int $product_id, string $variation): bool {
        $this->db->where('product_id', $product_id);
        $this->db->where('variation', $variation);
        return $this->db->delete('stock');
    }

    /**
     * Atualiza ou insere estoque para uma variação do produto
     *
     * @param int $product_id
     * @param string $variation
     * @param int $quantity
     * @return bool
     */
    public function update_stock(int $product_id, string $variation, int $quantity): bool {
        $stock = $this->db
            ->where(['product_id' => $product_id, 'variation' => $variation])
            ->get('stock')
            ->row();

        if ($stock) {
            return $this->db->where('id', $stock->id)->update('stock', ['quantity' => $quantity]);
        }

        return $this->db->insert('stock', [
            'product_id' => $product_id,
            'variation' => $variation,
            'quantity' => $quantity
        ]);
    }
}
