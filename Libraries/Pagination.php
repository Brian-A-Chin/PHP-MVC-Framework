<?php

    use JetBrains\PhpStorm\Pure;

    class Pagination {

        private int $CurrentPage = 1;
        private int $PageSize = 10;
        private ?int $AccountID = -1;
        private string $Query;
        private ?string $StartRange = null;
        private ?string $EndRange = null;
        private ?string $DateColumn = null;
        private ?string $Category = null;
        private ?string $Search = null;
        private ?string $Sort = null;
        private array $Columns;
        private bool $PrecisionSearch = false;

        public function __construct( $Params ){
            $this->Columns = $Params['Columns'];
            $this->Query = $Params['Query'];
            if(isset($Params['Request']['CurrentPage'])){
                $this->CurrentPage = Filters::NumberFilter($Params['Request']['CurrentPage']);
            }


            if(isset($Params['Request']['PageSize'])){
                $this->PageSize = Filters::NumberFilter($Params['Request']['PageSize']);
            }

            if(isset($Params['Request']['StartDate'])){
                $this->StartRange = Filters::DateFilter($Params['Request']['StartDate']);
            }


            if(isset($Params['Request']['EndDate'])){
                $this->EndRange = Filters::DateFilter($Params['Request']['EndDate']);
            }

            if(isset($Params['Request']['DateColumn'])){
                $this->DateColumn = Filters::AlphaFilter($Params['Request']['DateColumn']);
            }


            if(isset($Params['Request']['Category'])){
                $this->Category = Filters::AlphaFilter($Params['Request']['Category']);
            }


            if(isset($Params['Request']['Search'])){
                $this->Search = Filters::SearchFiler($Params['Request']['Search']);
            }


            if(isset($Params['Request']['Sort'])){
                $this->Sort = Filters::SortFilter($Params['Request']['Sort']);
            }


            if(isset($Params['AccountID'])){
                $this->AccountID = Filters::NumberFilter($Params['AccountID']);
            }
        }


        public function GetSort() : string{
            $sortString = '';
            if($this->Sort != null){
                $sortParams = explode(':',$this->Sort);
                $i = 0;
                foreach ($sortParams as $key => $value){
                    if ($i % 2 == 0) {
                        if(in_array($value, $this->Columns)) {
                            $sortString .= $value . ' ';
                        }else{
                            //Just exit. Action would be intentional manipulation
                            return false;
                        }
                    }else{
                        $sortString .= $value === 'ASC' || $value === 'DESC' ? $value : 'DESC';
                        if(($i+1) < count($sortParams)) {
                            $sortString .= ',';
                        }else{
                            $sortString .= ' ';
                        }
                    }
                    $i++;
                }
                return $sortString;
            }
            return false;
        }

        #[Pure] public static function FindBestKeyMatch($data, $Singular = false) {
            $max = 0;
            $index = 0;
            foreach ($data['Array'] as $key => $value) {
                $target = $Singular ? $value : $key;
                $comp = array_intersect(str_split(strtolower($data['Term'])), str_split(strtolower($target)));
                if(count($comp) > $max){
                    $index = $value;
                    $max = count($comp);
                }
            }
            return $index;
        }

        private function SmartConvert(): bool {
            $category = strtolower($this->Category);
            if(strtolower($category) === 'accountid'){
                if(is_numeric($this->Search)) {
                    $this->PrecisionSearch = true;
                    $this->Category = $this->FindBestKeyMatch([
                        'Array' => $this->Columns,
                        'Term' => $this->Category
                    ],true);
                }
            }else if(strtolower($category) === 'status'){
                if(!is_numeric($this->Search)) {
                    $this->PrecisionSearch = true;
                    $this->Search = $this->FindBestKeyMatch([
                        'Array' => AccountDeclarations::GetAccountState(false),
                        'Term' => $this->Search
                    ]);
                }
            }else if(in_array(strtolower($category),['email'])){
                $this->Search = BaseClass::Crypto($this->Search,'e');
            }
            return true;
        }

        public function GetRows(): bool|array {
            $paging = array();
            $query = $this->Query;
            if ($query != false) {
                $isInnerJoining = str_contains($query, ' JOIN ') == true;
                    try {

                        //Attempts to determine a possible datetime column for sorting
                        if($this->Search != null){
                            if(in_array($this->Category,$this->Columns) || stripos($this->Category,'ID') > 0){
                                if($this->SmartConvert() != false) {
                                    $query .= strpos($this->Category, '.') !== false ? " WHERE ( " . $this->Category : " WHERE ( `" . $this->Category . "`";
                                    if ($this->PrecisionSearch) {
                                        if (is_numeric($this->Search)) {
                                            $query .= "=" . $this->Search . " ";
                                        } else {
                                            $query .= "='" . $this->Search . "' ";
                                        }
                                    } else {
                                        $query .= " LIKE '%" . $this->Search . "%' ";
                                    }
                                    $query .= ") ";
                                }
                            }
                        }

                        if($this->StartRange != null){
                            $query .=  $this->Search != null ? " AND " : " WHERE ";
                            $query .= "(`".$this->DateColumn."` BETWEEN '".$this->StartRange."' AND '".$this->EndRange."') ";
                        }

                        if($this->AccountID != -1){
                            $query .= $this->StartRange == null && $this->Search == null ? ' WHERE ' : 'AND ';
                            if($isInnerJoining){
                                //Determines a unique column to filter AccountID on
                                $column = $this->FindBestKeyMatch([
                                    'Array' => $this->Columns,
                                    'Term' => '.'
                                ],true);

                                $query .= '('.$column.'='.$this->AccountID.') ';
                            }else{
                                $query .= '(AccountID='.$this->AccountID.') ';
                            }
                        }

                        if ($this->Sort != null) {
                            $query .= " ORDER BY " . $this->GetSort();
                        }else{
                            //Attempts to determine a possible datetime column for sorting
                            $dateColumn = $this->FindBestKeyMatch([
                                'Array' => $this->Columns,
                                'Term' => 'datepostedlogged'
                            ],true);

                            $query .= " ORDER BY " . $dateColumn. " DESC";
                        }

                        $query .= " limit " . (($this->CurrentPage - 1) * $this->PageSize) . ',' . $this->PageSize;
                        $conn = SQLServices::MakeCoreConnection();
                        $statement = $conn->prepare($query);
                        $statement->execute();
                        $result = $statement->fetchAll();
                        $pageSize = $statement->rowCount();
                        if($pageSize > 0) {
                            $paging = [
                                'CurrentPage' => $this->CurrentPage,
                                'PageSize' => $pageSize,
                                'TotalRows' => $result[0]["TotalRows"],
                                'TotalPages' => round($result[0]["TotalRows"] / $this->PageSize) > 0 ? Ceil($result[0]["TotalRows"] / $this->PageSize) : 1 ,
                                'RowStartAt' => (($this->CurrentPage - 1) * $this->PageSize + 1) == 0 ? 1 : (($this->CurrentPage - 1) * $this->PageSize + 1),
                                'RowEndAt' => ($this->CurrentPage * $pageSize),
                                'PageStartAt' => ($this->CurrentPage - 2) >= 1 ? $this->CurrentPage - 2 : 1,
                                'PageEndAt' => ($this->CurrentPage + 2 > round($result[0]["TotalRows"] / $this->PageSize)) ? Ceil($result[0]["TotalRows"] / $this->PageSize) : $this->CurrentPage + 2
                            ];
                        }
                        return [$result, $paging];
                } catch (Exception $e) {
                    BaseClass::LogError([
                        'Message' => 'Failed to get rows',
                        'Exception' => $e
                    ]);
                    return false;
                }
            }else{
                BaseClass::LogError([
                    'Message' => 'Specified query name does not exist',
                    'Exception' => $this->Query
                ]);
                return false;
            }
        }

    }