<?php
    class ManageController extends Controller {
        public function __construct() {

        }

        public function Index() {
            $this->view();
        }

        public function AccountDetails() {
            $Account = new Account(Authentication::GetAccountID());
            $this->view([
                'Name' => $Account->GetIdentity(),
                'Email' => $Account->GetEmail(),
                'Phone' => $Account->GetPhone(),
                'Settings' => $Account->GetSettings()
            ]);
        }

        public function AddressUpdate() {
            $Account = new Account(Authentication::GetAccountID());
            $data = [];
            if(isset($_GET['ref'])){
                $data = array_merge($Account->GetSpecificAddressRecord($_GET['ref']),['ref'=>$_GET['ref']]);
            }
            $this->view($data);
        }

        public function AddressBook() {

            $Account = new Account(Authentication::GetAccountID());
            $addresses = array();
            foreach ($Account->GetAllAddresses() as $row) {
                $addresses[] = [
                    'ID' => BaseClass::Crypto($row["ID"], 'e'),
                    'FullName' => $row["FullName"],
                    'AddressOne' => $row["AddressOne"],
                    'AddressTwo' => $row["AddressTwo"],
                    'City' => $row["City"],
                    'State' => $row["State"],
                    'Zipcode' => $row["Zipcode"],
                    'Phone' => $row["Phone"],
                    'Country' => $row["Country"],
                    'PrimaryAddress' => $row["PrimaryAddress"],
                    'CompanyName' => $row["CompanyName"],

                ];
            }

            $this->view(['Addresses' => $addresses]);
        }

        public function MyDocuments() {
            $e_AccID = BaseClass::Crypto(Authentication::GetAccountID());

            $this->view([
                'Documents' => Files::GetFiles(sprintf("Assets/Users/%s/Documents", Account::GetDirectory($e_AccID))),
            ]);
        }

        public function MyLoginHistory() {
            $Pagination = new Pagination([
                'Request' => $_GET,
                'Columns' => ['Accounts.AccountID','IP','Metadata','Logged'],
                'Query' => 'SELECT *,DATE_FORMAT(Logged, "%m/%d/%y %I:%i %p") AS NiceDate, count(ID) OVER() AS TotalRows FROM LoginHistory',
                'AccountID' => $this->AccountID
            ]);
            $data = $Pagination->GetRows();
            $this->view(['Rows' => $data[0], 'Paging' => $data[1]]);
        }


        public function SendVerificationCode() {
            $Account = new Account(Authentication::GetAccountID());
            $type = $_POST['Type'];
            if ($type === 'email') {
                $Code = $Account->SendClientEmailVerificationCode($_POST['Target']);
                if ($Code != false) {
                    $this->JSON(['Result' => true]);
                }else{
                    $this->JSON(['Result' => false]);
                }
            } if ($type === 'phone') {
                $Code = $Account->SendClientSMSVerificationCode($_POST['Target']);
                if ($Code != false) {
                    $this->JSON(['Result' => true]);
                }else{
                    $this->JSON(['Result' => false]);
                }
            }

        }

        public function VerifyClientCode() {
            if (isset($_SESSION['ClientVerificationCode'])) {
                if (Filters::NumberFilter($_POST['ClientCode']) == $_SESSION['ClientVerificationCode']) {
                    $this->JSON(['Result' => true, 'Reload'=>true]);
                } else {
                    $this->JSON(['Result' => false]);
                }
            } else {
                $this->JSON(['Result' => false]);
            }

        }

        public function UpdateAccountLoginInfo() {
            $Account = new Account(Authentication::GetAccountID());
            $Errors = [];
            if (!isset($_SESSION['ClientVerificationCode']))
                $_SESSION['ClientVerificationCode'] = '777';

            $email = $_POST['AccountEmail'];
            if ($_POST['AccountEmail'] != $Account->GetEmail()) {
                if ($_POST['VerificationCode'] != $_SESSION['ClientVerificationCode']) {
                    $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred', 'Reload' => false]);
                    return false;
                } else {
                    $Account->UpdateEmail(BaseClass::Crypto($_POST['AccountEmail']));
                    unset($_SESSION['ClientVerificationCode']);
                }
            }
            $identityParts = explode(' ', $_POST['AccountName']);
            $firstName = $identityParts[0];
            if (count($identityParts) > 1) {
                $lastName = $identityParts[count($identityParts) - 1];
            } else {
                $lastName = '';
            }

            if (!$Account->UpdatePhone(BaseClass::Crypto($_POST['AccountPhone']))) {
                $Errors[] = 'The new phone number you provided is already associated with another account therefore, it cannot be used for logging in or for verification purposes.';
            }
            if (isset($_POST['AccountPassword'])) {
                if (!empty($_POST['AccountPassword'])) {
                    $Account->UpdatePassword(BaseClass::Crypto($_POST['AccountPassword']));
                }
            }

            if (isset($_POST['2FAMethod'])) {
                $Account->UpdateSetting([
                    "Setting" => '2FAMethod',
                    "Value" => $_POST['2FAMethod']
                ]);
            }

            $Account->UpdateContactInfo([
                'Identifier' => $_POST['AccountName'],
                'FirstName' => $firstName,
                'LastName' => $lastName
            ]);

            $hasErrors = Count($Errors) != 0;
            $response = $hasErrors ? "Partially updated" : "Updated";

            $this->JSON(['Result' => !$hasErrors, 'PopupMsg' => $response, 'Reload' => false, 'Errors' => $Errors]);
        }

        public function AddAddress() {
            $Account = new Account(Authentication::GetAccountID());
            $contactAdd = $Account->AddAddressBookInfo([
                'FullName' => isset($_POST['FullName']) ? $_POST['FullName'] : null,
                'CompanyName' => isset($_POST['CompanyName']) ? $_POST['CompanyName'] : null,
                'AddressOne' => isset($_POST['AddressOne']) ? $_POST['AddressOne'] : null,
                'AddressTwo' => isset($_POST['AddressTwo']) ? $_POST['AddressTwo'] : null,
                'City' => isset($_POST['City']) ? $_POST['City'] : null,
                'State' => isset($_POST['State']) ? $_POST['State'] : null,
                'Zip' => isset($_POST['Zip']) ? $_POST['Zip'] : null,
                'Phone' => isset($_POST['Phone']) ? $_POST['Phone'] : null,
                'PrimaryAddress' => 0,
            ]);

            if ($contactAdd) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Address Added', 'Redirect' => '/addressBook']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }

        }

        public function UpdateAddress() {
            $Account = new Account(Authentication::GetAccountID());
            $UpdateAddress = $Account->UpdateAddressBookInfo([
                'FullName' => isset($_POST['FullName']) ? $_POST['FullName'] : null,
                'CompanyName' => isset($_POST['CompanyName']) ? $_POST['CompanyName'] : null,
                'AddressOne' => isset($_POST['AddressOne']) ? $_POST['AddressOne'] : null,
                'AddressTwo' => isset($_POST['AddressTwo']) ? $_POST['AddressTwo'] : null,
                'City' => isset($_POST['City']) ? $_POST['City'] : null,
                'State' => isset($_POST['State']) ? $_POST['State'] : null,
                'Zip' => isset($_POST['Zip']) ? $_POST['Zip'] : null,
                'Phone' => isset($_POST['Phone']) ? $_POST['Phone'] : null,
                'ID' => Filters::NumberFilter(BaseClass::Crypto($_POST['UpdateAddress'], 'd')),
            ]);

            if ($UpdateAddress) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Address Updated']);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }

        }



        public function MakePrimaryAddress() {
            $Account = new Account(Authentication::GetAccountID());
            $MakePrimaryAddress = $Account->SetAddressAsPrimary([
                'ID' => Filters::NumberFilter(BaseClass::Crypto($_POST['MakePrimaryAddress'], 'd')),
            ]);

            if ($MakePrimaryAddress) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Success', 'Reload' => true]);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }


        }

        public function RemoveAddress() {
            $Account = new Account(Authentication::GetAccountID());
            $RemoveAddress = $Account->RemoveAddress([
                'ID' => Filters::NumberFilter(BaseClass::Crypto($_POST['RemoveAddress'], 'd')),
            ]);

            if ($RemoveAddress) {
                $this->JSON(['Result' => true, 'PopupMsg' => 'Success', 'Reload' => true]);
            } else {
                $this->JSON(['Result' => false, 'PopupMsg' => 'Unknown error occurred']);
            }

        }

        public function VerifyUniqueness() {
            $Account = new Account(Authentication::GetAccountID());
            if ($_POST['VerifyUniqueness'] === 'email') {
                Authentication::IsUniqueEmail(Baseclass::Crypto($_POST['Target'])) ? $this->JSON(['Result' => true]) : $this->JSON(['Result' => false]);
            } else {
                Authentication::IsUniquePhone(Baseclass::Crypto($_POST['Target'])) ? $this->JSON(['Result' => true]) : $this->JSON(['Result' => false]);
            }
        }



    }

?>

