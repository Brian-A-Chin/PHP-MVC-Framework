<?php
    use Twilio\Rest\Client;
    class SMSControl Extends SMSConfiguration {

        private int $PhoneNumber;
        private string $Message;

        public function __construct($PhoneNumber,$Message){
            $this->PhoneNumber = $PhoneNumber;
            $this->Message = $Message;
        }


        public function SendSMS() : bool{
            try {
                $client = new Client(SMSConfiguration::$ACCOUNT_SID, SMSConfiguration::$AUTH_TOKEN);
                $client->messages->create(
                // Where to send a text message (your cell phone?)
                    $this->PhoneNumber,
                    array(
                        'from' => SMSConfiguration::$SENDER_PHONE_NUMBER,
                        'body' => $this->Message
                    )
                );
                return true;
            }catch (Twilio\Exceptions\TwilioException $e){
                $e->getMessage();
            }
            return false;
        }


    }