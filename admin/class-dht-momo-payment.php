<?php

use MService\Payment\Shared\SharedModels\Environment;
use MService\Payment\Shared\SharedModels\PartnerInfo;
use MService\Payment\AllInOne\Processors\CaptureMoMo;
use MService\Payment\Shared\Constants\Parameter;
use MService\Payment\Shared\Utils\Encoder;

class DHT_Momo_Payment_Process {

    private $orderId;
    private $env;
    private $requestId;
    private $amount;
    private $orderInfo;
    private $partnerInfo;
    private $returnUrl;
    private $targetEnvironment = 'development';
    private $notifyUrl;
    private $apiEndpoint;
    private $accessKey;
    private $partnerCode;
    private $secretKey;
    private $captureMomo;



    public function __construct($params)
    {

        $vars = get_object_vars($this);

        foreach ($vars as $var_name => $value) {
            if(array_key_exists($var_name, $params)) {
                $this->{$var_name} = (string) $params[$var_name];
            }
        }


        $this->apiEndpoint = $this->targetEnvironment === 'development' ? DHT_MOMO_PAYMENT_ENDPOINT_DEV : DHT_MOMO_PAYMENT_ENDPOINT_PROD;

        if(defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(json_encode(get_object_vars($this), JSON_PRETTY_PRINT));
        }

        $this->generatePartnerInfo();
        $this->generateEnv();

    }

    public function add_shortCode() {

        add_shortcode('dht_momo', [$this, 'generateLinkMomo']);
    }

    public function generateLinkMomo() {
        $this->catureMomo();
        $data = [
            'success' => false
        ];
        if(is_object($this->captureMomo)) {
            $data =  [
                'success'   => true,
                'url'       => $this->captureMomo->getPayUrl()
            ];
        }
        return $data;
    }

    private function generateEnv() {
        $this->env = new Environment($this->apiEndpoint, $this->partnerInfo, $this->targetEnvironment, '', true);
    }

    private function generatePartnerInfo() {
        $this->partnerInfo = new PartnerInfo($this->accessKey, $this->partnerCode, $this->secretKey);
    }

    private function catureMomo() {
        $this->captureMomo = CaptureMoMo::process($this->env, $this->orderId, $this->orderInfo, $this->amount, '', $this->requestId, $this->notifyUrl, $this->returnUrl);
    }

    private function create_signature($data) {
        error_log(json_encode($data, JSON_PRETTY_PRINT));
        $rawHash = "partnerCode=" . $data[Parameter::PARTNER_CODE] .
            "&accessKey=" . $data[Parameter::ACCESS_KEY] .
            "&requestId=" . $data[Parameter::REQUEST_ID] .
            "&amount=" . $data[Parameter::AMOUNT] .
            "&orderId=" . $data[Parameter::ORDER_ID] .
            "&orderInfo=" . $data[Parameter::ORDER_INFO] .
            "&orderType=" . $data[Parameter::ORDER_TYPE] .
            "&transId=" . $data[Parameter::TRANS_ID] .
            "&message=" . $data[Parameter::MESSAGE] .
            "&localMessage=" . $data[Parameter::LOCAL_MESSAGE] .
            "&responseTime=" . $data[Parameter::DATE] .
            "&errorCode=" . $data[Parameter::ERROR_CODE] .
            "&payType=" . $data[Parameter::PAY_TYPE] .
            "&extraData=" . $data[Parameter::EXTRA_DATA];

        return Encoder::hashSha256($rawHash, $this->secretKey);
    }

    public function check_signature($data) {
        return $this->create_signature($data) === $data[Parameter::SIGNATURE];
    }

}
