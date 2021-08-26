<?php

namespace App\Classes\Packages; // Update the Name Space with your Dir.

use Illuminate\Support\Str;

class SMS {

    protected $intent_url = "https://smsplus.sslwireless.com/api/v3/";
    protected $sid = ""; // Paste SSl Provided SID
    protected $api_token = ""; // Paste Live API Key Here
    protected $sms_id = "";
    protected $post_json_data;
    protected $sms_retriever_code = "";

    /**
     * Required Params 
     * sms => which text you want to send
     * phone_no => Phone number can be single or multiple. 
     * Single format "01760****84"
     * Multiple format ["01760****84", "01521****51"]
     * * @method App\Classes\Packages\SMS __construct(array|string $phone_no)
     */
    function __construct($sms, $phone_no, $intent_url = "/send-sms")
    {
        $this->intent_url = config("sslsms.intent_url");
        $this->sid = config("sslsms.sid");
        $this->api_token = config("sslsms.api_token");

        $this->sms_id = Str::random(10);
        $this->intent_url = trim($this->intent_url, '/') . $intent_url;
        $this->bindData($sms, $phone_no);
    }

    /**
     * Bind Required Data into 
     */
    protected function bindData($sms, $phone_no){
        $sms .= "\nThank you for using GariSeba.\n\n" . $this->sms_retriever_code;
        $arr_data = [
            "api_token" => $this->api_token,
            "sid"       => $this->sid,
            "msisdn"    => $phone_no,
            "sms"       => $sms,
        ];
        if(is_array($phone_no)){
            $this->intent_url = trim($this->intent_url, '/') . '/bulk';
            $arr_data["batch_csms_id"] = $this->sms_id;            
        }else{
            $arr_data["csms_id"] = $this->sms_id;
        }
        $this->post_json_data = json_encode($arr_data);
    }

    /**
     * Sens SMS After Execute this Curl
     */
    public function sendSms(){
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $this->intent_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($this->post_json_data),
            'accept:application/json'
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}