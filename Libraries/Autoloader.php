<?php

    class Autoloader {

        private string $Path;
        private array $Prerequisites;
        private string $ABS;
        public function __construct($ABS,$Path) {
            $this->ABS = $ABS;

            $this->Prerequisites = [
                'Render'=>[
                    'Libraries/3rdParty/twig/vendor/autoload.php',
                ]
            ];

            $this->Path = $this->ABS.$Path;
            spl_autoload_register( array($this, 'load') );
        }

        function load( $file ) {

            if (is_file($this->Path . '/' . $file . '.php')) {

                if(array_key_exists($file,$this->Prerequisites)){
                    foreach($this->Prerequisites[$file] as $pre_file ){
                        require_once( $this->ABS . $pre_file );
                    }
                }

                require_once( $this->Path . '/' . $file . '.php' );
            }
        }

    }