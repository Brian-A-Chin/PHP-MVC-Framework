<?php

    /* ALL RETURN TYPES MUST BE AN ARRAY*/

    use JetBrains\PhpStorm\ArrayShape;
    use JetBrains\PhpStorm\Pure;

    class Explorer extends Account {

        private ?array $GET = null;
        #[Pure] public function __Construct($AccountID, $GET) {
            $this->GET = $GET;
            parent::__construct($AccountID);
        }

        public function GetAllAccounts($results): array | bool {
            $data = array();
            if(!empty($results)){
                $accountStates = AccountDeclarations::GetAccountState(false);
                foreach ($results as $row) {
                    $data[] = [
                        'AccountID' => BaseClass::Crypto($row["AccountID"]),
                        'PermissionGroup' => $row["PermissionName"],
                        'Identifier' => $row["Identifier"],
                        'Phone' => $row["Phone"],
                        'Email' => BaseClass::Crypto($row["Email"],'d'),
                        'Status' => array_search($row["Status"],$accountStates),
                        'Verified' => $row["Verified"],
                        'NiceDate' => $row["NiceDate"],
                    ];
                }
                return $data;
            }else{
                BaseClass::LogError([
                    'Message' => 'Failed to get all accounts',
                    'Exception' => 'array of rows is empty. Pagination requires rows inorder to get all accounts'
                ]);
                return false;
            }
        }


    }