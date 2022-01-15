<?php


class Account{

    public int $AccountID;
    public function __Construct($AccountID){
        $this->AccountID = $AccountID;
    }



    public function GetAccountPermissionGroupID($AccountID = false) : int | bool{
        try {
            $query = 'SELECT PermissionGroupID FROM Accounts WHERE AccountID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$AccountID != false ? $AccountID : $this->AccountID]);
            return (int)$statement->fetch(PDO::FETCH_ASSOC)['PermissionGroupID'];
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account PermissionGroupID',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function GetPermissions(): array | bool {
        try {
            $query = 'SELECT Permissions FROM LetsProvide.PermissionGroups WHERE ID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->GetAccountPermissionGroupID()]);
            $results = $statement->fetch(PDO::FETCH_ASSOC);
            $permissionsArray =  strlen($results['Permissions']) > 0 ? explode(',',$results['Permissions']) : array();
            return Filters::CleanArray( $permissionsArray );
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account Status',
                'Exception' => $e
            ]);
            return false;
        }

    }

    public function GetAccountStatus($AccountID = false) : int | bool{
        try {
            $query = 'SELECT Status FROM Accounts WHERE AccountID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$AccountID != false ? $AccountID : $this->AccountID]);
            return $statement->rowCount() > 0 ? $statement->fetch(PDO::FETCH_ASSOC)['Status'] : false;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account Status',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function GetIdentity() : string | bool{
        try {
            $query = 'SELECT Identifier FROM ContactRecords WHERE AccountID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->AccountID]);
            return $statement->rowCount() > 0 ? $statement->fetch(PDO::FETCH_ASSOC)['Identifier'] : false;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account Identifier',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function GetEmail() : string | bool{
        try {
            $query = 'SELECT Email FROM Accounts WHERE AccountID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->AccountID]);
            return $statement->rowCount() > 0 ? BaseClass::Crypto($statement->fetch(PDO::FETCH_ASSOC)['Email'],'d') : false;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account Email',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function GetPhone() : string | bool{
        try {
            $query = 'SELECT Phone FROM Accounts WHERE AccountID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->AccountID]);
            return $statement->rowCount() > 0 ? BaseClass::Crypto($statement->fetch(PDO::FETCH_ASSOC)['Phone'],'d') : false;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account Phone',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function GetPrimaryAddress($ReturnAsArray = false) : string | bool | array{
        try {
            $query = 'SELECT * FROM AddressBook WHERE AccountID=? AND PrimaryAddress=1 LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->AccountID]);
            if($statement->rowCount() > 0){
                $address = $statement->fetch(PDO::FETCH_ASSOC);
                return !$ReturnAsArray ? $address['AddressOne']." ".$address['AddressTwo']." ".$address['City'].",".$address['State']." ".$address['Zipcode'] : $address;
            }else{
                return false;
            }
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account Identifier',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public static function GetDirectory($AccountID): string {
        return Filters::AlphaNumericFilter($AccountID);
    }

    public function IsComplete(): bool {
        try {
            $query = 'SELECT Complete FROM Accounts WHERE AccountID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->AccountID]);
            return $statement->fetch(PDO::FETCH_ASSOC)['Complete'] != 0;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account Status',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function RequireFullAccountSetup(): void{
        if(!$this->IsComplete()) {
            header("Location: /Account/Setup");
            exit;
        }
    }

    public function CompleteSetup(): bool {
        try{
            SQLServices::MakeCoreConnection()->prepare("UPDATE `Accounts` SET `Complete`=1 WHERE `AccountID`=?")->execute( [$this->AccountID]);
            return true;
        }catch(Exception $e){
            BaseClass::LogError([
                'Message' => 'Failed to set account as complete',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function GetAllAddresses( ): array{

        try {
            $query = 'SELECT * FROM AddressBook WHERE AccountID=?';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->AccountID]);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get all address records from account',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function GetSpecificAddressRecord($EncryptedID ): array | bool{

        try {
            $query = 'SELECT * FROM AddressBook WHERE AccountID=? AND ID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->AccountID, BaseClass::Crypto($EncryptedID,'D')]);
            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get address record',
                'Exception' => $e
            ]);
            return false;
        }
    }



    public function AddAddressBookInfo($data ): bool {
        try {
            $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `AddressBook` (`AccountID`,`FullName`,`CompanyName`,`AddressOne`,`AddressTwo`,`City`,`State`,`Zipcode`,`Phone`,`Country`,`PrimaryAddress`) VALUES (:AccountID, :FullName,:CompanyName,:AddressOne,:AddressTwo,:City,:State,:Zipcode,:Phone,:Country,:PrimaryAddress)");
            $smt->execute(array(
                ":AccountID" => $this->AccountID,
                ":FullName" => $data['FullName'],
                ":CompanyName" => $data['CompanyName'],
                ":AddressOne" => $data['AddressOne'],
                ":AddressTwo" => $data['AddressTwo'],
                ":City" => $data['City'],
                ":State" => $data['State'],
                ":Zipcode" => $data['Zip'],
                ":Phone" => $data['Phone'],
                ":Country" => 'US',
                ":PrimaryAddress" => $data['PrimaryAddress'],
            ));
            return true;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get add contact information',
                'Exception' => $e
            ]);
            return false;
        }
    }


        public function UpdateAddressBookInfo($data ): bool {
            try {
                $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `AddressBook` SET FullName=:FullName,CompanyName=:CompanyName,AddressOne=:AddressOne,AddressTwo=:AddressTwo,City=:City,State=:State,Zipcode=:Zipcode,Phone=:Phone,Country=:Country WHERE ID=:ID AND AccountID=:AccountID");
                $smt->execute(array(
                    ":FullName" =>  $data['FullName'],
                    ":CompanyName" =>  $data['CompanyName'],
                    ":AddressOne" => $data['AddressOne'],
                    ":AddressTwo" => $data['AddressTwo'],
                    ":City" => $data['City'],
                    ":State" => $data['State'],
                    ":Zipcode" => $data['Zip'],
                    ":Phone" => $data['Phone'],
                    ":Country" => 'US',
                    ":AccountID" =>  $this->AccountID,
                    ":ID" => $data['ID'],
                ));
                return true;
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to update contact information',
                    'Exception' => $e
                ]);
                return false;
            }
        }

    public function UpdateContactInfo($data ): bool {
        try {
            $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `ContactRecords` SET Identifier=:Identifier,FirstName=:FirstName,LastName=:LastName WHERE AccountID=:AccountID");
            $smt->execute(array(
                ":Identifier" =>  $data['Identifier'],
                ":FirstName" =>  $data['FirstName'],
                ":LastName" => $data['LastName'],
                ":AccountID" =>  $this->AccountID
            ));
            return true;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to update contact information',
                'Exception' => $e
            ]);
            return false;
        }
    }
    public function UpdateEmail( $Email ): bool {
        if (Authentication::IsUniqueEmail($Email)) {
            try {
                $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE LetsProvide.`Accounts` SET Email=:Email WHERE AccountID=:AccountID");
                $smt->execute(array(
                    ":Email" => $Email,
                    ":AccountID" => $this->AccountID
                ));
                return true;
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to update account email',
                    'Exception' => $e
                ]);
                return false;
            }
        }
        return false;
    }

    public function UpdatePhone( $Phone ): bool {
        if (Authentication::IsUniquePhone($Phone)) {
            try {
                $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE LetsProvide.`Accounts` SET Phone=:Phone WHERE AccountID=:AccountID");
                $smt->execute(array(
                    ":Phone" => $Phone,
                    ":AccountID" => $this->AccountID
                ));
                return true;
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to update account Phone',
                    'Exception' => $e
                ]);
                return false;
            }
        }
        return false;
    }

    public function UpdateSetting( $Data ): bool {
        $setting = $Data['Setting'];
        if(in_array($setting,['2FAMethod'])){
            try {
                $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE LetsProvide.`AccountSettings` SET ".$setting."=:value WHERE AccountID=:AccountID");
                $smt->execute(array(
                    ":value" => $Data['Value'],
                    ":AccountID" => $this->AccountID
                ));
                return true;
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to update account setting->'.$setting,
                    'Exception' => $e
                ]);
                return false;
            }
        }

        return false;
    }

    public function GetSettings($setting = null): array | bool {
        try {
            if($setting == null){
                $query = 'SELECT * FROM LetsProvide.AccountSettings WHERE AccountID=? LIMIT 1';
            }else{
                $query = 'SELECT '.$setting.' FROM LetsProvide.AccountSettings WHERE AccountID=? LIMIT 1';
            }
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([$this->AccountID]);
            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get Account Status',
                'Exception' => $e
            ]);
            return false;
        }

    }

    public function UpdatePassword( $Password ): bool {
        try {
            $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE LetsProvide.`Accounts` SET Password=:Password WHERE AccountID=:AccountID");
            $smt->execute(array(
                ":Password" => $Password,
                ":AccountID" => $this->AccountID
            ));
            return true;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to update account Phone',
                'Exception' => $e
            ]);
            return false;
        }

    }

    public function SetAddressAsPrimary( $data ): bool {
        try {
            $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `AddressBook` SET PrimaryAddress=0 WHERE AccountID=:AccountID");
            $smt->execute(array(
                ":AccountID" =>  $this->AccountID
            ));

            $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `AddressBook` SET PrimaryAddress=1 WHERE ID=:ID AND AccountID=:AccountID");
            $smt->execute(array(
                ":AccountID" =>  $this->AccountID,
                ":ID" => $data['ID'],
            ));
            return true;
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to update contact information',
                'Exception' => $e
            ]);
            return false;
        }

    }

    public function RemoveAddress( $data ): bool{
        try {
            SQLServices::MakeCoreConnection()->prepare("DELETE FROM `AddressBook` WHERE ID=? AND AccountID=? AND PrimaryAddress=0")->execute([$data['ID'],$this->AccountID]);
            return true;
        }catch(Exception $e){
            BaseClass::LogError([
                'Message' => 'Failed to delete address Code',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public function SendClientEmailVerificationCode( $Email ) : bool{
        $identityParts = explode(' ', $this->GetIdentity());
        if (count($identityParts) > 1) {
            $firstName = $identityParts[0];
            $lastName = $identityParts[count($identityParts) - 1];
        }else{
            $firstName = $identityParts[0];
            $lastName = '';
        }
        $verificationCode = BaseClass::Generate_int_code(6);
        $mailer = new Mailer('EmailVerification.twig', Mailer::GetDefaultAddresses('Security'), 'Verification',
            [
                $Email => [$firstName,$lastName]
            ],
            [
                'verification_code' => $verificationCode,
                'first_name' => $firstName
            ]
        );

        if (!$mailer->SendEmail()){
            return false;
        }else{
            $_SESSION['ClientVerificationCode'] = $verificationCode;
            return true;
        }

    }

    public function SendClientSMSVerificationCode( $Phone ) : bool{

        $verificationCode = BaseClass::Generate_int_code(6);

        $smsControl = new SMSControl($Phone,BusinessName.'Verification code:'.$verificationCode);
        if(!$smsControl->SendSMS()){
            return false;
        }else{
            $_SESSION['ClientVerificationCode'] = $verificationCode;
            return true;
        }

    }

}