<?php
header("Access-Control-Allow-Origin: *");
    include("class/classXML.php");
    $array = json_decode($_GET['ipArray'], TRUE);

if(isset($array)){
    $xml = new xmlFile('dbXML.xml');

    foreach($array as $key=>$value){
       var_dump($array);
       $xml->saveNodeToFile($value['storeName'],$value['ip']);
        //echo $value['storeName'];
    }
}else{
    echo "Error. Can't find array";
}

    
       
?>