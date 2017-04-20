<?php

final class LifeCell
{

    private static $instance;
    private $appKey = 'E6j_$4UnR_)0b';
    private $superPassword;
    private $token = null;
    private $subId = null;

    public $msisdn;
    public $xml;
    public $json;
    public $error = false;
    public $error_desc = '';

    private $RESPONSE_CODE = [
        '0' => 'Success',
        '-1' => 'Session timeout',
        '-2' => 'Internal Error',
        '-3' => 'Invalid parameter list',
        '-4' => 'Authorization failed',
        '-5' => 'Token expired',
        '-6' => 'Autorization failed (wrong link)',
        '-7' => 'Wrong Superpassword',
        '-8' => 'Wrong number',
        '-9' => 'Only for prepaid customers',
        '-10' => 'Superpassword locked. Order new superpassword',
        '-11' => 'Number doesnt exists',
        '-12' => 'Session expired',
        '-13' => 'Tariff plan changing error.',
        '-14' => 'Service activating error',
        '-15' => 'Order activation error',
        '-16' => 'Failed to get the list of tariffs',
        '-17' => 'Failed to get the list of services',
        '-18' => 'Remove service from preprocessing failed',
        '-19' => 'Logic is blocked',
        '-20' => 'Too many requests',
        '-40' => 'Payments of expenses missed',
        '-21474833648' => 'Internal application error',
    ];

    /**
     * Returns singleton
     *
     * @return self
     */
    public static function getInstance(int $msisdn, int $superPassword)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($msisdn, $superPassword);
        }
        return self::$instance;
    }

    private function __construct(int $msisdn, int $superPassword)
    {
        $this->msisdn = $msisdn;
        $this->superPassword = $superPassword;
        $this->signIn();
    }

    private function __clone()
    {
    }

    private function __sleep()
    {
    }

    private function __wakeup()
    {
    }

    private function signIn()
    {
        $this->request('signIn', ["superPassword" => $this->superPassword]);
    }

    public function signOut()
    {
        $this->request("signOut", ['subId' => $this->subId]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    private function request(string $method, array $params = [])
    {
        $constant_param = [
            'accessKeyCode' => '7',
            'msisdn' => $this->msisdn,
        ];

        $params = array_merge($constant_param, $params);

        if ($this->token) {
            $afte_singIn_param = [
                'languageId' => 'ru',
                'osType' => 'ANDROID',
                'token' => $this->token,
            ];
            $params = array_merge($afte_singIn_param, $params);
        }

        $built_query = urldecode(http_build_query($params));

        $params_in_url = $method . "?" . $built_query . "&signature=";

        $signed_url = hash_hmac('sha1', $params_in_url, $this->appKey);

        $url = "https://api.life.com.ua/mobile/" . $params_in_url . $signed_url;

        $this->xml = simplexml_load_string(file_get_contents($url));

        $this->json = json_decode(json_encode($this->xml), true);

        switch ($this->json['responseCode']) {
            case '0':
                continue;
                break;
            case '-1':
            case '-5':
                $this->signIn();
                $this->request($method, $params);
            default:
                $this->error = true;
                $this->error_desc = $this->RESPONSE_CODE[$this->json['responseCode']];
                break;
        }

        if (is_null($this->token)) {
            $this->token = $this->json['token'];
            $this->subId = $this->json['subId'];
        }
    }

    public function getSummaryData()
    {
        $this->request("getSummaryData");
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function getBalances()
    {
        $this->request("getBalances");
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function callMeBack(int $msisdnB)
    {
        $this->request("callMeBack", ['msisdnB' => $msisdnB]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function requestBalanceTransfer(int $msisdnB)
    {
        $this->request("requestBalanceTransfer", ['msisdnB' => $msisdnB]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function changeLanguage($newLanguageId)
    {
        $this->request("changeLanguage", ['newLanguageId' => $newLanguageId]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }


    public function changeSuperPassword($new_password)
    {
        $this->request("changeSuperPassword", ['oldPassword' => $this->superPassword, 'newPassword' => $new_password]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function getAvailableTariffs()
    {
        $this->request("getAvailableTariffs");
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function getExpensesSummary($period)
    {
        $this->request("getExpensesSummary", ['monthPeriod' => $period]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function getLanguages()
    {
        $this->request("getLanguages");
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function getPaymentsHistory($period)
    {
        $this->request("getPaymentsHistory", ['monthPeriod' => $period]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function getServices()
    {
        $this->request("getServices");
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function getToken()
    {
        $this->request("getToken");
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function getUIProperties($last_date_update)
    {
        $this->request("getUIProperties", ['lastDateUpdate' => $last_date_update]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function offerAction($offerCode)
    {
        $this->request("offerAction", ['offerCode' => $offerCode]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function refillBalanceByScratchCard($secretCode)
    {
        $this->request("refillBalanceByScratchCard", ['secretCode' => $secretCode]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }

    public function removeFromPreProcessing($serviceCode)
    {
        $this->request("removeFromPreProcessing", ['serviceCode' => $serviceCode]);
        return ($this->error) ? $this->error_desc : $this->xml;
    }


}
