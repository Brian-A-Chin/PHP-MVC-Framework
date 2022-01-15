<?php

    class Filters {
        public static function CleanArray($array): array {
            $array = array_unique($array);
            $array = array_filter($array);
            return $array;
        }

        #[Pure] public function IsHTML($string): bool{
            if($string != strip_tags($string)){
                return true;
            }else{
                return false;
            }
        }

        public function FileFilter($object): string {
            $ext = PATHINFO($object, PATHINFO_EXTENSION);
            $object = substr($object, 0, strrpos($object, "."));
            $object = preg_replace('/[^a-zA-Z0-9\s+]/', '', $object);
            $object = str_replace(' ', '_', $object);
            $object = preg_replace('/\s+/', '_', $object);
            return $object.'.'.$ext;
        }

        #[Pure] public function Post_clean($object):string | int{
            $object = filter_var($object, FILTER_SANITIZE_STRING);
            $object = filter_var($object, FILTER_SANITIZE_SPECIAL_CHARS);
            return $object;
        }

        #[Pure] public function Clean($object):string | int{

            $object = filter_var($object, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            return $object;
        }

        public static function AlphaFilter($object): string{
            return preg_replace("/[^A-Za-z]/","",$object);
        }

        public static function NumberFilter($object): string{
            return preg_replace('/\D/', '', $object);
        }

        public static function AlphaNumericFilter($object) : string{
            return preg_replace('/[^a-zA-Z0-9]/', '', $object);
        }

        public static function SearchFiler($object) : string{
            return preg_replace('/[^a-zA-Z0-9@_. ]/', '', $object);
        }

        public static function SortFilter($object) : string{
            return preg_replace('/[^a-zA-Z: ]/', '', $object);
        }

        public static function DateFilter($object) : string{
            return preg_replace('/[^0-9-]/', '', $object);
        }


        public function ScriptFilter( $string ): array|string|null {
            return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        }

        #[Pure] public static function ValidateEmail($email) : bool{
            $email= trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)){
                return true;
            }else{
                return false;
            }
        }
    }