<?php
header("Access-Control-Allow-Origin: *");
    include("Class/XML.php");
    $array = json_decode($_GET['ipArray'], TRUE);

if(isset($array)){
    $xml = new XML('dbXML.xml');

    foreach($array as $key=>$value){
       var_dump($array);
       $xml->saveNodeToFile($value['storeName'],$value['ip']);
        //echo $value['storeName'];
    }
}else{
    echo "Error. Can't find array";
}

    
       
?>