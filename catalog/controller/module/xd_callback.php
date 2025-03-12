<?php
class ControllerModuleXDCallback extends Controller
{
    private $error = array();

    private $field1_status = null;
    private $field2_status = null;
    private $field3_status = null;
    private $captcha = null;
    private $spam_protection = null;
    private $exan_status = null;

    public function index()
    {
        $data = [];
        $xd_callback_setting = $this->config->get('xd_callback');
        $data['status'] = false;
        if ($xd_callback_setting) {
            $data['status'] = boolval($xd_callback_setting['module_xd_callback_status']);
            if ($data['status']) {
                // Get language data
                $this->load->language('module/xd_callback');
                $data['modal_title'] = $this->language->get('modal_title');
                $data['required_text'] = $this->language->get('required_text');
                $data['error_required'] = $this->language->get('error_required');
                $data['error_sending'] = $this->language->get('error_sending');
                $data['submit_button'] = $this->language->get('submit_button');

                $current_language_id = $this->config->get('config_language_id');
                $data['button_name'] = $xd_callback_setting['button_name'][$current_language_id];
                if ($data['button_name'] == '') {
                    $data['button_name'] = $this->language->get('button_name');
                }
                $data['success_field'] = html_entity_decode($xd_callback_setting['success_field'][$current_language_id], ENT_QUOTES, 'UTF-8');
                if ($data['success_field'] == '') {
                    $data['success_field'] = $this->language->get('success_field');
                }

                // Fields
                $data['field1_status'] = intval($xd_callback_setting['field1_status']); // Name status
                $this->field1_status = $data['field1_status'];
                $data['field1_title'] = $this->language->get('field1_title'); // Name title
                $data['field2_status'] = intval($xd_callback_setting['field2_status']); // Phone status
                $this->field2_status = $data['field2_status'];
                $data['field2_title'] = $this->language->get('field2_title'); // Phone title
                $data['field3_status'] = intval($xd_callback_setting['field3_status']); // Message status
                $this->field3_status = $data['field3_status'];
                $data['field3_title'] = $this->language->get('field3_title'); // Message title

                // Captcha
                $data['captcha'] = $xd_callback_setting['captcha']; // Captcha
                $this->captcha = $data['captcha'];
                $data['captcha_class'] = $xd_callback_setting['captcha'];
                if ($this->config->get($data['captcha'] . '_status')) {
                    $data['captcha'] = $this->load->controller('captcha/' . $data['captcha'], $this->error);
                }

                // Agreement
                $data['agree_status'] = intval($xd_callback_setting['agree_status']);
                if ($data['agree_status'] !== 0) {
                    $this->load->model('catalog/information');
                    $information_info = $this->model_catalog_information->getInformation($data['agree_status']);
                    if ($information_info) {
                        $data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information', 'information_id=' . $data['agree_status'], 'SSL'), $information_info['title']);
                    } else {
                        $data['text_agree'] = '';
                    }
                }

                $data['spam_protection'] = boolval($xd_callback_setting['spam_protection']);
                $this->spam_protection = $data['spam_protection'];
                $data['validation_type'] = $xd_callback_setting['validation_type'];

                // Success
                $data['success_type'] = (isset($xd_callback_setting['success_type'])) ? $xd_callback_setting['success_type'] : 0;
                $data['success_utm'] = (isset($xd_callback_setting['success_utm'])) ? 'utm_source=' . trim($xd_callback_setting['success_utm']) : 'utm_source=xd_callback';

                // Styles
                $data['button_color'] = $xd_callback_setting['button_color'];
                $data['button_position'] = $xd_callback_setting['button_position'];
                $data['modal_style'] = $xd_callback_setting['modal_style'];

                // Analytics
                $data['exan_status'] = boolval($xd_callback_setting['exan_status']);
                $this->exan_status = $data['exan_status'];
            }
        }
        return $this->load->view('default/template/module/xd_callback.tpl', $data);
    }

    public function submit()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {

            $this->load->language('module/xd_callback');
            $json = array();
            $mail_text = '';

            $json['data'] = $this->request->post;

            if (isset($this->request->post['xd_callback_name'])) {
                $xd_callback_name = $this->request->post['xd_callback_name'];
                $mail_text .= $this->language->get('text_name') . $xd_callback_name . " \r\n";
            }

            if (isset($this->request->post['xd_callback_phone'])) {
                $xd_callback_phone = $this->request->post['xd_callback_phone'];
                $mail_text .= $this->language->get('text_phone') . $xd_callback_phone . " \r\n";
            }

            if (isset($this->request->post['xd_callback_message'])) {
                $xd_callback_message = $this->request->post['xd_callback_message'];
                $mail_text .= $this->language->get('text_message') . $xd_callback_message . " \r\n";
            }

            $mail_text .= " \r\n";

            // Private data
            $mail_text .= " \r\n" . $this->language->get('xd_callback_sb_private_title') . " \r\n";
            if (!empty($this->request->server['REMOTE_ADDR'])) {
                $ip = $this->request->server['REMOTE_ADDR'];
                $mail_text .= $this->language->get('text_ip') . $ip . " \r\n";
            }

            if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
                $forwarded_ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
                $mail_text .= $this->language->get('text_forwarded_ip') . $forwarded_ip . " \r\n";
            } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
                $forwarded_ip = $this->request->server['HTTP_CLIENT_IP'];
                $mail_text .= $this->language->get('text_forwarded_ip') . $forwarded_ip . " \r\n";
            }

            if (isset($this->request->server['HTTP_USER_AGENT'])) {
                $user_agent = $this->request->server['HTTP_USER_AGENT'];
                $mail_text .= $this->language->get('text_user_agent') . $user_agent . " \r\n";
            }

            if (isset($this->request->post['xd_callback_sb_udata_vst']) && $this->request->post['xd_callback_sb_udata_vst'] != '') {
                $xd_callback_sb_udata_vst = $this->request->post['xd_callback_sb_udata_vst'];
                $mail_text .= $this->language->get('xd_callback_sb_udata_vst') . $xd_callback_sb_udata_vst . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_promo_code']) && $this->request->post['xd_callback_sb_promo_code'] != '' && $this->request->post['xd_callback_sb_promo_code'] != 'undefined') {
                $xd_callback_sb_promo_code = $this->request->post['xd_callback_sb_promo_code'];
                $mail_text .= $this->language->get('xd_callback_sb_promo_code') . $xd_callback_sb_promo_code . " \r\n";
            }
            // Private data end

            // Source first visit
            $mail_text .= " \r\n" . $this->language->get('xd_callback_sb_first_visit_title') . " \r\n";
            if (isset($this->request->post['xd_callback_sb_first_typ']) && $this->request->post['xd_callback_sb_first_typ'] != '') {
                $xd_callback_sb_first_typ = $this->request->post['xd_callback_sb_first_typ'];
                $mail_text .= $this->language->get('xd_callback_sb_first_typ') . $xd_callback_sb_first_typ . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_first_src']) && $this->request->post['xd_callback_sb_first_src'] != '') {
                $xd_callback_sb_first_src = $this->request->post['xd_callback_sb_first_src'];
                $mail_text .= $this->language->get('xd_callback_sb_first_src') . $xd_callback_sb_first_src . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_first_mdm']) && $this->request->post['xd_callback_sb_first_mdm'] != '') {
                $xd_callback_sb_first_mdm = $this->request->post['xd_callback_sb_first_mdm'];
                $mail_text .= $this->language->get('xd_callback_sb_first_mdm') . $xd_callback_sb_first_mdm . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_first_cmp']) && $this->request->post['xd_callback_sb_first_cmp'] != '') {
                $xd_callback_sb_first_cmp = $this->request->post['xd_callback_sb_first_cmp'];
                $mail_text .= $this->language->get('xd_callback_sb_first_cmp') . $xd_callback_sb_first_cmp . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_first_cnt']) && $this->request->post['xd_callback_sb_first_cnt'] != '') {
                $xd_callback_sb_first_cnt = $this->request->post['xd_callback_sb_first_cnt'];
                $mail_text .= $this->language->get('xd_callback_sb_first_cnt') . $xd_callback_sb_first_cnt . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_first_trm']) && $this->request->post['xd_callback_sb_first_trm'] != '') {
                $xd_callback_sb_first_trm = $this->request->post['xd_callback_sb_first_trm'];
                $mail_text .= $this->language->get('xd_callback_sb_first_trm') . $xd_callback_sb_first_trm . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_first_add_fd']) && $this->request->post['xd_callback_sb_first_add_fd'] != '') {
                $xd_callback_sb_first_add_fd = $this->request->post['xd_callback_sb_first_add_fd'];
                $mail_text .= $this->language->get('xd_callback_sb_first_add_fd') . $xd_callback_sb_first_add_fd . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_first_add_ep']) && $this->request->post['xd_callback_sb_first_add_ep'] != '') {
                $xd_callback_sb_first_add_ep = $this->request->post['xd_callback_sb_first_add_ep'];
                $mail_text .= $this->language->get('xd_callback_sb_first_add_ep') . $xd_callback_sb_first_add_ep . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_first_add_rf']) && $this->request->post['xd_callback_sb_first_add_rf'] != '') {
                $xd_callback_sb_first_add_rf = $this->request->post['xd_callback_sb_first_add_rf'];
                $mail_text .= $this->language->get('xd_callback_sb_first_add_rf') . $xd_callback_sb_first_add_rf . " \r\n";
            }
            // Source first visit end

            // Source current visit
            $mail_text .= " \r\n" . $this->language->get('xd_callback_sb_current_visit_title') . " \r\n";
            if (isset($this->request->post['xd_callback_sb_current_typ']) && $this->request->post['xd_callback_sb_current_typ'] != '') {
                $xd_callback_sb_current_typ = $this->request->post['xd_callback_sb_current_typ'];
                $mail_text .= $this->language->get('xd_callback_sb_current_typ') . $xd_callback_sb_current_typ . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_current_src']) && $this->request->post['xd_callback_sb_current_src'] != '') {
                $xd_callback_sb_current_src = $this->request->post['xd_callback_sb_current_src'];
                $mail_text .= $this->language->get('xd_callback_sb_current_src') . $xd_callback_sb_current_src . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_current_mdm']) && $this->request->post['xd_callback_sb_current_mdm'] != '') {
                $xd_callback_sb_current_mdm = $this->request->post['xd_callback_sb_current_mdm'];
                $mail_text .= $this->language->get('xd_callback_sb_current_mdm') . $xd_callback_sb_current_mdm . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_current_cmp']) && $this->request->post['xd_callback_sb_current_cmp'] != '') {
                $xd_callback_sb_current_cmp = $this->request->post['xd_callback_sb_current_cmp'];
                $mail_text .= $this->language->get('xd_callback_sb_current_cmp') . $xd_callback_sb_current_cmp . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_current_cnt']) && $this->request->post['xd_callback_sb_current_cnt'] != '') {
                $xd_callback_sb_current_cnt = $this->request->post['xd_callback_sb_current_cnt'];
                $mail_text .= $this->language->get('xd_callback_sb_current_cnt') . $xd_callback_sb_current_cnt . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_current_trm']) && $this->request->post['xd_callback_sb_current_trm'] != '') {
                $xd_callback_sb_current_trm = $this->request->post['xd_callback_sb_current_trm'];
                $mail_text .= $this->language->get('xd_callback_sb_current_trm') . $xd_callback_sb_current_trm . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_current_add_fd']) && $this->request->post['xd_callback_sb_current_add_fd'] != '') {
                $xd_callback_sb_current_add_fd = $this->request->post['xd_callback_sb_current_add_fd'];
                $mail_text .= $this->language->get('xd_callback_sb_current_add_fd') . $xd_callback_sb_current_add_fd . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_current_add_ep']) && $this->request->post['xd_callback_sb_current_add_ep'] != '') {
                $xd_callback_sb_current_add_ep = $this->request->post['xd_callback_sb_current_add_ep'];
                $mail_text .= $this->language->get('xd_callback_sb_current_add_ep') . $xd_callback_sb_current_add_ep . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_current_add_rf']) && $this->request->post['xd_callback_sb_current_add_rf'] != '') {
                $xd_callback_sb_current_add_rf = $this->request->post['xd_callback_sb_current_add_rf'];
                $mail_text .= $this->language->get('xd_callback_sb_current_add_rf') . $xd_callback_sb_current_add_rf . " \r\n";
            }
            // Source current visit end

            // Current session
            $mail_text .= " \r\n" . $this->language->get('xd_callback_sb_session_title') . " \r\n";
            if (isset($this->request->post['xd_callback_sb_session_pgs']) && $this->request->post['xd_callback_sb_session_pgs'] != '') {
                $xd_callback_sb_session_pgs = $this->request->post['xd_callback_sb_session_pgs'];
                $mail_text .= $this->language->get('xd_callback_sb_session_pgs') . $xd_callback_sb_session_pgs . " \r\n";
            }
            if (isset($this->request->post['xd_callback_sb_session_cpg']) && $this->request->post['xd_callback_sb_session_cpg'] != '') {
                $xd_callback_sb_session_cpg = $this->request->post['xd_callback_sb_session_cpg'];
                $mail_text .= $this->language->get('xd_callback_sb_session_cpg') . $xd_callback_sb_session_cpg . " \r\n";
            }
            // Current session end

            $from_email = 'xd_callback@' . $_SERVER['SERVER_NAME'];
            $sender_name = $this->config->get('config_name');
            $mail_title = sprintf($this->language->get('text_mail_title'), $this->config->get('config_name'));

            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($this->config->get('config_email'));
            // $mail->setTo('domus159@gmail.com');
            $mail->setFrom($from_email);
            $mail->setSender($sender_name);
            $mail->setSubject($mail_title);
            $mail->setText($mail_text);
            $mail_result = $mail->send();

            if (!$mail_result) {
                $json['success'] = 'Success sending';
            } else {
                $json['error'] = 'Error sending';
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $json['error'] = $this->error;
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }

    protected function validate()
    {
        $xd_callback_setting = $this->config->get('xd_callback');
        $this->load->language('module/xd_callback');

        // Validate spam protection
        $this->spam_protection = boolval($xd_callback_setting['spam_protection']);
        if ($this->spam_protection && (strlen(trim($this->request->post['xd_callback_email'])) > 0 || strlen(trim($this->request->post['xd_callback_surname'])) > 0)) {
            $this->error['message'] = $this->language->get('spam_protection');
            $this->error['input'] = 'spam_protection';
            return false;
        }

        // Validate name
        $this->field1_status = intval($xd_callback_setting['field1_status']);
        if ($this->field1_status === 2) {
            if (strlen($this->request->post['xd_callback_name']) < 1 || strlen($this->request->post['xd_callback_name']) > 64) {
                $this->error['message'] = $this->language->get('error_name');
                $this->error['input'] = 'xd_callback_name';
                return false;
            }
        }

        // Validate phone number
        $this->field2_status = intval($xd_callback_setting['field2_status']);
        if ($this->field2_status === 2) {
            $min_length = 9;
            $max_length = 20;
            $this->phone_validation_type = $xd_callback_setting['validation_type'];
            if ($this->phone_validation_type) {
                $min_length = utf8_strlen($this->phone_validation_type);
                $max_length = utf8_strlen($this->phone_validation_type);
            }
            if ((utf8_strlen($this->request->post['xd_callback_phone']) < $min_length) || (utf8_strlen($this->request->post['xd_callback_phone']) > $max_length)) {
                $this->error['message'] = $this->language->get('error_phone');
                $this->error['input'] = 'xd_callback_phone';
                return false;
            }

            $phone_number_validation_regex = "/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/";
            if (!preg_match($phone_number_validation_regex, $this->request->post['xd_callback_phone'])) {
                $this->error['message'] = $this->language->get('error_phone');
                $this->error['input'] = 'xd_callback_phone';
                return false;
            }
        }

        // Validate message
        $this->field3_status = intval($xd_callback_setting['field3_status']);
        if ($this->field3_status === 2) {
            if (strlen($this->request->post['xd_callback_message']) < 1 || strlen($this->request->post['xd_callback_message']) > 9000) {
                $this->error['message'] = $this->language->get('error_message');
                $this->error['input'] = 'xd_callback_message';
                return false;
            }
        }

        // Captcha
        $data['captcha'] = (isset($xd_callback_setting['captcha'])) ? $xd_callback_setting['captcha'] : 0; // Captcha
        $this->captcha = $data['captcha'];
        if ($this->captcha && $this->config->get($this->captcha . '_status')) {
            $captcha = $this->load->controller('captcha/' . $this->captcha . '/validate');
            if ($captcha) {
                $this->error['message'] = $this->language->get('error_captcha');
                $this->error['input'] = 'xd_callback_captcha';
                return false;
            }
        }

        return !$this->error;
    }
}
