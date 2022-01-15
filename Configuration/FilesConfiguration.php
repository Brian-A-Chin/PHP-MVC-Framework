<?php


    class FilesConfiguration {

        static string $UserDirectory = 'Assets/Users';
        static int $BasePermissions = 0777;
        static string $EscapeExtension = 'txt';

        public static function GetSafeExtensions() : array{
            return array('png','jpg','pptx','pdf');
        }

    }