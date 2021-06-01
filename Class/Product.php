<?php

require dirname( __DIR__ ) . '\vendor\autoload.php';

class Product extends PDOException{
    
    private $pdo=null;
    private $petcoPDO = null;
    private $date = array();
    private $productArray = array();
    private $productSoldArray = array();
    private $yearsArray = array();
    private $brand = null;
    

    //creating connection string to petco to getallheaders Product list
    function __construct($petcoString){
        $petcoConnectionString = $petcoString;
        $this->date =  array(
                        array('year' => "All ".date("Y",mktime(0, 0, 0, 1, 1,   date("Y")-1)),
                              'dateStart' => date("Y-m-d",mktime(0, 0, 0, 1, 1,   date("Y")-1)),
                              'dateEnd' => date("Y-m-d",mktime(0, 0, 0, 12, 31,   date("Y")-1))),
                        array('year' => date("Y",mktime(0, 0, 0, 1, 1,   date("Y")-1)),
                              'dateStart' => date("Y-m-d",mktime(0, 0, 0, 1, 1,   date("Y")-1)),
                              'dateEnd' => date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"),   date("Y")-1))),
                        array('year' => date("Y",mktime(0, 0, 0, 1, 1,   date("Y"))),
                              'dateStart' => date("Y-m-d",mktime(0, 0, 0, 1, 1,   date("Y"))),
                              'dateEnd' => date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"),   date("Y"))))

                    );
       
        try{
            //$this->petcoPDO  = new PDO("sqlsrv:Server=86.47.51.83,1317;Database=petshoptest","sa","SMITH09ALPHA"); // charlestown db test
            $this->petcoPDO = new PDO($petcoConnectionString["server"],$petcoConnectionString["user"],$petcoConnectionString["password"]);
        }catch(Exception $e){
            //$this->petcoPDO  = new PDO("sqlsrv:Server=Server=192.168.1.2\SQLEXPRESS;Database=petshoptest","sa","SMITH09ALPHA");
            $this->petcoPDO = new PDO($petcoConnectionString["localServer"],$petcoConnectionString["user"],$petcoConnectionString["password"]);
        }
        
    }
    
    function openConnection($dbConnectionArray){
           
            try{
                
                $this->pdo = new PDO($dbConnectionArray["server"],$dbConnectionArray["user"],$dbConnectionArray["password"]); 
            }
            catch (PDOException $e){
               // var_dump($e);
                $this->pdo = new PDO($dbConnectionArray["localServer"],$dbConnectionArray["user"],$dbConnectionArray["password"]);
            }
    }
    
   //function openConnection($dbConnectionArray,$storeOrAway,$storeIndex){
   //     try{
   //         $this->pdo = new PDO($dbConnectionArray["localServer"],$dbConnectionArray["user"],$dbConnectionArray["password"]);
   //         
   //         try{
   //             $this->pdo = new PDO($dbConnectionArray["server"],$dbConnectionArray["user"],$dbConnectionArray["password"]);
   //         }
   //         catch(Exception ex){
   //             
   //         }
   //     }
   //     catch(){
   //         
   //     }
   // 
   // 
   //     if($storeOrAway == 99){
   //         $this->pdo = new PDO($dbConnectionArray["server"],$dbConnectionArray["user"],$dbConnectionArray["password"]);
   //     }else{
   //         if($storeOrAway == $storeIndex){
   //             $this->pdo = new PDO($dbConnectionArray["localServer"],$dbConnectionArray["user"],$dbConnectionArray["password"]);
   //         }else{
   //             $this->pdo = new PDO($dbConnectionArray["server"],$dbConnectionArray["user"],$dbConnectionArray["password"]);
   //         }
   //     }
   // }
    
    function excludeEmptyBrands($brand){
        $sql = "select count(*) as totalBrandProducts FROM Stock Where Discontinued = 0 and Manufacturer ='".$brand."';";
        $query = $this->petcoPDO->prepare($sql);
        $query->execute();
        
        $rows = $query->fetchColumn();
        return $rows;
    }
    
    function brandList(){
        
        $option = "";
        
        $sql = "SELECT DISTINCT([Manufacturer]) FROM [Stock] WHERE Discontinued = 0 ORDER BY [Manufacturer] ASC";
        
        $query = $this->petcoPDO->prepare($sql);
        $query->execute();
        
        while($row = $query->fetch()){
           // if($this->excludeEmptyBrands($row['Manufacturer']) != 0){
                $option .= "<option>".$row['Manufacturer']."</option>";    
            //}
            
        }
        
        return $option;
    }
    
    function createProducts($brand){
        
        $this->brand = $brand;
        
        $sql = "SELECT [Name of Item], ([Supplier Cost] - ([Supplier Cost] * ([SuppDis1]/100))) as 'Cost', [Selling Price]
                    FROM Stock
                    WHERE Discontinued = 0 AND Manufacturer = '".$this->brand."'
                        order by [Name of Item] ASC;";


        $query = $this->petcoPDO->prepare($sql);
        $query->execute();
         while($row = $query->fetch()){
                $this->productArray[] =   array(
                                        'name' => str_replace('"','_inch', $row['Name of Item']),
                                        'cost' => round($row['Cost'],2),
                                        'retail' => round($row['Selling Price'],2),
                                        'year' => array($this->date[0]['year'] =>0, $this->date[1]['year'] =>0, $this->date[2]['year'] =>0)
                                    );
         }
    }
    
    
    function saleQty(){
        for($i = 0; $i < count($this->date); $i++){
            $sql = "SELECT [Name of Item],SUM([QuantityBought]) as total
                    FROM Stock
                        inner join [Orders] on [Name of Item] = [NameOfItem]
                        inner join [Days] on [Order Number] = OrderNo
                    WHERE
                        [Date] >= '".$this->date[$i]['dateStart']."' AND
                        [Date] <= '".$this->date[$i]['dateEnd']."' AND
                        Discontinued = 0 AND
                        Manufacturer = '".$this->brand."'
                    GROUP BY [Name of Item] order by Stock.[Name of Item] ASC;";
                    
            $query = $this->pdo->prepare($sql);
            $query->execute();
            
                while($row = $query->fetch()){
                    $name = str_replace('"','_inch',$row['Name of Item']);
                    $qty = $row['total'];
                    
                    $key = array_search($name, array_column($this->productArray, 'name'));
                    $this->productArray[$key]['year'][$this->date[$i]['year']] = $this->productArray[$key]['year'][$this->date[$i]['year']] + $qty;
                }
        }
              // print_r($this->productArray);
    }
    
    function show(){
        $table = "<TABLE>";
        $table .="<tr>  <th>Product</th>";

            for($i=0;$i < count($this->date);$i++){
                $table .= "<th>".$this->date[$i]['year']." QTY</th>
                            <th>".$this->date[$i]['year']." Sold Value</th>
                            <th>".$this->date[$i]['year']." Sold Retail Value</th>";
            }
    
            $table .= "<th>Diff</th>";
            $table .= "</tr>";
            
            for ($i=0; $i < count($this->productArray);$i++){
                $table .= "<TR>";
                    $table .= "<td>".str_replace('_inch','"', $this->productArray[$i]['name'])."</td>";
    
                        for($j=0; $j < count($this->date); $j++){
                            $key = $this->date[$j]['year'];
                            $table .= "<td>".$this->productArray[$i]['year'][$key]."</td>";
                            $table .= "<td>&euro;".round($this->productArray[$i]['year'][$key]*$this->productArray[$i]['cost'],2)."</td>";
                            $table .= "<td>&euro;".round($this->productArray[$i]['year'][$key]*$this->productArray[$i]['retail'],2)."</td>";
                        }
                    
                    $table .= "<td>&euro;".round(($this->productArray[$i]['year'][$this->date[2]['year']]-$this->productArray[$i]['year'][$this->date[1]['year']])*$this->productArray[$i]['retail'],2)."</td>";
                $table .= "</TR>";    
            }
        $table .= "</TABLE>";
        echo $table.'<br/><br/>';
    }
    
    function getProductArray(){
            return $this->productArray;
    }
    
    function getYears(){
        return $this->date;
    }
  
}