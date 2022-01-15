<?php

    class Authentication{

        private int $MinimumPasswordLength = 8;
        private int $MaxLoginAttempts = 3;
        private int $MaxVerificationCodeAttempts = 7;
        private Render $Twig;

        function __construct() {
            $this->Twig = new Render(ABSPATH.'/Views/Authentication');
        }

        public function GetSuccessfulRedirectPage(): string{
            return '/'.explode('/',$_SERVER['HTTP_REFERER'])[3].'/';
        }
        
        public static function UpdateAccountStatus($data ): bool
        {
            try{
                SQLServices::MakeCoreConnection()->prepare("UPDATE `Accounts` SET `Status`=? WHERE `AccountID`=?")->execute( [$data['Status'],$data['AccountID']]);
                return true;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to update user status',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        public function UpdateAccountPassword( $data ): bool
        {
            try{
                SQLServices::MakeCoreConnection()->prepare("UPDATE `Accounts` SET `Password`=? WHERE `AccountID`=?")->execute([BaseClass::Crypto($data['Password']),$data['AccountID']]);
                return true;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to update user password',
                    'Exception' => $e
                ]);
                return false;
            }
        }



        public function AddToLoginHistory ( $data ): bool
        {
            try{
                $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `LoginHistory` (`AccountID`,`IP`,`Metadata`)VALUES(:AccountID,:IP,:Metadata) ON DUPLICATE KEY UPDATE Logged=NOW();");
                $smt->execute(array(
                    ':AccountID' => $data['AccountID'],
                    ':IP' => BaseClass::IP(),
                    ':Metadata' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "UNKNOWN"
                ));
                return true;
            }catch(Exception $e){
                if ($e->errorInfo[1] != 1062) {
                    BaseClass::LogError([
                        'Message' => 'Failed to insert into login history',
                        'Exception' => $e
                    ]);
                    return false;
                }
                return true;
            }
        }
        
        private function LoginLocationExists( $AccountID ): bool
        {
            try{
                $query = 'SELECT IP FROM LoginHistory WHERE AccountID=? AND IP=? LIMIT 1';
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute([$AccountID,BaseClass::IP()]);
                return $statement->rowCount() > 0;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get login history',
                    'Exception' => $e
                ]);
                return false;   
            }
        }

        private function GetUserStatus( $email ): int{
            try{
                $query = 'SELECT Status,AccountID FROM Accounts WHERE Email=? LIMIT 1';
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute([$email]); 
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                return $data['Status'];
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to user status',
                    'Exception' => $e
                ]);
                return 0;
            }
        }

        private function GetUserAccountData($uniqueValue, $method): array | bool{
            try{
                if($method == 'Phone'){
                    $query = 'SELECT AccountID,Password,Email,Status FROM Accounts WHERE Phone=? LIMIT 1';
                }else{
                    $query = 'SELECT AccountID,Password,Email,Status FROM Accounts WHERE Email=? LIMIT 1';
                }
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute([$uniqueValue]);
                return $statement->fetch(PDO::FETCH_ASSOC);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get user authentication data',
                    'Exception' => $e
                ]);
                return false;   
            }
        }
        
        private function GetUserBasicInfo( $AccountID ): array | bool{
            try{
                $query = 'SELECT FirstName,LastName FROM ContactRecords WHERE AccountID=? LIMIT 1';
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute([$AccountID]); 
                return $statement->fetch(PDO::FETCH_ASSOC);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get user basic info',
                    'Exception' => $e
                ]);
                return false;   
            }
        }

        public static function GetPermissionGroups(): array | bool{
            try{
                $query = 'SELECT ID,Name FROM PermissionGroups';
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute();
                return $statement->fetchAll(PDO::FETCH_ASSOC);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get permission group ids',
                    'Exception' => $e
                ]);
                return false;
            }
        }



        public static function GetPermissionGroupByID( $ID ): array | bool{
            try{
                $query = 'SELECT * FROM PermissionGroups WHERE ID=? LIMIT 1';
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute([$ID]);
                return $statement->fetch(PDO::FETCH_ASSOC);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get permission group BY id',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        public static function GetPermissionEnrollmentCount( $ID ): int | bool{
            try{
                $query = 'SELECT COUNT(AccountID) AS `Total` FROM Accounts WHERE PermissionGroupID=?';
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute([$ID]);
                return (int)$statement->fetch(PDO::FETCH_ASSOC)['Total'];
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get permission enrollment Count',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        public static function GetViews(): array | bool{
            $files = array();
            $approvedViews = TemplateManager::FetchAll([
                'Views/Admin/',
                'Views/Authentication/',
                'Views/Explorer/',
                'Views/Individual/',
                'Views/Manage/'
            ]);
            if(is_array($approvedViews)){
                foreach ($approvedViews as $key => $value) {
                    foreach ($approvedViews[$key] as $value) {
                        $files[ $value['ExcludeExtension'] ] = $value['FormattedName'];
                    }
                }
            }
            return $files;
        }

        public static function RequireAuthentication() : void{
            if(isset($_SESSION['AUTHENTICATION'])) {
                try {
                    $query = 'SELECT Status,AccountID FROM Accounts WHERE Email=? AND Status=1 LIMIT 1';
                    $statement = SQLServices::MakeCoreConnection()->prepare($query);
                    $statement->execute([$_SESSION['AUTHENTICATION']['Email']]);
                    if($statement->rowCount() == 0){
                        header("Location: /account/login");
                        exit;
                    }
                } catch (Exception $e) {
                    BaseClass::LogError([
                        'Message' => 'Failed to verify if user is logged in',
                        'Exception' => $e
                    ]);
                    header("Location: /account/login");
                    exit;
                }
            }else{
                header("Location: /account/login");
                exit;
            }
        }

        public static function GetAccountID($encrypt = false){
            if(isset($_SESSION['AUTHENTICATION'])) {
                try {
                    $query = 'SELECT AccountID FROM Accounts WHERE Email=? AND Status=1 LIMIT 1';
                    $statement = SQLServices::MakeCoreConnection()->prepare($query);
                    $statement->execute([$_SESSION['AUTHENTICATION']['Email']]);
                    if($statement->rowCount() > 0){
                        $acc = $statement->fetch(PDO::FETCH_ASSOC)['AccountID'];
                        return $encrypt ? BaseClass::Crypto($acc) : $acc;
                    }else{
                        return false;
                    }
                } catch (Exception $e) {
                    BaseClass::LogError([
                        'Message' => 'Failed to get AccountID',
                        'Exception' => $e
                    ]);
                    return false;
                }
            }
            return false;
        }


        public static function ActionIsAuthorized( $Data ): bool {
            try {

                $query = 'SELECT AccountID FROM PermissionGroups INNER JOIN Accounts A on PermissionGroups.ID = A.PermissionGroupID WHERE (AccountID=? AND Status=1) AND (FIND_IN_SET(?,Permissions)) LIMIT 1';
                try {
                    $statement = SQLServices::MakeCoreConnection()->prepare($query);
                    $statement->execute([Authentication::GetAccountID(), $Data['Action']]);
                    return $statement->rowCount() > 0;
                } catch (Exception $e) {
                    BaseClass::LogError([
                        'Message' => 'Failed to check if action is authorized for account',
                        'Exception' => $e
                    ]);
                    return false;
                }
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Namespace('.Filters::AlphaFilter($Data['Namespace']).') does not exist in permissions group',
                    'Exception' => $e
                ]);
                return false;
            }
        }


        
        public function GetCurrentLoginStatus(): bool|array {
            
            if(isset($_SESSION['AUTHENTICATION'])){
                //1 ready | 2 verification | 3 banned
                return $this->GetUserStatus( $_SESSION['AUTHENTICATION']['Email'] );
            }else{
                //Auth session data not set
                return false;
            }
            
        }


        public function VerifyRegisterCode( $data ): bool
        {
            try{
                $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT Code FROM RegistrationCodes WHERE Code=?");
                $stmt->execute([$data['Code']]);
                return $stmt->rowCount() > 0;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to validate Registration Code',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        private function GetRegistrationCodeDetails( $data ){

            try{
                $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT ID,PermissionGroupID FROM RegistrationCodes WHERE Code=?");
                $stmt->execute([$data['Code']]);
                return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to validate Registration Code',
                    'Exception' => $e
                ]);
                return false;
            }

        }

        private function DeleteRegistrationCode( $ID ): void{
            try {
                SQLServices::MakeCoreConnection()->prepare("DELETE FROM RegistrationCodes WHERE ID=?")->execute([$ID]);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to delete Registration Code',
                    'Exception' => $e
                ]);
            }
        }


        private function GetPasswordResetLinkData( $data ): array | bool{

            try{
                $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT * FROM ResetLinks WHERE (Code=?) AND (IP=? AND NOW() <= (Posted + INTERVAL 5 MINUTE)) limit 1");
                $stmt->execute([trim($data['Code']),BaseClass::IP()]);
                return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC) : [];
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get password reset Link data',
                    'Exception' => $e
                ]);
                return false;
            }

        }

        private function DeleteSecurityCodeData( $data ): void{
            $email = array_key_exists("Email",$data) ? $data['Email'] : null;
            try{
                $stmt = SQLServices::MakeCoreConnection()->prepare("DELETE FROM VerificationCodes WHERE Email=? OR IP=?");
                $stmt->execute([trim($email),BaseClass::IP()]);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get delete verification code data',
                    'Exception' => $e
                ]);

            }

        }

        private function GetSecurityCodeData( $data ): array | bool{

            try{
                $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT * FROM VerificationCodes WHERE (Code=?) AND (IP=? AND NOW() <= (Posted + INTERVAL 5 MINUTE)) limit 1");
                $stmt->execute([trim($data['Code']),BaseClass::IP()]);
                return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC) : [];
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get password reset Link data',
                    'Exception' => $e
                ]);
                return false;
            }

        }
        
        public static function IsUniqueEmail( $email ): bool
        {

            $accountID = Authentication::GetAccountID();
            try{
                if($accountID != false){
                    $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT Email FROM Accounts WHERE Email=? AND AccountID!=?");
                    $stmt->execute([$email,$accountID]);
                }else{
                    $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT Email FROM Accounts WHERE Email=?");
                    $stmt->execute([$email]);
                }
                return $stmt->rowCount() == 0;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to check if email is unique',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        public static function IsUniquePhone( $Phone ): bool
        {
            $accountID = Authentication::GetAccountID();
            try{
                if($accountID != false) {
                    $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT Phone FROM Accounts WHERE Phone=? AND AccountID!=?");
                    $stmt->execute([$Phone, $accountID]);
                }else{
                    $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT Phone FROM Accounts WHERE Phone=?");
                    $stmt->execute([$Phone]);
                }
                return $stmt->rowCount() == 0;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to check if Phone is unique',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        private function EmailIsLinkedToActiveAccount( $email ): bool {
            try{
                $stmt = SQLServices::MakeCoreConnection()->prepare("SELECT Email FROM Accounts WHERE Email=? AND Status=1");
                $stmt->execute([$email]);
                return $stmt->rowCount() == 1;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to check if email is linked to an account',
                    'Exception' => $e
                ]);
                return false;
            }
        }



        private function AuthenticateUser( $data ): bool
        {
            $accountID = $data['AccountID'];

            $email = $data['Email'];
            $AddToLoginHistory = (bool)$data['Remember'] ? $this->AddToLoginHistory(['AccountID' => $accountID]) : true;
            if( $AddToLoginHistory && Authentication::UpdateAccountStatus([
                'Status' => 1,
                'AccountID' => $accountID
            ]) ) {

                SQLServices::MakeCoreConnection()->prepare("DELETE FROM VerificationCodes WHERE AccountID=? LIMIT 1")->execute([$accountID]);
                SQLServices::MakeCoreConnection()->prepare("DELETE FROM ResetLinks WHERE AccountID=? LIMIT 1")->execute([$accountID]);

                $_SESSION['AUTHENTICATION'] = [
                    'Email' => $email
                ];

                unset($_SESSION['LoginAttempts']);
                unset($_SESSION['VerificationCodeAttempts']);

                return true;
            }else{
                return false;
            }
            
        }


        public function SendVerificationCode($data): bool
        {
            $userBasicInfo = $this->GetUserBasicInfo($data['AccountID']);
            $AccountID = $data['AccountID'];
            try{
                SQLServices::MakeCoreConnection()->prepare("DELETE FROM VerificationCodes WHERE AccountID=? LIMIT 1")->execute([$AccountID]);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to delete codes from verificationCode by AccountID',
                    'Exception' => $e
                ]);
                return false;
            }

            try{
                $verificationCode = BaseClass::Generate_int_code(6);
                $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `VerificationCodes` (`IP`, `Email`,`AccountID`,`Code`) VALUES (:IP, :Email,:AccountID,:Code)");
                $insertCode = $smt->execute(array(
                    ":IP" =>  BaseClass::IP(),
                    ":Email" => $data['Email'],
                    ":AccountID" => $AccountID,
                    ":Code" => $verificationCode
                ));

                if($insertCode){
                    if($data['Method'] == 'Phone'){
                        $smsControl = new SMSControl($data['Phone'],BusinessName.'Verification code:'.$verificationCode);
                        if($smsControl->SendSMS()){
                            return true;
                        }
                    }else{
                        $mailer = new Mailer('CodeVerification.twig', Mailer::GetDefaultAddresses('Security'), 'Verify Sign in',
                            [
                                BaseClass::Crypto($data['Email'],'Decrypt') => [$userBasicInfo['FirstName'],$userBasicInfo['LastName']]
                            ],
                            [
                                'verification_code' => $verificationCode,
                                'first_name' => $userBasicInfo['FirstName'],
                                'login_attempts' => isset($_SESSION['LoginAttempts']) ? $_SESSION['LoginAttempts'] : 0
                            ]
                        );

                        if ($mailer->SendEmail()){
                            return true;
                        }
                    }
                }

            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to Insert Verification code',
                    'Exception' => $e
                ]);
                return false;
            }
            return false;
        }

        public function VerifySecurityCode( $code ): array
        {
            $code = preg_replace('~\D~', '', $code);
            $verificationData = $this->GetSecurityCodeData([
                'Code' => $code,
            ]);
            if(is_array($verificationData) && !empty($verificationData)) {
                if ($this->AuthenticateUser([
                    "Email" => $verificationData['Email'],
                    "AccountID" => $verificationData['AccountID'],
                    "Remember" => true,
                ])) {
                    return ['Result'=>true,'Response'=>'Thanks for verifying!','Redirect'=>$this->GetSuccessfulRedirectPage(),'Timeout'=>0];
                } else {
                    return BaseClass::GetError('Critical');
                }
            }else if(is_array($verificationData)){

                if(!isset($_SESSION['VerificationCodeAttempts']))
                    $_SESSION['VerificationCodeAttempts'] = 0;

                $attempts = $_SESSION['VerificationCodeAttempts']++;

                if($attempts == $this->MaxVerificationCodeAttempts){
                    $this->DeleteSecurityCodeData([]);
                    $_SESSION['VerificationCodeAttempts'] = 0;
                    return ['Result'=>false,'Response'=>'Attempts exceeded. Please try again later.', 'Redirect' => '/account/login','Timeout'=>7000];
                }

                return ['Result'=>false,'Response'=>'Code not valid'];
            }else{
                return BaseClass::GetError('Critical');
            }
        }




        public function SendAccountPasswordResetLink( $data ): array
        {
            $email = isset($data['Email']) ? Baseclass::Crypto($data['Email']) : false;
            $response = ['Result' => true, 'Response' => 'If ' . $data['Email'] . ' matches what we have on file, we will send you a reset link.','View'=> $this->Twig->GetTemplate('Login.twig')];
            if ($this->EmailIsLinkedToActiveAccount($email)) {
                $authenticationData = $this->GetUserAccountData($email,'Email');
                if($authenticationData['Status'] != '0') {
                    try {
                        $verificationCode = BaseClass::GenerateAlphaNumericCode(32);
                        $userAuthInfo = $this->GetUserAccountData($email,'Email');
                        $AccountID = $userAuthInfo['AccountID'];
                        $userBasicInfo = $this->GetUserBasicInfo($AccountID);
                        SQLServices::MakeCoreConnection()->prepare("DELETE FROM ResetLinks WHERE AccountID=? LIMIT 1")->execute([$AccountID]);
                        $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `ResetLinks` (`IP`, `Email`,`AccountID`,`Code`) VALUES (:IP, :Email,:AccountID,:Code)");
                        $smt->execute(array(
                            ":IP" => BaseClass::IP(),
                            ":Email" => $email,
                            ":AccountID" => $AccountID,
                            ":Code" => $verificationCode
                        ));

                        $mailer = new Mailer('PasswordReset.twig', Mailer::GetDefaultAddresses('Security'), 'Password Reset',
                            [
                                $data['Email'] => [$userBasicInfo['FirstName'], $userBasicInfo['LastName']]
                            ],
                            [
                                'reset_link' => 'http://192.168.1.50:8080/account/login?resetcode=' . $verificationCode,
                                'first_name' => $userBasicInfo['FirstName']
                            ]
                        );

                        if (!$mailer->SendEmail()) {
                            $response = BaseClass::GetError('Critical');
                        }

                    } catch (Exception $e) {
                        BaseClass::LogError([
                            'Message' => 'Failed to send password reset link to User',
                            'Exception' => $e
                        ]);
                        $response = BaseClass::GetError('Critical');
                    }
                }

            }else{
                $response = ['Result' => false, 'Response' => 'This account is no longer authorized.','View'=> $this->Twig->GetTemplate('Login.twig')];
            }
            return $response;
        }

        public function VerifyPasswordResetLink($data): bool
        {
            $response = $this->GetPasswordResetLinkData($data);
            return is_array($response) && !empty($response);
        }

        public function ResetPasswordByResetCode($data): array
        {
            $response = $this->GetPasswordResetLinkData([
                'Code' => $data['Code']
            ]);
            if(is_array($response) && !empty($response)){
                if($this->UpdateAccountPassword([
                    'Password' => $data['Password'],
                    'AccountID' => $response['AccountID']
                ])){
                    if ($this->AuthenticateUser([
                        "Email" => $response['Email'],
                        "AccountID" => $response['AccountID'],
                        "Remember" => true,
                    ])) {
                        return ['Result'=>true,'Response'=>'Password Updated','Redirect'=>$this->GetSuccessfulRedirectPage()];
                    } else {
                        return BaseClass::GetError('Critical');
                    }

                }else{
                    return BaseClass::GetError('Critical');
                }
            }else{
                return BaseClass::GetError('Critical');
            }
        }


        public function Register( $registrationInfo ): array
        {
            $DBKN = BaseClass::GenerateAlphaNumericCode(4).'_'.BaseClass::Generate_int_code('5').'_'.BaseClass::GenerateAlphaNumericCode(4);
            $permissionGroup = 0;
            $registrationCode = $registrationInfo['Code'];
            $registrationSettings = Security::GetSettings('Registration');
            $requireRegistrationCode = $registrationSettings['RequireRegistrationCode'];
            $email = $registrationInfo['Email'];
            $phone = $registrationInfo['Phone'];
            $password = $registrationInfo['Password'];
            if(($registrationCode != false || !$requireRegistrationCode)) {
                //Identity classification
                $firstName = null;
                $lastName = null;
                $identity = $registrationInfo['Identity'];
                $identityParts = explode(' ', $identity);
                if (count($identityParts) > 1) {
                    $firstName = $identityParts[0];
                    $lastName = $identityParts[count($identityParts) - 1];
                }



                if (Filters::ValidateEmail($email)) {
                    if (Authentication::IsUniqueEmail(Baseclass::Crypto($email)) && Authentication::IsUniquePhone(Baseclass::Crypto($phone))) {

                        if($registrationCode != false) {
                            $registrationCodeDetails = $this->GetRegistrationCodeDetails([
                                'Code' => $registrationCode
                            ]);
                            if($registrationCodeDetails != false) {
                                $permissionGroup = (int)$registrationCodeDetails['PermissionGroupID'];
                                $this->DeleteRegistrationCode($registrationCodeDetails['ID']);
                            }else{
                                return ['Result' => false, 'Response' => 'Invitation code not valid'];
                            }
                        }

                        if (strlen($registrationInfo['Password']) >= $this->MinimumPasswordLength) {
                            try {
                                $conn = SQLServices::MakeCoreConnection();
                                $smt = $conn->prepare("INSERT INTO `Accounts` (`Email`,`Phone`,`Password`,`PermissionGroupID`,`DBKN`) VALUES (:Email,:Phone,:Password,:PermissionGroupID,:DBKN)");
                                $smt->execute(array(
                                    "Email" => Baseclass::Crypto($email),
                                    "Phone" => Baseclass::Crypto($phone),
                                    "Password" => Baseclass::Crypto($password),
                                    "PermissionGroupID" => $permissionGroup,
                                    "DBKN" => $DBKN
                                ));

                                $accountID = $conn->lastInsertId();
                                $this->AddToLoginHistory([
                                    'AccountID' => $accountID
                                ]);

                                $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `AccountSettings` (`AccountID`) VALUES (:AccountID)");
                                $smt->execute(array(
                                    "AccountID" => $accountID,
                                ));

                                $smt = SQLServices::MakeCoreConnection()->prepare("INSERT INTO `ContactRecords` (`AccountID`,`FirstName`,`LastName`,`Identifier`) VALUES (:AccountID,:FirstName,:LastName,:Identifier)");
                                $smt->execute(array(
                                    "AccountID" => $accountID,
                                    "FirstName" => $firstName,
                                    "LastName" => $lastName,
                                    "Identifier" => $identity
                                ));

                                if ($this->AuthenticateUser([
                                    "Email" => Baseclass::Crypto($email),
                                    "AccountID" => $accountID,
                                    "Remember" => true,
                                ])) {

                                    $multitenancy = new Multitenancy($DBKN);
                                    if($multitenancy->CreateDatabase()){
                                        if($multitenancy->FillNewDatabase()){
                                            return ['Result' => true, 'Response' => 'Registration Successful!', 'Redirect' => $this->GetSuccessfulRedirectPage()];
                                        }else{
                                            return BaseClass::GetError('Critical');
                                        }
                                    }


                                } else {
                                    return BaseClass::GetError('Critical');
                                }

                            } catch (Exception $e) {
                                BaseClass::LogError([
                                    'Message' => 'Failed to register User',
                                    'Exception' => $e
                                ]);
                                return BaseClass::GetError('Critical');
                            }
                        } else {
                            //password length not sufficient
                            return ['Result' => false, 'Response' => 'Password does not meet requirements.'];
                        }
                    } else {
                        //duplicate email
                        return ['Result' => false, 'Response' => 'Account with this email or phone already exist.', 'View' => $this->Twig->GetTemplate('Login.twig')];

                    }
                } else {
                    return ['Result' => false, 'Response' => 'Email is not valid'];
                }
            }else{
                return ['Result' => false, 'Response' => 'Invitation code required'];
            }
            
        }


        
        public function Login ($LoginInfo ): array
        {
            $UniqueValue = $LoginInfo['UniqueValue'];
            $usingPhone = self::IsUniquePhone(BaseClass::Crypto($UniqueValue)) ? false : true;

            if($usingPhone || Filters::ValidateEmail($UniqueValue)){
                $UniqueValue = BaseClass::Crypto($UniqueValue);
                if ($usingPhone || !Authentication::IsUniqueEmail($UniqueValue)) {
                    $accountData = $this->GetUserAccountData($UniqueValue,$usingPhone ? 'Phone' : 'email');
                    if (is_array($accountData)) {
                        $accountID = $accountData['AccountID'];
                        $email = $accountData['Email'];

                        if(isset($_SESSION['LoginAttempts'])){
                            if($_SESSION['LoginAttempts'] == $this->MaxLoginAttempts){
                                /* set account to verification state*/
                                Authentication::UpdateAccountStatus([
                                    'Status' => 2,
                                    'AccountID' => $accountID
                                ]);


                                $userBasicInfo = $this->GetUserBasicInfo($accountID);
                                $mailer = new Mailer('LimitedAccountAccess.twig', Mailer::GetDefaultAddresses('Security'), 'Account Notification',
                                    [
                                        BaseClass::Crypto($email,'Decrypt') => [$userBasicInfo['FirstName'],$userBasicInfo['LastName']]
                                    ],
                                    [
                                        'first_name' => $userBasicInfo['FirstName'],
                                        'last_name' => $userBasicInfo['LastName'],
                                        'IP' => BaseClass::IP(),
                                        'server_data' => $_SERVER
                                    ]
                                );
                                $mailer->SendEmail();

                            }
                        }

                        if ($accountData['Password'] == BaseClass::Crypto($LoginInfo['Password'])) {
                            $Account = new Account($accountID);
                            $securityCodeDeliveryMethod = $usingPhone ? 'Phone' : $Account->GetSettings('2FAMethod')['2FAMethod'];
                            $phone = null;
                            if($securityCodeDeliveryMethod == 'Phone')
                                $phone = $usingPhone ? $LoginInfo['UniqueValue'] : $Account->GetPhone();

                            $verificationErrorType = ['Result' => false, 'Response' => 'Please enter the code sent to your '.$securityCodeDeliveryMethod, 'View' => $this->Twig->GetTemplate('ConfirmationCode.twig', ['account_ID' => BaseClass::Crypto($accountData['AccountID']), 'account_email' => $email])];
                            $accountStatus = $this->GetUserStatus($email);
                            if ($this->LoginLocationExists($accountID) || $accountStatus == AccountDeclarations::GetAccountState('Disabled')) {
                                switch ($accountStatus) {
                                    case AccountDeclarations::GetAccountState('Ready'):
                                        //login info is correct
                                        if($usingPhone){

                                            if ($this->SendVerificationCode([
                                                'Method' => $securityCodeDeliveryMethod,
                                                'Phone' => $phone,
                                                'Email' => $email,
                                                'AccountID' => $accountID
                                            ])) {
                                                return $verificationErrorType;
                                            } else {
                                                return ['Result' => false, 'Response' => 'An unknown error occurred please try again later.'];
                                            }

                                        }else{
                                            if ($this->AuthenticateUser([
                                                "Email" => $email,
                                                "AccountID" => $accountID,
                                                "Remember" => $LoginInfo['Remember'],
                                            ])) {
                                                return ['Result' => true, 'Redirect' => $this->GetSuccessfulRedirectPage(),'Timeout'=>0];
                                            } else {
                                                return BaseClass::GetError('Critical');
                                            }
                                        }
                                        break;
                                    case AccountDeclarations::GetAccountState('Verification'):
                                        //in verification state
                                        if ($this->SendVerificationCode([
                                            'Method' => $securityCodeDeliveryMethod,
                                            'Phone' => $phone,
                                            'Email' => $email,
                                            'AccountID' => $accountID
                                        ])) {
                                            return $verificationErrorType;
                                        } else {
                                            return ['Result' => false, 'Response' => 'An unknown error occurred please try again later.'];
                                        }
                                        break;
                                    case AccountDeclarations::GetAccountState('Disabled'):
                                        //account is locked/banned
                                        return ['Result' => false, 'Response' => 'This account is no longer authorized.'];
                                        break;
                                    default:
                                        return ['Result' => false, 'Response' => 'An unknown error occurred please try again later.'];
                                }
                            } else {
                                //Login location does not exist
                                if ($this->SendVerificationCode([
                                    'Method' => $securityCodeDeliveryMethod,
                                    'Phone' => $phone,
                                    'Email' => $email,
                                    'AccountID' => $accountID
                                ])) {
                                    $Error = $verificationErrorType;
                                } else {
                                    $Error = ['Result' => false, 'Response' => 'An unknown error occurred please try again later.'];
                                }
                            }
                        } else {
                            //password is not valid
                            $Error = ['Result' => false, 'Response' => 'Invalid credentials'];
                        }
                    } else {
                        //email is not valid
                        $Error = ['Result' => false, 'Response' => 'Invalid credentials'];
                    }
                }else{
                    //account does not exist
                    $Error = ['Result' => false, 'Response' => 'There is no associated account for this email or phone.'];
                }
                $_SESSION['LoginAttempts']++;
                return $Error;
            }else{
                //email is not an email
                $_SESSION['LoginAttempts']++;
                return ['Result'=>false,'Response'=>'Please enter a valid email or phone'];
            }
            
        }

        
    }

  