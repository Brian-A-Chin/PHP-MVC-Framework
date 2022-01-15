<?php
if ( basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]) ) { 
    header("location:../../404");
    exit;
}

define( 'ABSPATH', realpath($_SERVER["DOCUMENT_ROOT"]) . '/' );

define( 'COUNTRY_CODE', 'US');

define( 'DB_NAME', 'tss-211' );

define( 'DB_USER', 'root' );

define( 'DB_PASSWORD', '' );

define( 'DB_HOST', 'localhost' );

define( 'DB_CHARSET', 'utf8mb4' );

define( 'DB_COLLATE', '' );

define( 'SALT','W48VhWERWER48344R35WEF4R4ETGoeF3nTjgGQFx' );

define( 'SECRET_KEY','ERIBFIHF8394UIF83VYFUOUBWFIFB3F' );

define( 'BusinessName', 'SmartieGroup' );




?>