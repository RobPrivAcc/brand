<?php
class xmlFile{
    
    private $xml = null;
    private $xmlFile = null;
    private $xmlArray = array();
    
     function __construct($xmlFile){
        $this-> xmlFile = $xmlFile;
        $this->xml = simplexml_load_file($this-> xmlFile);
     }
     
     function saveNodeToFile($shopName,$newIp){
        echo $shopName.' - '.$newIp.'<br/>';
        $shopName = trim($shopName);
        $newIp = trim($newIp);
        $path = '/connection/shop[shopName="'.$shopName.'"]';
        $desc = $this->xml->xpath($path);
        $desc[0]->external_IP = $newIp;
        $this->xml->asXML($this->xmlFile); 
     }
     
    function updateNodeToFile($shopName,$newIp){
        $shopName = trim($shopName);
        $newIp = trim($newIp);
        $path = '/connection/shop[shopName="'.$shopName.'"]';
        $desc = $this->xml->xpath($path);
        $desc[0]->external_IP = $newIp;
        $this->xml->asXML($this->xmlFile); 
     }
     
     private function xmlToArray(){
        $xml = simplexml_load_file($this-> xmlFile);
        $json = json_encode($xml);
        $this->xmlArray = json_decode($json,TRUE);
     }
     
     function getConnectionArray(){
        $this->xmlToArray();
        $array = array('login'=> $this->xmlArray['login'],
                       'shop'=> $this->xmlArray['shop']);
        return $array;
     }     
}
?>