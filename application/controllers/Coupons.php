<?php
class Coupons extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Coupon_model');
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
    }

    public function index() {
        $data['coupons'] = $this->Coupon_model->get_all();
        $this->load->view('coupons/index', $data);
    }

    public function create() {
        // Ajusta valores numéricos para formato com ponto decimal (substitui vírgulas por pontos)
        $_POST['discount'] = str_replace(',', '.', $this->input->post('discount'));
        $_POST['min_value'] = str_replace(',', '.', $this->input->post('min_value'));

        // Define regras de validação para o formulário de criação
        $this->form_validation->set_rules('code', 'Código', 'required|is_unique[coupons.code]', [
            'required' => 'O campo {field} é obrigatório.',
            'is_unique' => 'O código informado já está em uso.'
        ]);
        $this->form_validation->set_rules('discount', 'Desconto', 'required|callback_valid_decimal_or_integer', [
            'required' => 'O campo {field} é obrigatório.'
        ]);
        $this->form_validation->set_rules('min_value', 'Valor Mínimo', 'required|callback_valid_decimal_or_integer', [
            'required' => 'O campo {field} é obrigatório.'
        ]);
        $this->form_validation->set_rules('valid_until', 'Validade', 'required|callback_valid_date', [
            'required' => 'O campo {field} é obrigatório.'
        ]);

        if ($this->form_validation->run() === FALSE) {
            // Exibe formulário de criação (primeira exibição ou erro na validação)
            $this->load->view('coupons/create');
        } else {
            // Prepara dados para salvar no banco
            $data = [
                'code' => strtoupper($this->input->post('code')),
                'discount' => $_POST['discount'],  // valor já formatado com ponto decimal
                'min_value' => $_POST['min_value'], // valor já formatado com ponto decimal
                'valid_until' => $this->input->post('valid_until'),
            ];
            $this->Coupon_model->save($data);
            redirect('coupons');
        }
    }

    public function edit($id) {
        $coupon = $this->Coupon_model->get_by_id($id);
        if (!$coupon) show_404();

        // Se formulário submetido, ajusta valores numéricos para formato com ponto decimal
        if ($this->input->post()) {
            $_POST['discount'] = str_replace(',', '.', $this->input->post('discount'));
            $_POST['min_value'] = str_replace(',', '.', $this->input->post('min_value'));
        }

        // Define regras de validação para o formulário de edição
        $this->form_validation->set_rules('code', 'Código', 'required|callback_check_code_unique['.$id.']');
        $this->form_validation->set_rules('discount', 'Desconto', 'required|callback_valid_decimal_or_integer');
        $this->form_validation->set_rules('min_value', 'Valor Mínimo', 'required|callback_valid_decimal_or_integer');
        $this->form_validation->set_rules('valid_until', 'Validade', 'required|callback_valid_date');

        if ($this->form_validation->run() === FALSE) {
            // Em caso de erro ou primeira exibição, prepara dados para preencher o formulário
            if ($this->input->post()) {
                // Preenche formulário com dados enviados pelo usuário (após erro na validação)
                $data['coupon'] = (object) [
                    'id' => $id,
                    'code' => $this->input->post('code'),
                    'discount' => $this->input->post('discount'),
                    'min_value' => $this->input->post('min_value'),
                    'valid_until' => $this->input->post('valid_until'),
                ];
            } else {
                // Preenche formulário com dados atuais do banco (primeira exibição)
                $data['coupon'] = $coupon;
            }
            $this->load->view('coupons/edit', $data);
        } else {
            // Salva os dados atualizados e redireciona
            $data = [
                'code' => strtoupper($this->input->post('code')),
                'discount' => $_POST['discount'],
                'min_value' => $_POST['min_value'],
                'valid_until' => $this->input->post('valid_until'),
            ];
            $this->Coupon_model->save($data, $id);
            redirect('coupons');
        }
    }

    // Valida se o valor é numérico (decimal ou inteiro)
    public function valid_decimal_or_integer($value) {
        if (is_numeric($value)) {
            return TRUE;
        }
        $this->form_validation->set_message('valid_decimal_or_integer', 'O campo {field} deve conter um número válido.');
        return FALSE;
    }

    // Exclui cupom pelo ID e redireciona para lista
    public function delete($id) {
        $this->Coupon_model->delete($id);
        redirect('coupons');
    }

    // Callback para validar data no formato YYYY-MM-DD
    public function valid_date($date) {
        if (DateTime::createFromFormat('Y-m-d', $date) !== FALSE) {
            return TRUE;
        }
        $this->form_validation->set_message('valid_date', 'O campo {field} deve ser uma data válida no formato YYYY-MM-DD.');
        return FALSE;
    }

    // Callback para validar código único no editar (ignora o registro atual)
    public function check_code_unique($code, $id) {
        $existing = $this->Coupon_model->get_by_code($code);
        if ($existing && $existing->id != $id) {
            $this->form_validation->set_message('check_code_unique', 'O código do cupom já está em uso.');
            return FALSE;
        }
        return TRUE;
    }
}
