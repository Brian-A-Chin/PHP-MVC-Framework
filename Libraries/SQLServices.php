<?php
class SQLServices{

    public static function GetDBKN() : string | bool{
        try {
            $query = 'SELECT DBKN FROM Accounts WHERE AccountID=? LIMIT 1';
            $statement = SQLServices::MakeCoreConnection()->prepare($query);
            $statement->execute([Authentication::GetAccountID()]);
            return $statement->fetch(PDO::FETCH_ASSOC)['DBKN'];
        } catch (Exception $e) {
            BaseClass::LogError([
                'Message' => 'Failed to get DBKN',
                'Exception' => $e
            ]);
            return false;
        }
    }

    public static function MakeCoreConnection(): PDO {
        try{
            return new PDO(
                'mysql:host='.DB_HOST.';dbname='.DB_NAME,
                DB_USER, 
                DB_PASSWORD
            );
        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }


    public static function MakeTenantConnection(): PDO {

        $DBKN = SQLServices::GetDBKN();
        try{
            return new PDO(
                'mysql:host='.DB_HOST.';dbname='.$DBKN,
                DB_USER,
                DB_PASSWORD
            );
        } catch (PDOException $e) {
            die($e->getMessage());
        }


    }
    
}

