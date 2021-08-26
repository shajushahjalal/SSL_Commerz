<?php

namespace App\Classes\Packages; // Update the Name Space

use Illuminate\Support\Facades\Route;

class SSLCommerz{
    /**
     * Define Mandatory properties
     */
    protected $live_url = "https://securepay.sslcommerz.com/";
    protected $sandbox_url = "https://sandbox.sslcommerz.com/";
    protected $live_store_id = "";
    protected $live_store_passwd = "";
    protected $sandbox_store_id = "datas5ffd2eab8303d";
    protected $sandbox_store_passwd = "datas5ffd2eab8303d@ssl";
    protected $success_url = "";
    protected $failed_url = "";
    protected $cancel_url = "";
    protected $environment = "";
    protected $intent_url;
    protected $data_field = [];

    function __construct($intent_url, array $data_field, $environment = "sandbox")
    {
        $this->intent_url = $intent_url;
        $this->environment = $environment;
        $this->success_url  = route('ssl-payment-success');
        $this->failed_url   = route('ssl-payment-failed');
        $this->cancel_url   = route('ssl-payment-cancel');
        $this->ipn_url      = Route::has('ssl-payment-ipn') ? route('ssl-payment-ipn') : '';
        $this->bindStoreInformation($data_field);
    }

    /**
     * Bind Store Info with post data field
     */
    protected function bindStoreInformation(array $data_field){
        if($this->environment == "sandbox"){
            $this->data_field['store_id'] = $this->sandbox_store_id;
            $this->data_field['store_passwd'] = $this->sandbox_store_passwd;
            $this->intent_url = trim($this->sandbox_url, '/') . $this->intent_url;
        }else{
            $this->data_field['store_id'] = $this->live_store_id;
            $this->data_field['store_passwd'] = $this->live_store_passwd;
            $this->intent_url = trim($this->live_url, '/') . $this->intent_url;
        }        
        foreach($data_field as $key => $value){
            $this->data_field[$key] = $value;
        }
    }

    /**
     * Bind Urls
     * This Method is Requitrd for Web Payment
     */
    protected function bindStoreUrls(){
        $this->data_field['success_url']  = $this->success_url;
        $this->data_field['fail_url']     = $this->failed_url;
        $this->data_field['cancel_url']   = $this->cancel_url;
        if(!empty($this->ipn_url) ){
            $this->data_field['ipn_url']   = $this->ipn_url;
        }
    }

    /**
     * Execute Curl & After Execute Curl 
     * Return SSL Response
     */
    public function getResponse($live = true){
        $this->bindStoreUrls();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->intent_url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, true );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data_field);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $live ? TRUE : FALSE); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $live ? TRUE : FALSE); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC
        
        $content = curl_exec($curl );
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($code == 200 && !( curl_errno($curl))) {
            curl_close( $curl);
            return $content;
        }else {
            $response = ['status' => false , 'message' => "FAILED TO CONNECT WITH SSL-COMMERZ API"];
            curl_close( $curl);
            return json_encode($response);
        }
    }


    public function getPaymentData($live = true){
        $this->intent_url .= "?";
        foreach($this->data_field as $key => $value){
            $this->intent_url .= $key . '=' . $value . '&';
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->intent_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $live ? TRUE : FALSE); # IF YOU RUN FROM LOCAL PC
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $live ? TRUE : FALSE); # IF YOU RUN FROM LOCAL PC

        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($code == 200 && !( curl_errno($curl))) {
            curl_close( $curl);
            return $result;
        }else {
            $response = ['status' => false , 'message' => "FAILED TO CONNECT WITH SSL-COMMERZ API"];
            curl_close( $curl);
            return json_encode($response);
        }    
    }

    
}