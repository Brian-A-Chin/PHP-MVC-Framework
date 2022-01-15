<?php



    class Multitenancy {
        private  string $DatabaseName;

        public function __Construct($DatabaseName){
            $this->DatabaseName = $DatabaseName;
        }

        public function CreateDatabase() : bool{

            try{
                $query = sprintf("CREATE DATABASE %s",$this->DatabaseName);
                $statement = SQLServices::MakeCoreConnection()->prepare($query);
                $statement->execute();
            }catch(Exception $e){
                BaseClass::LogError([
                    'Message' => 'Failed to create database',
                    'Exception' => $e
                ]);
                return false;
            }

            return true;

        }

        public function FillNewDatabase(){

            $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, $this->DatabaseName) or die('Error connecting to MySQL Database: ' . mysqli_error());
            $tempLine = '';
            // Read in the full file
            $lines = file(ABSPATH . '/Assets/SYSTEM/Queries/Main.sql');
            // Loop through each line
            foreach ($lines as $line) {

                // Skip it if it's a comment
                if (substr($line, 0, 2) == '--' || $line == '')
                    continue;

                // Add this line to the current segment
                $tempLine .= $line;
                // If its semicolon at the end, so that is the end of one query
                if (substr(trim($line), -1, 1) == ';')  {
                    // Perform the query
                   try{
                       mysqli_query($link, $tempLine);
                   }catch (\mysql_xdevapi\Exception $ex){
                       BaseClass::LogError([
                           'Message' => 'Failed to add table to new database',
                           'Exception' => $ex
                       ]);
                       return false;
                   }
                    // Reset temp variable to empty
                    $tempLine = '';
                }
            }
            $link->close();
            return true;
        }




    }