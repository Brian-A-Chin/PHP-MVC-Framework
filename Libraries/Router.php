<?php
    class Router {

        protected mixed $CurrentController;
        protected string $CurrentMethod;

        public function __construct($CurrentController,$CurrentMethod) {

            $this->CurrentController = $CurrentController;
            $this->CurrentMethod = $CurrentMethod;

        }

        public function Route(){

            if(!file_exists(ABSPATH.'/Controllers/' . ucwords($this->CurrentController). '.php')){
                // If not exists, set as controller
                $Twig = new Render(ABSPATH.'/Views/Error');
                echo $Twig->GetTemplate('Index.twig',['Error'=>'Controller invalid']);
                return;
            }

            // Require the controller
            require_once ABSPATH.'/Controllers/'. $this->CurrentController . '.php';

            // Instantiate controller class
            $this->CurrentController = new $this->CurrentController;

            // Check if method does not exist
            if($this->CurrentMethod == 'Error'){
                Utilities::ModifyCurrentUrl(1,'Index');
                $this->CurrentMethod = 'Index';
            }else if(!method_exists($this->CurrentController, $this->CurrentMethod)) {
                $Twig = new Render(ABSPATH.'/Views/Error');
                echo $Twig->GetTemplate('Index.twig',['Error'=>'Method invalid']);
                return;
            }

            Utilities::ModifyGET();

            call_user_func_array([$this->CurrentController, $this->CurrentMethod], []);

        }



    }