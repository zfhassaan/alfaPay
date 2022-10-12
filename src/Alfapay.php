<?php

namespace zfhassaan\Alfapay;

class Alfapay
{
    protected $apiUrl;
    public $channel_id;
    public $merchant_id;
    public $api_mode;
    public $store_id;
    public $return_url;
    public $merchant_username;
    public $merchant_password;
    public $merchant_hash;

    protected $transactionReferenceNumber;
    protected $transactionType;
    protected $currency;
    protected $amount;
    protected $mobileNumber;
    protected $accountNumber;
    protected $countryCode;
    protected $email;
    protected $SMSOTAC;
    protected $EmailOTAC;
    protected $SMSOTP;

    public $cipher;
    public $isRedirectionRequest;
    public $isBTN;

    public $key_1;
    public $key_2;

    protected $hashRequest;
    protected $auth_token;
    protected $hashKey;
    public $handshake;

    private $_doTranUrl;
    private $_proTranUrl;

    /**
     * Constructor for AlfaPay
     * @return void
     */
    public function __construct()
    {
        $this->initConfig();
    }

    /**
     * Initialize Config Values.
     * @return void
     */
    public function initConfig()
    {
        $this->setApiUrl(config('alfapay.api_url'));
        $this->channel_id = config('alfapay.channel_id');
        $this->merchant_id = config('alfapay.merchant_id');
        $this->api_mode = config('alfapay.mode');
        $this->store_id = config('alfapay.store_id');
        $this->return_url = config('alfapay.return_url');
        $this->merchant_username = config('alfapay.merchant_username');
        $this->merchant_password = config('alfapay.merchant_password');
        $this->merchant_hash = config('alfapay.merchant_hash');
        //
        $this->setTransactionType(3); // 1: Alfa Wallet, 2: Alfalah Bank Account, 3: Credit Card
        $this->cipher = "aes-128-cbc";
        $this->isRedirectionRequest = 0;
        $this->isBIN = 0;
        //
        $this->key_1 = config('alfapay.key_1');
        $this->key_2 = config('alfapay.key_2');

        //Sandbox or Production Mode
        if($this->api_mode == 'sandbox'){
            $this->_doTranUrl = 'https://sandbox.bankalfalah.com/HS/api/Tran/DoTran';
            $this->_proTranUrl = 'https://sandbox.bankalfalah.com/HS/api/ProcessTran/ProTran';
        } else {
            $this->_doTranUrl = 'https://payments.bankalfalah.com/HS/api/Tran/DoTran';
            $this->_proTranUrl = 'https://payments.bankalfalah.com/HS/api/ProcessTran/ProTran';
        }
    }

    /**
     * Get Token from API
     * @return hash key
     */
    public function getToken() {
        $mapString =
            "HS_ChannelId={$this->channel_id}"
            . "&HS_IsRedirectionRequest={$this->isRedirectionRequest}"
            . "&HS_MerchantId={$this->merchant_id}"
            . "&HS_StoreId={$this->store_id}"
            . "&HS_ReturnURL={$this->return_url}"
            . "&HS_MerchantHash={$this->merchant_hash}"
            . "&HS_MerchantUsername={$this->merchant_username}"
            . "&HS_MerchantPassword={$this->merchant_password}"
            . "&HS_TransactionReferenceNumber={$this->getTransactionReferenceNumber()}";

        $hashRequest = $this->_generateHashRequest($mapString);
        $this->setHashRequest($hashRequest);
        $this->_sendRequest();
        return $this->handshake;
    }

    /**
     * Generate Hash/Token of Request
     * @param string $mapString
     * @return hash key
     */
    private function _generateHashRequest(string $mapString) {
        $cipher_text = openssl_encrypt($mapString,$this->cipher,$this->key_1,OPENSSL_RAW_DATA,$this->key_2);
        $hashRequest = base64_encode($cipher_text);
        return $hashRequest;
    }

    /**
     * Send Request to API
     * @return void
     */
    private function _sendRequest() {
        // The data you want to send via POST
        $fields = [
            "HS_ChannelId"=>$this->channel_id,
            "HS_IsRedirectionRequest"=>$this->isRedirectionRequest,
            //"HS_IsBIN"=>$this->isBIN,
            "HS_MerchantId"=> $this->merchant_id,
            "HS_StoreId"=> $this->store_id,
            "HS_ReturnURL"=> $this->return_url,
            "HS_MerchantHash"=> $this->merchant_hash,
            "HS_MerchantUsername"=> $this->merchant_username,
            "HS_MerchantPassword"=> $this->merchant_password,
            "HS_TransactionReferenceNumber"=> $this->getTransactionReferenceNumber(),
            "HS_RequestHash"=> $this->getHashRequest()
        ];

        $fields_string = http_build_query($fields);
        // Open Connection
        $ch = curl_init();
        // set the url, number of POST Vars, POST data
        curl_setopt($ch,CURLOPT_URL,$this->getApiUrl());
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
        // So that curl_exec returns the contents of the curl; rather than echoing it.
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        //execute post
        $result = curl_exec($ch);
        $this->handshake = json_decode(json_decode($result));
        if($this->handshake != null && $this->handshake->success == 'true') {
            $AuthToken = $this->handshake->AuthToken;
            $this->setAuthToken($AuthToken);
        } else {
            $this->setAuthToken(false);
        }
    }

    /**
     * Set/Prepare Transaction Request Values
     * @return string
     */
    public function sendTransactionRequest(){
        $mapString =
            "ChannelId={$this->channel_id}"
            . "&MerchantId={$this->merchant_id}"
            . "&StoreId={$this->store_id}"
            . "&ReturnURL={$this->return_url}"
            . "&MerchantHash={$this->merchant_hash}"
            . "&MerchantUsername={$this->merchant_username}"
            . "&MerchantPassword={$this->merchant_password}"
            . "&TransactionReferenceNumber={$this->getTransactionReferenceNumber()}"
            . "&AuthToken={$this->getAuthToken()}"
            . "&TransactionTypeId={$this->getTransactionType()}"
            . "&Currency={$this->getCurrency()}"
            . "&TransactionAmount={$this->getAmount()}"
            . "&MobileNumber={$this->getMobileNumber()}"
            . "&AccountNumber={$this->getAccountNumber()}"
            . "&Country={$this->getCountryCode()}"
            . "&EmailAddress={$this->getEmail()}";

        $hashRequest = $this->_generateHashRequest($mapString);
        //
        $this->setHashRequest($hashRequest);
        //
        return $this->_sendTransactionRequest();
    }

    /**
     * Send POST Request to Transaction API
     * @return string
     */
    private function _sendTransactionRequest() {
        //The data you want to send via POST
        $fields = [
            "ChannelId"=>$this->channel_id,
            "MerchantId"=> $this->merchant_id,
            "StoreId"=> $this->store_id,
            "MerchantHash"=> $this->merchant_hash,
            "MerchantUsername"=> $this->merchant_username,
            "MerchantPassword"=> $this->merchant_password,
            "ReturnURL"=> $this->return_url,
            "Currency"=> $this->getCurrency(),
            "AuthToken"=> $this->getAuthToken(),
            "TransactionTypeId"=> $this->getTransactionType(),
            "TransactionReferenceNumber"=> $this->getTransactionReferenceNumber(),
            "TransactionAmount"=> $this->getAmount(),
            "MobileNumber"=> $this->getMobileNumber(),
            "AccountNumber"=> $this->getAccountNumber(),
            "Country"=> $this->getCountryCode(),
            "EmailAddress"=> $this->getEmail(),
            "RequestHash"=> $this->getHashRequest()
        ];

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $this->_doTranUrl);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        //execute post
        $result = curl_exec($ch);

        $response =  json_decode(json_decode($result));
        //
        return $response;
    }

    /**
     * Set/Prepare Process Transaction values
     * @param string $transactionReferenceNumber
     * @return void
     */
    public function processTransaction() {
        //
        $mapString =
            "ChannelId={$this->channel_id}"
            . "&MerchantId={$this->merchant_id}"
            . "&StoreId={$this->store_id}"
            . "&MerchantHash={$this->merchant_hash}"
            . "&MerchantUsername={$this->merchant_username}"
            . "&MerchantPassword={$this->merchant_password}"
            . "&ReturnURL={$this->return_url}"
            . "&Currency={$this->getCurrency()}"
            . "&AuthToken={$this->getAuthToken()}"
            . "&TransactionType={$this->getTransactionType()}"
            . "&TransactionReferenceNumber={$this->getTransactionReferenceNumber()}"
            . "&SMSOTAC={$this->getSMSOTAC()}"
            . "&EmailOTAC={$this->getEmailOTAC()}"
            . "&SMSOTP={$this->getSMSOTP()}"
            . "&HashKey={$this->getHashKey()}"
            . "&IsOTP=true";
        //
        $hashRequest = $this->_generateHashRequest($mapString);
        //
        $this->setHashRequest($hashRequest);
        //
        $response = $this->_sendProcessTransaction();
        //
        return $response;
    }

    /**
     * Send POST Request to Process Transaction API
     * @return string
     */
    private function _sendProcessTransaction() {
        //The data you want to send via POST
        $fields = [
            "ChannelId"=>$this->channel_id,
            "MerchantId"=> $this->merchant_id,
            "StoreId"=> $this->store_id,
            "MerchantHash"=> $this->merchant_hash,
            "MerchantUsername"=> $this->merchant_username,
            "MerchantPassword"=> $this->merchant_password,
            "ReturnURL"=> $this->return_url,
            "Currency"=> $this->getCurrency(),
            "AuthToken"=> $this->getAuthToken(),
            "TransactionTypeId"=> $this->getTransactionType(),
            "TransactionReferenceNumber"=> $this->getTransactionReferenceNumber(),
            "SMSOTAC"=> $this->getSMSOTAC(),
            "EmailOTAC"=> $this->getEmailOTAC(),
            "SMSOTP"=> $this->getSMSOTP(),
            "HashKey"=> $this->getHashKey(),
            "RequestHash"=> $this->getHashRequest(),
            "IsOTP"=> "true"
        ];

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $this->_proTranUrl);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        //execute post
        $result = curl_exec($ch);

        $response =  json_decode(json_decode($result));
        //
        return $response;
    }

    /**
     * Get the value of apiUrl
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Set the value of apiUrl
     *
     * @return  self
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * Get the value of transactionReferenceNumber
     */
    public function getTransactionReferenceNumber()
    {
        return $this->transactionReferenceNumber;
    }

    /**
     * Set the value of transactionReferenceNumber
     *
     * @return  self
     */
    public function setTransactionReferenceNumber($transactionReferenceNumber)
    {
        $this->transactionReferenceNumber = $transactionReferenceNumber;

        return $this;
    }

    /**
     * Get the value of hashRequest
     */
    public function getHashRequest()
    {
        return $this->hashRequest;
    }

    /**
     * Set the value of hasRequest
     *
     * @return  self
     */
    public function setHashRequest($hashRequest)
    {
        $this->hashRequest = $hashRequest;

        return $this;
    }

    /**
     * Get the value of auth_token
     */
    public function getAuthToken()
    {
        return $this->auth_token;
    }

    /**
     * Set the value of auth_token
     *
     * @return  self
     */
    public function setAuthToken($auth_token)
    {
        $this->auth_token = $auth_token;

        return $this;
    }

    /**
     * Get the value of transactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Set the value of transactionType
     * @param 1: Alfa Wallet, 2: Alfalah Bank Account, 3: Credit Card
     * @return  self
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * Get the value of currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set the value of currency
     *
     * @return  self
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get the value of amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     *
     * @return  self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of mobileNumber
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * Set the value of mobileNumber
     *
     * @return  self
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;

        return $this;
    }

    /**
     * Get the value of accountNumber
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Set the value of accountNumber
     *
     * @return  self
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * Get the value of countryCode
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set the value of countryCode
     *
     * @return  self
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of SMSOTAC
     */
    public function getSMSOTAC()
    {
        return $this->SMSOTAC;
    }

    /**
     * Set the value of SMSOTAC
     *
     * @return  self
     */
    public function setSMSOTAC($SMSOTAC)
    {
        $this->SMSOTAC = $SMSOTAC;

        return $this;
    }

    /**
     * Get the value of EmailOTAC
     */
    public function getEmailOTAC()
    {
        return $this->EmailOTAC;
    }

    /**
     * Set the value of EmailOTAC
     *
     * @return  self
     */
    public function setEmailOTAC($EmailOTAC)
    {
        $this->EmailOTAC = $EmailOTAC;

        return $this;
    }

    /**
     * Get the value of SMSOTP
     */
    public function getSMSOTP()
    {
        return $this->SMSOTP;
    }

    /**
     * Set the value of SMSOTP
     *
     * @return  self
     */
    public function setSMSOTP($SMSOTP)
    {
        $this->SMSOTP = $SMSOTP;

        return $this;
    }

    /**
     * Get the value of hashKey
     */
    public function getHashKey()
    {
        return $this->hashKey;
    }

    /**
     * Set the value of hashKey
     *
     * @return  self
     */
    public function setHashKey($hashKey)
    {
        $this->hashKey = $hashKey;

        return $this;
    }
}
