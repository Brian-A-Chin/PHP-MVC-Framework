<?php
    class SetupController extends Controller {
        public function __construct() {

        }

        public function index(){

            try {
                $query = 'SELECT Message FROM ErrorLogs WHERE ID=1 LIMIT 1';
                $statement = SQLServices::MakeTenantConnection()->prepare($query);
                $statement->execute();
                $test = $statement->fetch(PDO::FETCH_ASSOC)['Message'];
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to get Account PermissionGroupID',
                    'Exception' => $e
                ]);
                return false;
            }

            if($_GET['key'] == '1152'){
                $this->view([],'HelperAccountSetup');
            }


        }

        public function AddressOne() {
            $Account = new Account(Authentication::GetAccountID());
            //Add address information
            $contactAdd = $Account->AddAddressBookInfo([
                'FullName' => isset($_POST['FullName']) ? $_POST['FullName'] : null,
                'CompanyName' => isset($_POST['CompanyName']) ? $_POST['CompanyName'] : null,
                'AddressOne' => isset($_POST['AddressOne']) ? $_POST['AddressOne'] : null,
                'AddressTwo' => isset($_POST['AddressTwo']) ? $_POST['AddressTwo'] : null,
                'City' => isset($_POST['City']) ? $_POST['City'] : null,
                'State' => isset($_POST['State']) ? $_POST['State'] : null,
                'Zip' => isset($_POST['Zip']) ? $_POST['Zip'] : null,
                'Phone' => isset($_POST['Phone']) ? $_POST['Phone'] : null,
                'PrimaryAddress' => 1,
            ]);


            //What makes a successful account setup?
            if($contactAdd){
                $Account->CompleteSetup();
                echo json_encode(['Result'=>true,'PopupMsg'=>'Setup successful','Reload'=>true]);
            }else{
                echo json_encode(['Result'=>false,'PopupMsg'=>'Unknown error occurred']);
            }
            return false;
        }

    }



