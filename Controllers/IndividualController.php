<?php
    class IndividualController extends Controller {
        public function __construct() {

        }

        public function Index() {

            $SelectedAccountID = Filters::NumberFilter(BaseClass::Crypto($_GET['account'],'D'));
            $UserAccount = new Account( $SelectedAccountID );

            $this->View([
                'Name' => $UserAccount->GetIdentity(),
                'Email' => $UserAccount->GetEmail(),
                'PrimaryAddress' => $UserAccount->GetPrimaryAddress()
            ]);
        }


        public function Privileges(): void{

            $this->View();
        }



        public function AccountPrivileges(): void {
            $SelectedAccountID = Filters::NumberFilter(BaseClass::Crypto($_GET['account'],'D'));
            $Account = new Account(Authentication::GetAccountID());
            $this->View([
                'AccountID' => $SelectedAccountID,
                'PermissionGroups'=> Authentication::GetPermissionGroups(),
                'CurrentPermissionGroup' => $Account->GetAccountPermissionGroupID($SelectedAccountID),
            ]);
        }

        public function DocumentsManager(): void {
            $SelectedAccountID = Filters::NumberFilter(BaseClass::Crypto($_GET['account'],'D'));
            $e_AccID = BaseClass::Crypto($SelectedAccountID);
            $this->View([
                'AccountID' => $SelectedAccountID,
                'Documents' => Files::GetFiles(sprintf("Assets/Users/%s/Documents",Account::GetDirectory($e_AccID))),
                'GET' => $_GET
            ]);
        }


        public function UpdatePrivileges() {
            $Individual = new Individual( Authentication::GetAccountID(),$_GET );
            if($Individual->UpdateAccountPrivileges([
                'AccountID' => $_POST['UpdatePrivileges'],
                'PermissionGroupID' => $_POST['PermissionGroupID']
            ])){
                $this->JSON(['Result'=>true,'PopupMsg'=>'Updated!']);
            }else{
                $this->JSON(['Result'=>false,'PopupMsg'=>'Failed to update']);
            }
        }

        public function ApprovedDocuments(){
            $account = BaseClass::Crypto($_POST['Account'],'d');
            $Files = new Files(
                $_POST['ApprovedDocuments'], //approved files
                $_FILES['UploadDocuments'], //working files
                ['Documents'], //folders
                Filters::NumberFilter($account),//account
            );
            $this->JSON($Files->Upload());
        }

        public function AccountSettings(){
            $Account = new Account(Authentication::GetAccountID());
            $this->View();
        }

        public function UpdateStatus(){
            $this->JSON(['Result' => true, 'PopupMsg' => 'status Updated']);
        }


    }
?>



