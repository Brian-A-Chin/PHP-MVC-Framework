<?php
    //Load the model and the view
    class Controller {

        //Load the view (checks for the file)
        public function View($templateData = [], $overrideTemplate = false) {
            $template = !$overrideTemplate ? Utilities::GetCurrentMethod() : $overrideTemplate;
            $path = sprintf(ABSPATH ."Views/%s",str_replace('Controller','',Utilities::GetCurrentController()));
            $templateName = sprintf("%s.twig",$template);
            if(file_exists(sprintf('%s/%s',$path,$templateName))){
                $Twig = new Render($path);
                $Account = new Account( Authentication::GetAccountID() );
                $templateData['CurrentUserPermissions'] = $Account->GetPermissions();

                //Adds paging partial to template
                if(array_key_exists('Paging',$templateData)){
                    $PagingTwig = new Render(ABSPATH ."Views/Shared");
                    $templateData['PagingPartial'] = $PagingTwig->GetTemplate('PagingPartial.twig',$templateData['Paging']);
                }

                echo $Twig->GetTemplate($templateName,$templateData);
            }else{
                $Twig = new Render(ABSPATH.'/Views/Error');
                echo $Twig->GetTemplate('Index.twig',['Error'=>'template does not exist']);
            }


        }

        public function JSON($templateData = []){
            echo json_encode($templateData);
        }

    }