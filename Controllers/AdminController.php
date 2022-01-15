<?php
    class AdminController extends Controller {
        public function __construct() {

        }

        public function Index() {



            $this->View();
        }

        //HTTP METHOD: GET
        public function CreatePermissionGroup(): void {
            $permissionsData = array();
            if (isset($_GET['ref'])) {
                $ID = Filters::NumberFilter(BaseClass::Crypto($_GET['ref'], 'd'));
                $data = Authentication::GetPermissionGroupByID($ID);
                if ($data != false) {
                    $permissionsData = [
                        'ID' => $data['ID'],
                        'EncryptedID' => BaseClass::Crypto($data['ID']),
                        'Name' => $data['Name'],
                        'Permissions' => explode(',', $data['Permissions']),
                        'TotalActive' => Authentication::GetPermissionEnrollmentCount($ID)
                    ];
                }
            }

            $this->View([
                'PermissionsData' => $permissionsData,
            ]);

        }

        //HTTP METHOD: POST
        public function ManagePermissionGroup(): void {
            $Admin = new Admin(Authentication::GetAccountID());
            $Permissions = $_POST['Permissions'];
            if ($_POST['CreatePermissions'] == 'true') {
                if ($Admin->CreatePermissionGroup([
                    'Name' => $_POST['Name'],
                    'Permissions' => $Permissions
                ])) {
                    $this->JSON(['Result' => true, 'PopupMsg' => 'Group Created', 'Redirect' => '/account/admin/?action=ViewPermissionGroups']);
                } else {
                    $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
                }
            } else {
                if ($Admin->UpdatePermissionGroup([
                    'ID' => BaseClass::Crypto($_POST['PermissionGroupID'], 'd'),
                    'Name' => $_POST['Name'],
                    'Permissions' => $Permissions
                ])) {
                    $this->JSON(['Result' => true, 'PopupMsg' => 'Group Updated']);
                } else {
                    $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
                }
            }
        }

        //HTTP METHOD: GET
        public function ViewPermissionGroups(): void {

            $Pagination = new Pagination([
                'Request' => $_GET,
                'Columns' => ['Name', 'ID', 'Posted'],
                'Query' => 'SELECT ID,Name,DATE_FORMAT(Posted, "%m/%d/%y %I:%i %p") AS NiceDate, count(ID) OVER() AS TotalRows FROM PermissionGroups'
            ]);
            $data = $Pagination->GetRows();
            $permissions = array();
            foreach ($data[0] as $row) {
                $permissions[] = [
                    'ID' => $row["ID"],
                    'Encrypted_ID' => BaseClass::Crypto($row["ID"]),
                    'Name' => $row["Name"],
                    'NiceDate' => $row["NiceDate"],
                ];
            }
            $this->View([
                'Rows' => $permissions,
                'Paging' => $data[1]
            ]);

        }

        //HTTP METHOD: GET
        public function SecurityLogs(): void {

            $Pagination = new Pagination([
                'Request' => $_GET,
                'Columns' => ['Subject', 'Message', 'AccountID', 'IP', 'Posted'],
                'Query' => 'SELECT *,DATE_FORMAT(Posted, "%m/%d/%y %I:%i %p") AS NiceDate, count(ID) OVER() AS TotalRows FROM SecurityLogs'
            ]);
            $data = $Pagination->GetRows();
            $logs = array();
            foreach ($data[0] as $row) {
                $logs[] = [
                    'Subject' => $row["Subject"],
                    'Message' => $row["Message"],
                    'AccountID' => BaseClass::Crypto($row["AccountID"]),
                    'IP' => $row["IP"],
                    'NiceDate' => $row["NiceDate"]
                ];
            }
            $this->View([
                'Rows' => $logs,
                'Paging' => $data[1]
            ]);
        }

        public function ErrorLogs(): void {

            $Pagination = new Pagination([
                'Request' => $_GET,
                'Columns' => ['Message', 'Exception', 'IP', 'Posted'],
                'Query' => 'SELECT *,DATE_FORMAT(Posted, "%m/%d/%y %I:%i %p") AS NiceDate, count(ID) OVER() AS TotalRows FROM ErrorLogs'
            ]);
            $data = $Pagination->GetRows();
            $this->View([
                'Rows' => $data[0],
                'Paging' => $data[1]
            ]);
        }

        public function ViewPendingInvites(): void {

            $Pagination = new Pagination([
                'Request' => $_GET,
                'Columns' => ['ID', 'Email', 'Code', 'Posted'],
                'Query' => 'SELECT RegistrationCodes.*,Name AS PermissionName,DATE_FORMAT(RegistrationCodes.Posted, "%m/%d/%y %I:%i %p") AS NiceDate, count(RegistrationCodes.ID) OVER() AS TotalRows FROM RegistrationCodes INNER JOIN PermissionGroups PG on RegistrationCodes.PermissionGroupID = PG.ID'
            ]);
            $data = $Pagination->GetRows();
            $invites = array();
            foreach ($data[0] as $row) {
                $invites[] = [
                    'ID' => BaseClass::Crypto($row["ID"]),
                    'Code' => $row["Code"],
                    'Email' => BaseClass::Crypto($row["Email"], 'd'),
                    'PermissionName' => $row["PermissionName"],
                    'NiceDate' => $row["NiceDate"],
                    'PermissionGroupID' => $row["PermissionGroupID"],
                ];
            }

            $this->View([
                'Rows' => $invites,
                'Paging' => $data[1]
            ]);
        }

        public function CreateInvite(): void {
            $this->View([
                'Permissions' => Authentication::GetPermissionGroups()
            ]);
        }

        public function RegistrationSettings(): void {
            $this->View([
                'Settings' => Security::GetSettings('Registration')
            ]);

        }

        public function UpdateRegistrationSettings() {
            $data = $_POST;
            try {
                $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `RegistrationSettings` SET `RequireRegistrationCode`=:RequireRegistrationCode WHERE `ID`=:ID LIMIT 1");
                $smt->execute([
                    ':ID' => 1,
                    ':RequireRegistrationCode' => $data["RequireRegistrationCode"]
                ]);
                $this->JSON(['Result' => 'true', 'PopupMsg' => 'Settings updated']);
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to update Registration Settings',
                    'Exception' => $e
                ]);
                $this->JSON(['Result' => 'false', 'PopupMsg' => 'Failed to update']);
            }
        }


        public function Firewalls(): void {
            $Pagination = new Pagination([
                'Request' => $_GET,
                'Columns' => ['ID', 'Registration', 'Login', 'Payment', 'Support', 'IP', 'Reason', 'Expires', 'Logged'],
                'Query' => 'SELECT *,DATE_FORMAT(Logged, "%m/%d/%y %I:%i %p") AS NiceDate,DATE_FORMAT(Expires, "%m/%d/%y") AS NiceExpires, count(ID) OVER() AS TotalRows FROM Blacklist'
            ]);
            $data = $Pagination->GetRows();
            $list = array();
            foreach ($data[0] as $row) {
                $list[] = [
                    'ID' => BaseClass::Crypto($row["ID"]),
                    'Registration' => $row["Registration"],
                    'Login' => $row["Login"],
                    'Payment' => $row["Payment"],
                    'Support' => $row["Support"],
                    'IP' => $row["IP"],
                    'Reason' => $row["Reason"],
                    'Expires' => $row["NiceExpires"],
                    'NiceDate' => $row["NiceDate"],
                ];
            }
            $this->View([
                'Rows' => $list,
                'Paging' => $data[1]
            ]);
        }

        public function FirewallCreation(): void {
            if (isset($_GET['ref'])) {
                $this->View(['Target' => $_GET['ref'], 'data' => Security::GetBlacklistData(BaseClass::Crypto($_GET['ref'], 'd'))]);
            } else {
                $this->View();
            }

        }

        public function TemplateManager(): void {
            $this->View([
                "Templates" => TemplateManager::FetchAll()
            ]);
        }

        public function PageEditor(): void {
            $this->View([
                "Templates" => TemplateManager::FetchAll()
            ]);
        }

        public function SendInvitationEmail(): void {
            $Admin = new Admin(Authentication::GetAccountID());
            $createInvitation = $Admin->Invite([
                'Identity' => $_POST['Identity'],
                'Email' => BaseClass::Crypto($_POST['InvitationEmail']),
                'PermissionGroup' => $_POST['PermissionGroup'],
            ]);
            if ($createInvitation == 1) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Invitation Sent', 'Redirect' => '/account/admin/?action=ViewPendingInvites']);
            } elseif ($createInvitation == 2) {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Account with Email exist']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }

        }

        public function RemoveInvite(): void {
            $Admin = new Admin(Authentication::GetAccountID());
            $RemoveInvite = $Admin->RemoveInvite([
                'ID' => Filters::NumberFilter(BaseClass::Crypto($_POST['RemoveInvite'], 'd')),
            ]);
            if ($RemoveInvite) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Invitation Removed', 'Reload' => true]);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }

        }

        public function ResendInvite(): void {
            $Admin = new Admin(Authentication::GetAccountID());
            $ResendInvite = $Admin->ResendInvite([
                'ID' => Filters::NumberFilter(BaseClass::Crypto($_POST['ResendInvite'], 'd')),
            ]);
            if ($ResendInvite == 1) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Invitation Resent', 'Reload' => false]);
            } elseif ($ResendInvite == 2) {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Account with Email exist']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }

        }



        public function RemovePermission(): void {
            $Admin = new Admin(Authentication::GetAccountID());
            $RemoveInvite = $Admin->RemovePermissionGroup([
                'ID' => Filters::NumberFilter(BaseClass::Crypto($_POST['RemovePermission'], 'd')),
            ]);
            if ($RemoveInvite) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Group Removed', 'Redirect' => '/account/admin/?action=ViewPermissionGroups']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }

        }

        public function AddToBlacklist(): void {
            $Security = new Security();
            $add = $Security->AddToBlackList([
                'Blacklist' => $_POST['Blacklist'],
                'Reason' => $_POST['Reason'],
                'LoginAccess' => $_POST['LoginAccess'],
                'RegistrationAccess' => $_POST['RegistrationAccess'],
                'PaymentAbility' => $_POST['PaymentAbility'],
                'SupportAbility' => $_POST['SupportAbility'],
                'Expires' => $_POST['Expires']
            ]);
            if ($add) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Rule created', 'Redirect' => '/account/admin/?action=Firewall']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }
        }

        public function UpdateBlacklist(): void {
            $Security = new Security();
            $add = $Security->UpdateBlacklist([
                'ID' => BaseClass::Crypto($_POST['UpdateBlacklist'], 'd'),
                'IP' => $_POST['IP'],
                'Reason' => $_POST['Reason'],
                'LoginAccess' => $_POST['LoginAccess'],
                'RegistrationAccess' => $_POST['RegistrationAccess'],
                'PaymentAbility' => $_POST['PaymentAbility'],
                'SupportAbility' => $_POST['SupportAbility'],
                'Expires' => $_POST['Expires']
            ]);
            if ($add) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Rule updated']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }
        }


        public function RemoveBlacklistRule(): void {
            $Security = new Security();
            $remove = $Security->RemoveBlacklistRule([
                'ID' => BaseClass::Crypto($_POST['RemoveBlacklistRule'], 'd'),
            ]);
            if ($remove) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Rule removed', 'Redirect' => '/account/admin/?action=Firewall']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }
        }

        public function RequireRegistrationCode(): void {
            $Security = new Security();
            $update = $Security->UpdateRegistrationSettings([
                'ID' => 1,
                'RequireRegistrationCode' => $_POST['RequireRegistrationCode'],
            ]);
            if ($update) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Settings updated']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }
        }

        public function GetTemplate(): void {

            $path = $_POST['Path'];
            $TemplateManager = new TemplateManager(
                $path
            );
            $result = $TemplateManager->GetTemplate();
            echo is_array($result) ? json_encode($result) : $result;
        }

        public function SaveTemplate(): void {

            $path = $_POST['Path'];
            $template = $_POST['Template'];

            $TemplateManager = new TemplateManager($path, $template);
            echo json_encode($TemplateManager->SaveTemplate());
        }


    }

















