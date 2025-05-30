<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Busca cupom pelo código
     *
     * @param string $code
     * @return object|null
     */
    public function get_by_code($code)
    {
        return $this->db->where('code', $code)
                        ->get('coupons')
                        ->row();
    }

    /**
     * Busca cupom pelo ID
     *
     * @param int $id
     * @return object|null
     */
    public function get_by_id(int $id) {
        return $this->db->get_where('coupons', ['id' => $id])->row();
    }

    /**
     * Retorna todos os cupons
     *
     * @return array
     */
    public function get_all(): array {
        return $this->db->get('coupons')->result();
    }

    /**
     * Cria ou atualiza um cupom
     *
     * @param array $data
     * @param int|null $id
     * @return int|bool
     */
    public function save(array $data, int $id = null) {
        if ($id === null) {
            $this->db->insert('coupons', $data);
            return $this->db->insert_id();
        }

        $this->db->where('id', $id);
        return $this->db->update('coupons', $data);
    }

    /**
     * Remove um cupom pelo ID
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $this->db->where('id', $id);
        return $this->db->delete('coupons');
    }
}
