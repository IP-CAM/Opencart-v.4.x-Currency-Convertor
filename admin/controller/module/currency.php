<?php

namespace Opencart\Admin\Controller\Extension\Currency\Module;
/**
 * Class Currency
 *
 * @package Opencart\Admin\Controller\Extension\Currency\Module
 */
class Currency extends \Opencart\System\Engine\Controller
{
    /**
     * Index
     *
     * @return void
     */
    public function index(): void
    {
        $this->load->language('extension/currency/module/currency');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/currency/module/currency', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['save'] = $this->url->link('extension/currency/module/currency.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // Module Settings
        $data['module_currency_status'] = $this->config->get('module_currency_status');
        $data['module_currency_rate']   = $this->config->get('module_currency_rate');
        $data['config_currency']        = $this->config->get('config_currency');

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/currency/module/currency', $data));
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $this->load->language('extension/currency/module/currency');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/currency/module/currency')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->request->post['module_currency_rate'] = $this->currency("EUR");

            $this->model_setting_setting->editSetting('module_currency', $this->request->post);

            $json['success'] = $this->language->get('text_success');

            $json['redirect'] = $this->url->link('extension/currency/module/currency', 'user_token=' . $this->session->data['user_token'], true);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Install
     *
     * @return void
     */
    public function install(): void
    {
        @mail('info@opencartbulgaria.com', 'OpenCartBulgaria Currency 4 installed (v4.0.0)', HTTP_CATALOG . ' - ' . $this->config->get('config_name') . "\r\n" . 'version - ' . VERSION . "\r\n" . 'IP - ' . $this->request->server['REMOTE_ADDR'], 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n" . 'From: ' . $this->config->get('config_owner') . ' <' . $this->config->get('config_email') . '>' . "\r\n");
    }

    /**
     * @param string $default
     * @return float
     */
    private function currency(string $default = ''): float
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($status == 200) {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->loadXml($response);

            $cube = $dom->getElementsByTagName('Cube')->item(0);

            // Compile all the rates into an array
            $currencies = [];

            $currencies['EUR'] = 1.0000;

            foreach ($cube->getElementsByTagName('Cube') as $currency) {
                $currencies[$currency->getAttribute('currency')] = $currency->getAttribute('rate');
            }

            if (isset($currencies[$default])) {
                $value = $currencies[$default];
            } else {
                $value = $currencies['EUR'];
            }

            if (count($currencies) > 1) {
                if (isset($currencies[$this->config->get('config_currency')])) {

                    if (isset($currencies[$this->config->get('config_currency')])) {
                        $from = $currencies['EUR'];
                        $to   = $currencies[$this->config->get('config_currency')];

                        return 1 / ($value * ($from / $to));
                    }
                }
            }
        } else {
            return "0.0000";
        }
        return "0.0000";
    }
}