<?php


class JavaScriptBundles{


    public static function GetScripts( $types ){
        $scriptsToRender = [];
        $scripts = [
            'Main' => [
                '<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/Toastr/toastr.min.js"></script>',
                '<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/Main/Core.js"></script>',
                '<script type="text/javascript" src="/Scripts/Main/CoreEventFunctions.js"></script>',
                '<script type="text/javascript" src="/Scripts/Modals/Modals.js"></script>'
            ],
            'Authentication' => [
                '<script type="text/javascript" src="/Scripts/Authentication/Authentication.js"></script>'
            ],
            'Account' => [
                '<script type="text/javascript" src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>',
                '<script type="text/javascript" src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/adminlte/adminlte.js"></script>',
                '<script type="text/javascript" src="/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>',
                '<script type="text/javascript" src="/plugins/raphael/raphael.min.js"></script>',
                '<script type="text/javascript" src="/plugins/jquery-mapael/jquery.mapael.min.js"></script>',
                '<script type="text/javascript" src="/plugins/jquery-mapael/maps/usa_states.min.js"></script>',
                '<script type="text/javascript" src="/plugins/chart.js/Chart.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/Dashboard.js"></script>'
            ],
            'AccountSetup' => [
                '<script type="text/javascript" src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>',
                '<script type="text/javascript" src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/adminlte/adminlte.js"></script>',
                '<script type="text/javascript" src="/Scripts/Authentication/AccountSetup.js"></script>'
            ],
            'Pagination' => [
                '<script type="text/javascript" src="/Scripts/3rdParty/Moment/moment-with-locales.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/DateRange/daterangepicker.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/DragTable/jquery.dragtable.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/ExportTable/es6-promise.auto.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/ExportTable/FileSaver.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/ExportTable/html2canvas.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/ExportTable/jspdf.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/ExportTable/jspdf.plugin.autotable.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/ExportTable/xlsx.core.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/ExportTable/pdfmake.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/ExportTable/tableExport.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/Pagination/Pagination.js"></script>'
            ],

            'NFT' => [
                '<script type="text/javascript" src="/Scripts/Frontend/NFT/main.js"></script>'
            ]
        ];
        if(is_array($types)){
            foreach ($types as $type){
                if(array_key_exists($type,$scripts)){
                    $scriptsToRender[] = implode("\n",$scripts[$type]);
                }
            }
        }
        echo implode("\n",$scriptsToRender);
    }

    public static function GetSpecificScripts($Method){

        $scripts = [
            'explorer' => [
                '<script type="text/javascript" src="/Scripts/Individual.js"></script>'
            ],
            'admincontroller' => [
                '<script type="text/javascript" src="/Scripts/Admin.js"></script>'
            ],
            'individualcontroller' => [
                '<script type="text/javascript" src="/Scripts/Main/FileManager.js"></script>',
                '<script type="text/javascript" src="/Scripts/Individual.js"></script>'
            ],
            'managecontroller' => [
                '<script type="text/javascript" src="/Scripts/Manage.js"></script>'
            ],
            'templatemanager' => [
                '<script type="text/javascript" src="/Scripts/3rdParty/Quill/highlight.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/Quill/code-highlight.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/Quill/quill.core.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/Quill/quill.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/TemplateManager/Main.js"></script>'
            ],
            'pageeditor' => [
                '<script type="text/javascript" src="/Scripts/3rdParty/grapesjs/dist/grapes.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/grapesjs/dist/grapesjs-blocks-basic.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/grapesjs/dist/grapesjs-plugin-forms.min.js"></script>',
                '<script type="text/javascript" src="/Scripts/3rdParty/grapesjs/custom.js"></script>'
            ]
        ];

        $Method = strtolower($Method);
        if(array_key_exists($Method,$scripts)){
            echo implode("\n",$scripts[$Method]);
        }
    }

}