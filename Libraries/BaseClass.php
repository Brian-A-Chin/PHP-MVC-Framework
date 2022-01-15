<?php

    use JetBrains\PhpStorm\Pure;

    class BaseClass{
    

    public static function Crypto( $string, $action = 'e' ): string {

        $key = substr(hash('sha256', SECRET_KEY), 0, 32);
        $iv = substr(hash('sha256', SALT), 0, 16);
        $padwith = '`';
        $blocksize = 32;
        $method = "AES-256-CFB";
        if($action === 'e'){
            $padded_secret = $string . str_repeat($padwith, ($blocksize - strlen($string) % $blocksize));
            $encrypted_string = openssl_encrypt($padded_secret, $method, $key, OPENSSL_RAW_DATA, $iv);
            $encrypted_secret = base64_encode($encrypted_string);
            return $encrypted_secret;
        }else{
            $decoded_secret = base64_decode($string);
            $decrypted_secret = openssl_decrypt($decoded_secret, $method, $key, OPENSSL_RAW_DATA, $iv);
            return rtrim($decrypted_secret, $padwith);
        }
    }
    
    public function Create_token(): string {
        
        $date = new DateTime();
        $unix_time_stamp = $date->getTimestamp();
        return $this->crypto(ceil(($unix_time_stamp * 1996) / 24));
        
    }
    
    public function Validate_token($token): bool {
        //BASED ON A 7 SEC TTL 
        $date = new DateTime();
        $current_time = $date->getTimestamp();
        $tokenTime = $this->crypto($token,'d');
        return (ceil(($current_time * 1996) / 24) - 4 <= $tokenTime) && ($tokenTime <= (ceil(($current_time * 1996) / 24)) + 4); 
    }
    
    
    public static function IP():string | int{
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else{
            return $_SERVER['REMOTE_ADDR'];
        }
    }




    
    #[Pure] public static function Generate_int_code($length) :int{
        if($length == 1){
            return rand(0,9);
        }else{
          $int = '';
            for($i = 0;$i < $length; $i++){
                $int .= 9;
            } 
            return rand($int,$int.'9');
        }
    }




    #[Pure] public static function GenerateAlphaNumericCode($length): string{
	   $str = "";
	   $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
	   $max = count($characters) - 1;
	   for ($i = 0; $i < $length; $i++) {
		  $rand = mt_rand(0, $max);
		  $str .= $characters[$rand];
	   }
	   return $str;
        
    }

    public static function LogError($data) : void{
        $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `ErrorLogs` (`Message`,`Exception`,`IP`) VALUES (:Message,:Exception,:IP)");
        $smt->execute(array(
            ":IP" =>  BaseClass::IP(),
            ":Message" =>  $data['Message'],
            ":Exception" =>  $data['Exception']
        ));
    }

    public static function SecurityLog($data): void{
        $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `SecurityLogs` (`Subject`,`Message`,`AccountID`,`IP`) VALUES (:Subject,:Message,:AccountID,:IP)");
        $smt->execute(array(
            ":Subject" =>  $data['Subject'],
            ":Message" =>  $data['Message'],
            ":AccountID" =>  array_key_exists('AccountID',$data) ? $data['AccountID'] : null,
            ":IP" =>  BaseClass::IP()
        ));
    }

    public static function GetError( $type ): array {

        $types = [
            "Critical" =>  [
                "Result" => false,
                "Response" => "An unknown error occurred. Please check back again later."
            ]
        ];

        return $types[$type];
    }

    
    
}





