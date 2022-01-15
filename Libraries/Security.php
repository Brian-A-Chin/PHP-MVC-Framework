<?php


    class Security{

        public function __construct(){

        }

        public function AddToBlackList( $data): bool {
            $insertValues = array();
            $placeHolders = array();
            $AccountID = Authentication::GetAccountID();
            foreach(explode(',',$data['Blacklist']) as $IP){
                if(strlen($IP) == 0)
                    continue;

                $insertValues[] = $IP;
                $insertValues[] = $data['Reason'];
                $insertValues[] = $data['LoginAccess'];
                $insertValues[] = $data['RegistrationAccess'];
                $insertValues[] = $data['PaymentAbility'];
                $insertValues[] = $data['SupportAbility'];
                $insertValues[] = $data['Expires'];
                $insertValues[] = $AccountID;
                $placeHolders[] = "(?,?,?,?,?,?,?,?)";
            }
            $query = "INSERT INTO `Blacklist` (`IP`, `Reason`,`Login`,`Registration`,`Payment`,`Support`,`Expires`,`AccountID`) VALUES".implode(',',$placeHolders);
            try {
                $smt = SQLServices::MakeCoreConnection()->prepare($query);
                $smt->execute($insertValues);
                return true;
            }catch (Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to add to BlackList',
                    'Exception' => $e
                ]);
                return false;
            }

        }

        public function UpdateBlacklist ( $data ): bool
        {
            try{
                $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `Blacklist` SET `Registration`=:Registration,`Login`=:Login,`Payment`=:Payment,`Support`=:Support,`IP`=:IP ,`Reason`=:Reason ,`Expires`=:Expires WHERE `ID`=:ID LIMIT 1");
                $smt->execute([
                    ':ID' => $data["ID"],
                    ':Registration' => $data["RegistrationAccess"],
                    ':Login' => $data["LoginAccess"],
                    ':Payment' => $data["PaymentAbility"],
                    ':Support' => $data["SupportAbility"],
                    ':IP' => $data["IP"],
                    ':Reason' => $data["Reason"],
                    ':Expires' => $data["Expires"],
                ]);
                return true;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to update Blacklist Group',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        public function RemoveBlacklistRule( $data ): bool {
            try {
                SQLServices::MakeCoreConnection()->prepare("DELETE FROM `Blacklist` WHERE ID=?")->execute([$data['ID']]);
                return true;
            } catch (Exception $e) {
                BaseClass::LogError([
                    'Message' => 'Failed to delete from Blacklist',
                    'Exception' => $e
                ]);
                return false;
            }
            return false;
        }

        public static function GetBlacklistData( $ID ): array | bool{
            try {
                $smt = SQLServices::MakeCoreConnection()->prepare("SELECT * FROM `Blacklist` WHERE ID=?");
                $smt->execute([$ID]);
                return $smt->fetch(PDO::FETCH_ASSOC);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to fetch Blacklist Data',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        public static function GetSettings($type): array | bool{
            $query = match ($type) {
                'Registration' => 'SELECT * FROM RegistrationSettings WHERE ID=1',
                default => null,
            };
            try{
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute();
                return $statement->fetch(PDO::FETCH_ASSOC);
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to get '.$type.' settings ',
                    'Exception' => $e
                ]);
                return false;
            }
        }

        public function UpdateRegistrationSettings ( $data ): bool
        {
            try{
                $smt = SQLServices::MakeCoreConnection()->prepare("UPDATE `RegistrationSettings` SET `RequireRegistrationCode`=:RequireRegistrationCode WHERE `ID`=:ID LIMIT 1");
                $smt->execute([
                    ':ID' => $data["ID"],
                    ':RequireRegistrationCode' => $data["RequireRegistrationCode"]
                ]);
                return true;
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to update Registration Settings',
                    'Exception' => $e
                ]);
                return false;
            }
        }

    }