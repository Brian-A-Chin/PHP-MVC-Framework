<?php

    class Utilities {

        public static function GetCurrentUrl() : array{
            $url = [];
            if(isset($_GET['url'])){
                $url = rtrim($_GET['url'], '/');
                $url = filter_var($url, FILTER_SANITIZE_URL);
                $url = explode('/', $url);
            }
            return $url;
        }

        public static function ModifyCurrentUrl($Index,$value) : void{
            $url = self::GetCurrentUrl();
            if(Count($url) >= $Index){
                $url[$Index] = $value;
                $_GET['url'] = implode("/",$url);
            }
        }

        public static function ModifyGET() : void{
            foreach($_GET as $key => $value){
                $new_val = rawurldecode($value);
                $_GET[$key] = preg_replace('/\s+/', '+', $new_val);
            }
        }

        public static function GetCurrentController() : string{
            $url = self::GetCurrentUrl();
            if(Count($url) == 0)
                return 'Error';
            return sprintf('%sController',ucwords(self::GetCurrentUrl()[0]));
        }

        public static function GetCurrentMethod() : string{
            $url = self::GetCurrentUrl();
            if(Count($url) < 2)
                return 'Error';
            return ucwords(self::GetCurrentUrl()[1]);
        }



    }