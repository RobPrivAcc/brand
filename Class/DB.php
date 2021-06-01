 <?php
 class DB{
    
    /*
        0 - Petzone
        1 - Donaghmede
        2 - Petco
        3 - Charlestown
        4 - Swords
    */
    
    
    private $user = null;
    private $password = null;
    private $index;
    
    private $dbConnectionArray;
  
     function __construct($xmlArray){
    
      $this->user = $xmlArray['login']['user'];
      $this->password = $xmlArray['login']['password'];
      $array = $xmlArray['shop'];
      for($i=0; $i < count($array); $i++){
         $this->dbConnectionArray[$i] = array(
                'shopName' => $array[$i]['shopName'],
                'server' => 'sqlsrv:Server='.$array[$i]['external_IP'].',1317;Database='.$array[$i]['dbName'],
                'localServer' => 'sqlsrv:Server='.$array[$i]['internal_IP'].';Database='.$array[$i]['dbName'],// SANGOTILL3
                'user' => $this -> user,
                'password' => $this -> password,
                'shopNo' => $array[$i]['shop_No']
            );
      }
    }
   
    public function getMaxIndex(){
        return count($this -> dbConnectionArray);
    }
    
    public function getShopName(){
        return $this -> dbConnectionArray[$this -> index]['shopName'];
    }
    
    public function getShopNo(){
         return $this -> dbConnectionArray[$this -> index]['shopNo'];
    }

    public function getDbConnection($index){
        $this -> index = $index;
        if($this -> index > $this -> getMaxIndex()){
            return "Out of range. Max index =".count($this -> dbConnectionArray);
        }else{
            return $this -> dbConnectionArray[$this -> index];
        }
     }

     public function getShopsName(){
      $shopNameArray = "";
      
      for ($i = 0; $i < $this -> getMaxIndex(); $i++){
         $shopNameArray[] = array($this -> dbConnectionArray[$i]['shopName'],$this -> dbConnectionArray[$i]['shopNo']);
      }
      return $shopNameArray;
     }
}
?>