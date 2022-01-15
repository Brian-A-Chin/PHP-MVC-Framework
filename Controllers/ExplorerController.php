<?php

    class ExplorerController extends Controller {

        public function __construct() {

        }

        public function Index() {
            $Explorer = new Explorer(Authentication::GetAccountID(), $_GET);
            $Pagination = new Pagination([
                'Request' => $_GET,
                'Columns' => ['Accounts.AccountID','Identifier','Email','Phone','Status','Verified','Name','JoinDate'],
                'Query' => 'SELECT count(Accounts.AccountID) OVER() AS TotalRows,Accounts.AccountID AS AccountID,Verified,ContactRecords.Identifier,Accounts.PermissionGroupID,Name AS PermissionName, Phone,Email,Status,DATE_FORMAT(JoinDate, "%m/%d/%y") AS NiceDate FROM ContactRecords INNER Join Accounts ON ContactRecords.AccountID=Accounts.AccountID INNER JOIN PermissionGroups PG on Accounts.PermissionGroupID = PG.ID'
            ]);

            $fetchRows = $Pagination->GetRows();
            if($fetchRows != false) {
                $data = $Explorer->GetAllAccounts($fetchRows[0]);
                $this->View([
                    'Rows' => $data,
                    'MyAccEID' => Authentication::GetAccountID(true),
                    'Paging' => $fetchRows[1]]
                );
            }


        }
    }