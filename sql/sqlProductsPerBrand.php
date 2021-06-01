<?php
require dirname( __DIR__ ) . '\vendor\autoload.php';
    set_time_limit(0);
    ini_set('max_execution_time', 3000);
    ini_set('max_input_vars', 9000);
    
    $brandName = $_POST['brandName'];  //getting supplier name from select
    //$storeOrAway = $_POST['storeOrAway'];
    
    $xml = new XML($_SERVER["DOCUMENT_ROOT"].'/dbXML.xml');
    $db = new DB($xml->getConnectionArray());
    
    $product = new Product($db->getDbConnection(2));
    $product->createProducts($brandName);
    
    
    
    for($i=0; $i < $db->getMaxIndex();$i++){
        
        $product->openConnection($db->getDbConnection($i));
        $product->saleQty();        
    }
    //echo "<div Class='row'>
    //        <div Class='col-xs-12 col-12'>
    //            <button id='excelExport'><img src='exportExcel.jpg'/><br/>Export to Excel</button>
    //            </div>
    //            </div>";
    //
    echo '<script>$("#exportToExcel").show();</script>';
    $product->show();
  

?>
<script>
    $( "#exportToExcel" ).click(function(){
        var brandName = '<?php echo $brandName; ?>';
        var products = '<?php echo json_encode($product->getProductArray()); ?>';
        var years = '<?php echo json_encode($product->getYears()); ?>';
        var result = $('#result').html();
         $.post( "pages/exportToExcel.php", {
            brandName: brandName,
            products: products,
            years: years
        }).done(function( data ) {
            $('#result').html(data+result);
        });
    });
</script>
