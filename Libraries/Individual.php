<?php


    class Individual extends Account {

        public int $AccountID;
        public function __Construct($AccountID){
            $this->AccountID = $AccountID;
        }


        public function UpdateAccountPrivileges ( $data ): bool
        {
            try{
                $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `Accounts` SET `PermissionGroupID`=:PermissionGroupID WHERE `AccountID`=:AccountID LIMIT 1");
                $smt->execute([
                    ':PermissionGroupID' => $data['PermissionGroupID'],
                    ':AccountID' => $data['AccountID'],
                ]);
                return true;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to update Account privileges Group',
                    'Exception' => $e
                ]);
                return false;
            }
        }








    }