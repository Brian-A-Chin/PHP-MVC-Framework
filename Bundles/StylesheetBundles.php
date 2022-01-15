<?php


class StylesheetBundles{
    public static function GetStyles( $types ){
        $stylesToRender = [];
        $styles = [
            'Modals' => [
                '<link href="/Stylesheets/Modals.css" rel="stylesheet" type="text/css" />'
            ],
            'Grids' => [
                '<link href="/Stylesheets/Grids.css" rel="stylesheet" type="text/css" />'
            ],
            'Main' => [
                '<link href="/Stylesheets/bootstrap.min.css" rel="stylesheet" type="text/css" />',
                '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/Site.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/toastr.min.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/JqueryUi.css" rel="stylesheet" type="text/css" />'
            ],
            'Login' => [
                '<link href="/Stylesheets/3rdParty/Icons/Iconmoon/fonts.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/login.css" rel="stylesheet" type="text/css" />'
            ],
            'Account' => [
                '<link href="/plugins/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css" />',
                '<link href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/3rdParty/themes/adminlte/adminlte.css" rel="stylesheet" type="text/css" />'
            ],
            'Admin' => [],
            'Individual' => [
                '<link href="/Stylesheets/FileManager.css" rel="stylesheet" type="text/css" />'
            ],
            'AccountSetup' => [
                '<link href="/Stylesheets/3rdParty/themes/adminlte/adminlte.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/bootstrap.min.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/AccountSetup.css" rel="stylesheet" type="text/css" />'
            ],
            'Pagination' => [
                '<link href="/Stylesheets/3rdParty/Plugins/DragTable/dragtable.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/3rdParty/Plugins/DateRange/daterangepicker.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/Pagination.css" rel="stylesheet" type="text/css" />'
            ],
            'NFT' => [
                '<link href="/Stylesheets/Frontend/NFT/main.css" rel="stylesheet" type="text/css" />'
            ]

        ];
        if(is_array($types)){
            foreach ($types as $type){
                if(array_key_exists($type,$styles)){
                    $stylesToRender[] = implode("\n",$styles[$type]);
                }
            }
        }

        echo implode("\n",$stylesToRender);
    }

    public static function GetSpecificStyles($Method){

        $styles = [

            'TemplateManager' => [
                '<link href="/Stylesheets/TemplateManager.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/3rdParty/Quill/quill.core.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/3rdParty/Quill/quill.snow.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/3rdParty/Quill/highlight.min.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/3rdParty/Quill/code-highlight.css" rel="stylesheet" type="text/css" />'
            ],
            'PageEditor' => [
                '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"/>',
                '<link href="/Stylesheets/3rdParty/grapesjs/css/grapes.min.css" rel="stylesheet" type="text/css" />',
                '<link href="/Stylesheets/3rdParty/grapesjs/css/Overrides.css" rel="stylesheet" type="text/css" />'

            ]

        ];


        if(array_key_exists($Method,$styles)){
            echo implode("\n",$styles[$Method]);
        }
    }
}

