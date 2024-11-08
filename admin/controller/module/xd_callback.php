<?php
class ControllerModuleXDZvonok extends Controller
{
    private $error = array();
    public function index()
    {
        $this->load->language('module/callback');
        $this->document->setTitle($this->language->get('heading_name'));
        $this->document->addStyle('view/stylesheet/callback.css');
        $this->document->addStyle('view/stylesheet/css/colorpicker.css');
        $this->document->addScript('view/javascript/jquery/colorpicker.js');
        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('callback', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }
        // Heading
        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_name'] = $this->language->get('heading_name');
        // Text
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        //Buttons
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        // Nav tabs
        $data['settings_main'] = $this->language->get('settings_main');
        $data['settings_style'] = $this->language->get('settings_style');
        $data['settings_sms'] = $this->language->get('settings_sms');
        $data['settings_analytics'] = $this->language->get('settings_analytics');
        $data['text_tab_help'] = $this->language->get('text_tab_help');
        // Fields
        $data['field1_title'] = $this->language->get('field1_title');
        $data['field2_title'] = $this->language->get('field2_title');
        $data['field3_title'] = $this->language->get('field3_title');
        $data['field4_title'] = $this->language->get('field4_title');
        $data['agree_title'] = $this->language->get('agree_title');
        $data['field_required'] = $this->language->get('field_required');
        // Phone validation
        $data['entry_validation_type'] = $this->language->get('entry_validation_type');
        $data['text_validation_type0'] = $this->language->get('text_validation_type0');
        $data['text_validation_type1'] = $this->language->get('text_validation_type1');
        $data['text_validation_type2'] = $this->language->get('text_validation_type2');
        $data['value_validation_type1'] = $this->language->get('value_validation_type1');
        $data['value_validation_type2'] = $this->language->get('value_validation_type2');
        // Entry
        $data['entry_button_name'] = $this->language->get('entry_button_name');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_success_field'] = $this->language->get('entry_success_field');
        $data['success_field_tooltip'] = htmlspecialchars($this->language->get('success_field_tooltip'));

        // Tab Styles
        $data['tab_styles'] = $this->language->get('tab_styles');
        $data['tab_styles_button_color'] = $this->language->get('tab_styles_button_color');

        // Default values
        $data['default_button_name'] = $this->language->get('default_button_name');
        $data['default_success_field'] = $this->language->get('default_success_field');



        // Extended analytics
        $data['exan_form_title'] = $this->language->get('exan_form_title');
        $data['exan_status_title'] = $this->language->get('exan_status_title');

        // Yandex
        $data['ya_form_title'] = $this->language->get('ya_form_title');
        $data['ya_counter_title'] = $this->language->get('ya_counter_title');
        $data['ya_identifier_title'] = $this->language->get('ya_identifier_title');
        $data['ya_identifier_send_title'] = $this->language->get('ya_identifier_send_title');
        $data['ya_identifier_success_title'] = $this->language->get('ya_identifier_success_title');
        $data['ya_target_status_title'] = $this->language->get('ya_target_status_title');

        // Google
        $data['google_form_title'] = $this->language->get('google_form_title');
        $data['google_category_btn_title'] = $this->language->get('google_category_btn_title');
        $data['google_action_btn_title'] = $this->language->get('google_action_btn_title');
        $data['google_category_send_title'] = $this->language->get('google_category_send_title');
        $data['google_action_send_title'] = $this->language->get('google_action_send_title');
        $data['google_category_success_title'] = $this->language->get('google_category_success_title');
        $data['google_action_success_title'] = $this->language->get('google_action_success_title');
        $data['google_target_status_title'] = $this->language->get('google_target_status_title');


        // Help tab
        $data['text_tab_help'] = $this->language->get('text_tab_help');
        $data['text_tab_help_title'] = $this->language->get('text_tab_help_title');
        $data['text_help'] = $this->language->get('text_help');



        $this->load->model('catalog/information');
        $data['informations'] = $this->model_catalog_information->getInformations();
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_name'),
            'href' => $this->url->link('module/callback', 'token=' . $this->session->data['token'], 'SSL')
        );
        $data['action'] = $this->url->link('module/callback', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

        // New var
        if (isset($this->request->post['callback'])) {
            $data['callback'] = $this->request->post['callback'];
        } else {
            $data['callback'] = $this->config->get('callback');
        }

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();
        $languages = $this->model_localisation_language->getLanguages();
        foreach ($languages as $language) {
            $language_id = $language['language_id'];
            if (isset($this->request->post['callback'])) {
                $post_data = $this->request->post['callback'];
                $data['callback']['button_name'][$language['language_id']] = $post_data['button_name'][$language['language_id']];
                $data['callback']['success_field'][$language['language_id']] = $post_data['success_field'][$language['language_id']];
            } else {
                if (isset($this->config->get('callback')['button_name'][$language['language_id']]) && $this->config->get('callback')['button_name'][$language['language_id']] != '') {
                    $data['callback']['button_name'][$language['language_id']] = $this->config->get('callback')['button_name'][$language['language_id']];
                } else {
                    // Set default values
                    if ($language_id == $this->config->get('config_language_id')) {
                        $data['callback']['button_name'][$language['language_id']] = $this->language->get('default_button_name');
                    }
                }
                if (isset($this->config->get('callback')['success_field'][$language['language_id']]) && $this->config->get('callback')['success_field'][$language['language_id']] != '') {
                    $data['callback']['success_field'][$language['language_id']] = $this->config->get('callback')['success_field'][$language['language_id']];
                } else {
                    // Set default values
                    if ($language_id == $this->config->get('config_language_id')) {
                        $data['callback']['success_field'][$language['language_id']] = $this->language->get('default_success_field');
                    }
                }
            }
        }
        // Set default values
        if (!isset($data['callback']['button_color']) || $data['callback']['button_color'] == '') {
            $data['callback']['button_color'] = '#0066cc';
        }

        /********************* Captchas ********************************/
        $this->load->model('extension/extension');

        $data['captchas'] = array();

        // Get a list of installed captchas
        $extensions = $this->model_extension_extension->getInstalled('captcha');

        foreach ($extensions as $code) {
            $this->load->language('captcha/' . $code);

            if ($this->config->has($code . '_status')) {
                $data['captchas'][] = array(
                    'text'  => $this->language->get('heading_title'),
                    'value' => $code
                );
            }
        }

        /********************* Modal window fields *********************/
        if (isset($this->request->post['callback_field1_status'])) {
            $data['callback_field1_status'] = $this->request->post['callback_field1_status'];
        } else {
            $data['callback_field1_status'] = $this->config->get('callback_field1_status');
        }
        if (isset($this->request->post['callback_field1_required'])) {
            $data['callback_field1_required'] = $this->request->post['callback_field1_required'];
        } else {
            $data['callback_field1_required'] = $this->config->get('callback_field1_required');
        }
        if (isset($this->request->post['callback_field2_status'])) {
            $data['callback_field2_status'] = $this->request->post['callback_field2_status'];
        } else {
            $data['callback_field2_status'] = $this->config->get('callback_field2_status');
        }
        if (isset($this->request->post['callback_field2_required'])) {
            $data['callback_field2_required'] = $this->request->post['callback_field2_required'];
        } else {
            $data['callback_field2_required'] = $this->config->get('callback_field2_required');
        }
        if (isset($this->request->post['callback_field3_status'])) {
            $data['callback_field3_status'] = $this->request->post['callback_field3_status'];
        } else {
            $data['callback_field3_status'] = $this->config->get('callback_field3_status');
        }
        if (isset($this->request->post['callback_field3_required'])) {
            $data['callback_field3_required'] = $this->request->post['callback_field3_required'];
        } else {
            $data['callback_field3_required'] = $this->config->get('callback_field3_required');
        }

        if (isset($this->request->post['callback_agree_status'])) {
            $data['callback_agree_status'] = $this->request->post['callback_agree_status'];
        } else {
            $data['callback_agree_status'] = $this->config->get('callback_agree_status');
        }
        if (isset($this->request->post['callback_validation_type'])) {
            $data['callback_validation_type'] = $this->request->post['callback_validation_type'];
        } else {
            $data['callback_validation_type'] = $this->config->get('callback_validation_type');
        }
        /********************* STATUS *********************/
        if (isset($this->request->post['callback_status'])) {
            $data['callback_status'] = $this->request->post['callback_status'];
        } else {
            $data['callback_status'] = $this->config->get('callback_status');
        }
        /*
		if (isset($this->request->post['callback_style_status'])) {
			$data['callback_style_status'] = $this->request->post['callback_style_status'];
		} else {
			$data['callback_style_status'] = $this->config->get('callback_style_status');
		}
		*/
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('module/callback.tpl', $data));
    }
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'module/callback')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
