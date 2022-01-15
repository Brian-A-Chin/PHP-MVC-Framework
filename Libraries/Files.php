<?php


    use JetBrains\PhpStorm\Pure;

    class Files extends FilesConfiguration {

        public array $ApprovedFiles;
        public array $WorkingFiles;
        public array $Folders;
        public array $ErrorList;
        public array $NoticeList;
        public string $AccountID;
        public string $AccountDirectory;
        public ?string $OverrideFileName;
        public string $WorkingDirectory;
        public function __construct($ApprovedFiles, $WorkingFiles, $Folders, $AccountID, $OverrideFileName = null){
            $this->ApprovedFiles = explode(",",$ApprovedFiles);
            $this->WorkingFiles = $WorkingFiles;
            $this->Folders = $Folders;
            $this->AccountID = $AccountID;
            $this->AccountDirectory = Account::GetDirectory( BaseClass::Crypto($this->AccountID) );
            $this->OverrideFileName = $OverrideFileName;
            $this->ErrorList = [];
            $this->NoticeList = [];
        }

        public function VerifyUserDirectory() : void{

            $path = sprintf("%s%s/%s",ABSPATH,FilesConfiguration::$UserDirectory,$this->AccountDirectory);
            if (!file_exists($path)) {
                mkdir($path, FilesConfiguration::$BasePermissions);
            }
            $this->WorkingDirectory = $path;
        }

        public function FileExist($fileName) : bool{
            return file_exists(sprintf("%s/%s",$this->WorkingDirectory,$fileName));
        }

        public function VerifyRequestedDirectory() : void{
            for($i = 0; $i < Count($this->Folders);$i++){
                $path = sprintf("%s/%s",$this->WorkingDirectory,$this->Folders[$i]);
                if (!file_exists($path)) {
                    mkdir($path, FilesConfiguration::$BasePermissions);
                }
                $this->WorkingDirectory = sprintf("%s/%s",$this->WorkingDirectory,$this->Folders[$i]);
            }
        }

        public function Upload( ): array {
            if($this->WorkingFiles['size'][0] == 0){
                return [
                    'Result' => false,
                    'ErrorList' => [],
                    'NoticeList' => ['No files to upload']
                ];
            }
            $this->VerifyUserDirectory();
            $this->VerifyRequestedDirectory();
            foreach($this->WorkingFiles['name'] as $key =>  $filename){
                $fileSize = $this->WorkingFiles['size'][$key];
                $fileName = $this->OverrideFileName != null ? $this->OverrideFileName : $filename;
                $tmpName = $this->WorkingFiles['tmp_name'][$key];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                //Logic
                $approvedExt = in_array($extension,FilesConfiguration::GetSafeExtensions());
                $approvedFile = in_array($fileName,$this->ApprovedFiles);
                if($approvedFile && $approvedExt){
                    $oldFilename = $fileName;
                    if($this->FileExist($fileName)){
                        $fileName = sprintf("%s_%s",$fileName,BaseClass::Generate_int_code(8));
                        $this->NoticeList[] = sprintf('"%s" was uploaded as: %s.',$oldFilename,$fileName);
                    }
                    move_uploaded_file($tmpName, sprintf("%s/%s",$this->WorkingDirectory,$fileName));
                }else if(!$approvedExt && $approvedFile){
                    $this->ErrorList[] = sprintf('"%s" is not an approved file format.',$fileName);
                }
            }

            return [
                'Result' => Count($this->ErrorList) != Count($this->WorkingFiles['name']),
                'ErrorList' => $this->ErrorList,
                'NoticeList' => $this->NoticeList
            ];
        }

        public static function GetFiles($Path){

            $FilesList = [];
            foreach(glob(sprintf("%s%s/*.*",ABSPATH,$Path)) as $filename){
                if(is_file($filename)){
                    $basename = basename($filename);
                    $FilesList[] = [
                        'Name' => $basename,
                        'Location' => sprintf("/%s/%s",$Path,$basename),
                        'Modified' => date('F d, Y \a\t g:i:s a', filemtime($filename)),
                        'ModifiedDay' => date('n/j/y ', filemtime($filename))
                    ];
                }
            }
            return $FilesList;
        }



    }