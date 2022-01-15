<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class Render extends FilesystemLoader {
    private FilesystemLoader $Loader;
    private Environment $Twig;
    public function __construct($path){
        $this->Loader = new FilesystemLoader($path);
        $this->Twig = new Environment($this->Loader);
    }

    public function GetTemplate($type, $parameters = false ): string
    {

        try {
            if(!$parameters){
                return $this->Twig->render($type);
            }else{
                return $this->Twig->render($type,$parameters);
            }
        } catch (LoaderError | SyntaxError | RuntimeError $e) {
            BaseClass::LogError([
                'Message' => 'Twig failed to render template',
                'Exception' => $e
            ]);
            return false;
        }
    }



}