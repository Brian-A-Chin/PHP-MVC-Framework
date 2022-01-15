<?php


    class TemplateManager {
        public string $Source;
        public ?string $Template;
        private static array $AllowedFileExtensions = ['twig','css'];

        #[Pure] public function __Construct($Source,$Template = null) {
           $this->Source = BaseClass::Crypto($Source,'D');
           $this->Template = $Template;

        }



        public static function FetchAll($paths = false):array{

            if(!$paths){
                $paths = [
                    'Templates/SiteComponents/',
                    'Templates/Email/',
                    'Templates/Error/',
                    'Views/Setup/',
                    'Views/Authentication/',
                    'Models/',
                    'Stylesheets/'
                ];
            }

            $TemplateList =  array();

            foreach($paths as $file_path){
                foreach(glob(sprintf("%s%s*.{%s}",ABSPATH,$file_path,implode(",",self::$AllowedFileExtensions)),GLOB_BRACE) as $filename){
                    if(is_file($filename)){
                        $basename = basename($filename);
                        $pathName = substr($file_path,0,strlen($file_path) - 1);
                        //Adds spacing to file name based on capital letters
                        $formattedName = implode(' ',preg_split('/(?=[A-Z])/',pathinfo($basename, PATHINFO_FILENAME)));

                        $TemplateList[$pathName][] = [
                            'Name' => $basename,
                            'ExcludeExtension' => substr($basename,0,strpos($basename,'.')),
                            'FormattedName' => $formattedName,
                            'Location' => BaseClass::Crypto(sprintf("%s%s%s",ABSPATH,$file_path,$basename)),
                            'Modified' => date('F d, Y \a\t g:i:s a', filemtime($filename))
                        ];
                    }
                }
            }

            return $TemplateList;


        }

        public function GetTemplate(){
            if(in_array(pathinfo($this->Source)['extension'],self::$AllowedFileExtensions)){
                return file_get_contents($this->Source);
            }else{
                return [
                    'Result' => false,
                    'Response' => "Invalid File type Please add ".pathinfo($this->Source)['extension']." to your configuration."
                ];
            }

        }

        public function SaveTemplate(){
            if(file_exists($this->Source)){
                file_put_contents($this->Source, $this->Template);
                return [
                    'Result' => true,
                    'Response' => "File updated successfully"
                ];
            }else{
                return [
                    'Result' => false,
                    'Response' => "File does not exist"
                ];
            }
        }
    }