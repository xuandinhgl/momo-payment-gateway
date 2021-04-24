<?php

use MService\Payment\Shared\Constants\Parameter;

class Dht_Momo_Gateway extends WC_Payment_Gateway
{

    private $testmode;
    private $partner_code;
    private $access_key;
    private $secret_key;
    private $notify_url;
    private $target_environment;

    public function __construct()
    {
        $this->id = DHT_MOMO_GATEWAY_ID;
        $this->icon = DHT_MOMO_PAYMENT_LOGO;
        $this->has_fields = true;
        $this->method_title = DHT_MOMO_GATEWAY_TITLE;
        $this->method_description = DHT_MOMO_GATEWAY_DESC;

        // gateways can support subscriptions, refunds, saved payment methods,
        // but in this tutorial we begin with simple payments
        $this->supports = [
            'products',
            'refunds'
        ];

        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->partner_code = $this->get_option('partner_code');
        $this->access_key = $this->get_option('access_key');
        $this->secret_key = $this->get_option('secret_key');

        $this->target_environment = $this->testmode ? 'development' : 'production';

        $this->notify_url = site_url() . '/wc-api/' . DHT_MOMO_WEBHOOK;

        // This action hook saves the settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);

        add_action('woocommerce_thankyou_' . strtolower(DHT_MOMO_GATEWAY_NAME), [$this, 'update_order_status']);
        add_action("woocommerce_api_" . DHT_MOMO_WEBHOOK, array($this, 'webhook'));

    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => 'Enable/Disable',
                'label' => 'Enable Momo gateway',
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no'
            ],
            'title' => [
                'title' => 'Title',
                'type' => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default' => 'Payment out with Momo',
                'desc_tip' => true,
            ],
            'description' => [
                'title' => 'Description',
                'type' => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default' => 'Pay with your Momo account.',
            ],
            'testmode' => [
                'title' => 'Test mode',
                'label' => 'Enable Test Mode',
                'type' => 'checkbox',
                'description' => 'Place the payment gateway in test mode using development environment.',
                'default' => 'yes',
                'desc_tip' => true,
            ],
            'partner_code' => [
                'title' => 'Partner code',
                'type' => 'text'
            ],
            'access_key' => [
                'title' => 'Access key',
                'type' => 'text',
            ],
            'secret_key' => [
                'title' => 'Secret key',
                'type' => 'password'
            ]
        ];

    }


    public function webhook()
    {
        $this->update_order_status($_REQUEST[Parameter::ORDER_ID]);

        if(defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(sprintf('IPN Order %s', $_REQUEST[Parameter::ORDER_ID]));
            error_log(json_encode($_REQUEST, JSON_PRETTY_PRINT));
        }

    }

    public function process_payment($order_id)
    {


        $order = wc_get_order($order_id);

        $orderInfo = sprintf(__('Payment order %s at %s', 'dht-momo'), $order_id, get_bloginfo('name'));


        $paymentData = [
            'orderId' => $order_id,
            'notifyUrl' => $this->notify_url,
            'returnUrl' => $this->get_return_url($order),
            'targetEnvironment' => $this->target_environment,
            'orderInfo' => $orderInfo,
            'amount' => (string)$order->get_total('edit'),
            'requestId' => $order_id,
            'accessKey' => $this->access_key,
            'partnerCode' => $this->partner_code,
            'secretKey' => $this->secret_key,
        ];

        $sc = new DHT_Momo_Payment_Process($paymentData);

        $response = $sc->generateLinkMomo();
        if ($response['success']) {
            $order->update_status('on-hold', __('Awaiting payment', 'dht-momo'));

            // Reduce stock levels
            wc_reduce_stock_levels($order_id);

            // Remove cart
            WC()->cart->empty_cart();

            return [
                'result' => 'success',
                'redirect' => $response['url']
            ];
        } else {
            wc_add_notice(  $response['message'], 'dht-momo' );

            return [
                'result' => 'error',
            ];
        }

    }

    public function update_order_status($order_id)
    {

        $data = $_REQUEST;

        $order = wc_get_order($order_id);
        $paymentMethod = $order->get_payment_method('edit');

        if ($paymentMethod != strtolower(DHT_MOMO_GATEWAY_NAME)) {
            return;
        }


        $data[Parameter::RETURN_URL] = $this->get_return_url($order);
        $data[Parameter::NOTIFY_URL] = $this->notify_url;

        $paymentData = [
            'targetEnvironment' => $this->target_environment,
            'accessKey' => $this->access_key,
            'partnerCode' => $this->partner_code,
            'secretKey' => $this->secret_key,
        ];

        $paymentProcessing = new DHT_Momo_Payment_Process($paymentData);

        $result = $paymentProcessing->check_signature($data);

        if ($result) {
            $message = __('Payment failed via MOMO', 'dht-momo');
            if ($data[Parameter::ERROR_CODE] == 0 || $data[Parameter::ERROR_CODE] == '0') {
                $order->payment_complete($data[Parameter::TRANS_ID]);
                $order->update_status('completed', sprintf(__('Payment Success via MOMO, transaction ID %s ', 'dht-momo'), $data[Parameter::TRANS_ID]));
                printf(__('<p class="woocommerce-notice woocommerce-notice--success">Payment Success via MOMO, transaction ID <strong>%s</strong></p>', 'dht-momo'), $data[Parameter::TRANS_ID]);
                return ;
            } elseif(array_key_exists($data[Parameter::ERROR_CODE], DHT_MOMO_ERROR_RESPONSE)) {
                $message = DHT_MOMO_ERROR_RESPONSE[$data[Parameter::ERROR_CODE]];
            }
            $logger = wc_get_logger();
            $logger->error(
                sprintf(
                    'Error updating status for order #%d, transaction: %s, error code %s',
                    $order_id,
                    $data[Parameter::TRANS_ID],
                    $data[Parameter::ERROR_CODE]
                ),
                array(
                    'order' => $order
                )
            );
            printf(__('<p class="woocommerce-notice woocommerce-notice--error">%s Error code<strong>%s</strong></p>', 'dht-momo'), $message, $data[Parameter::ERROR_CODE]);
            $order->update_status('failed', $message);
        } else {
            _e('<p class="woocommerce-notice woocommerce-notice--error">Payment failed. Can not verify your request</p>', 'dht-momo');

            return ;
        }
    }

}