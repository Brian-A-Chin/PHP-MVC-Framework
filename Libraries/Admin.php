<?php
//

class Admin{

    public int $AccountID;
    public function __Construct($AccountID){
        $this->AccountID = $AccountID;
    }

    public function Invite($Data,$ResendInvite = false):int{
        if(Authentication::IsUniqueEmail($Data['Email'])) {
            try {
                $insertInvite = false;
                if ($ResendInvite == false) {
                    $Data['Code'] = sprintf("%u-%u", BaseClass::Generate_int_code(3), BaseClass::Generate_int_code(3));
                    $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `RegistrationCodes` (`Email`,`Identity`,`Code`,`PermissionGroupID`) VALUES (:Email,:Identity,:Code,:PermissionGroupID)");
                    $insertInvite = $smt->execute(array(
                        ":Email" => $Data['Email'],
                        ":Identity" => $Data['Identity'],
                        ":Code" => $Data['Code'],
                        ":PermissionGroupID" => $Data['PermissionGroup']
                    ));
                }
                if ($insertInvite || $ResendInvite) {
                    $mailer = new Mailer('Onboarding.twig', Mailer::GetDefaultAddresses('NoReply'), 'Welcome to the team!',
                        [
                            BaseClass::Crypto($Data['Email'],'d') => [$Data['Identity'], '']
                        ],
                        [
                            'Code' => $Data['Code'],
                            'Identity' => $Data['Identity']
                        ]
                    );
                    if ($mailer->SendEmail()) {
                        return 1;
                    }
                }
                return 0;
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to INSERT invite data',
                    'Exception' => $e
                ]);
                return 0;
            }
        }else{
            return 2;
        }

    }

    public function RemoveInvite( $data ): bool{
        try {
            SQLServices::MakeCoreConnection()->prepare("DELETE FROM `RegistrationCodes` WHERE ID=?")->execute([$data['ID']]);
            return true;
        }catch(Exception $e){
            BaseClass::LogError([
                'Message' => 'Failed to delete invite data',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function ResendInvite( $data ): int{
        try {
            $invite = SQLServices::MakeCoreConnection()->prepare("SELECT * FROM `RegistrationCodes` WHERE ID=?");
            $invite->execute([$data['ID']]);
            $details = $invite->fetch(PDO::FETCH_ASSOC);
            if($invite->rowCount() > 0) {
                return $this->Invite([
                    'Email' => $details['Email'],
                    'Code' => $details['Code'],
                    'Identity' => $details['Identity']
                ], true);
            }
            return 0;
        }catch(Exception $e){
            BaseClass::LogError([
                'Message' => 'Failed to fetch resend invite data',
                'Exception' => $e
            ]);
            return 0;
        }
    }

    public function CreatePermissionGroup ( $data ): bool
    {
        try{
            $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO LetsProvide.`PermissionGroups` (`Name`,`Permissions`)VALUES(:Name,:Permissions)");
            $smt->execute(array(
                ':Name' => $data['Name'],
                ':Permissions' => implode(',',$data['Permissions'])
            ));
            return true;
        }catch(Exception $e){
            BaseClass::LogError([
                'Message' => 'Failed to insert into PermissionGroups',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function UpdatePermissionGroup ( $data ): bool
    {
        try{
            $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `PermissionGroups` SET `Name`=:Name,`Permissions`=:Permissions WHERE `ID`=:ID LIMIT 1");
            $smt->execute([
                ':Name' => $data['Name'],
                ':Permissions' => implode(',',$data['Permissions']),
                ':ID' => $data['ID']
            ]);
            return true;
        }catch(Exception $e){
            BaseClass::LogError([
                'Message' => 'Failed to update Permission Group',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function RemovePermissionGroup( $data ): bool {
        if (Authentication::GetPermissionEnrollmentCount($data['ID']) === 0) {
            try {
                SQLServices::MakeCoreConnection()->prepare("DELETE FROM `PermissionGroups` WHERE ID=?")->execute([$data['ID']]);
                return true;
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to delete permissionGroup',
                    'Exception' => $e
                ]);
                return false;
            }
        }
        return false;
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