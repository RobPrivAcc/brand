<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');
ini_set('max_input_vars', 19000);


$brandName = $_POST['brandName'];
$productsArray = json_decode($_POST['products'], true);
$years = json_decode($_POST['years'],true);
//print_r($years);
$dataStart = 4;

require_once dirname(__FILE__) . '/../class/Excel/PHPExcel.php';

$objPHPExcel = new PHPExcel();

//$cellArray = array("D","E","F","G","H","I","J","K","L","M");
$cellArray = array("B","C","D","E","F","G","H","I","J");



$objPHPExcel->getProperties()->setCreator("Robert Kocjan")
							 ->setLastModifiedBy("Robert Kocjan")
							 ->setTitle("PHPExcel Test Document")
							 ->setSubject("PHPExcel Test Document")
							 ->setDescription("Test document for PHPExcel, generated using PHP classes.")
							 ->setKeywords("office PHPExcel php")
							 ->setCategory("Test result file");


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A3', 'Product Name')
		
			->mergeCells('B2:D2')->setCellValue('B2',$years[0]['year'] )
			->mergeCells('E2:G2')->setCellValue('E2',$years[1]['year'] )
			->mergeCells('H2:J2')->setCellValue('H2',$years[2]['year'])
		
			->setCellValue('B3', 'QTY')
			->setCellValue('C3', 'Value')
			->setCellValue('D3', 'Retail Value')
			
			->setCellValue('E3', 'QTY')
			->setCellValue('F3', 'Value')
			->setCellValue('G3', 'Retail Value')
			
			->setCellValue('H3', 'QTY')
			->setCellValue('I3', 'Value')
			->setCellValue('J3', 'Retail Value')
			->setCellValue('L3', 'Diff');

/*
	spreadsheet style
*/

    $columnWidth = 12;
    
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
	for ($i=0; $i < count($cellArray);$i++){
		$objPHPExcel->getActiveSheet()->getColumnDimension($cellArray[$i])->setWidth($columnWidth);	
	}
    
	$objPHPExcel->getActiveSheet()->getStyle('A2:L3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A2:L3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

/*
	spreadsheet calculations
*/

			
			$lastYear = 0;
			$currentYear = 0;
			
			for ($i=0; $i < count($productsArray);$i++){
				$cell = $dataStart+$i;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$cell, str_replace('_inch','"', $productsArray[$i]['name']));
				
					$k=0;
				while($k <count($cellArray)){
					for($j=0; $j < count($years); $j++){
						$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellArray[$k].$cell, $productsArray[$i]['year'][$years[$j]['year']]);
						$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellArray[++$k].$cell, round($productsArray[$i]['year'][$years[$j]['year']]*$productsArray[$i]['cost'],2));
						$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellArray[++$k].$cell, round($productsArray[$i]['year'][$years[$j]['year']]*$productsArray[$i]['retail'],2));
						$k++;
					}
				}
				
				$gValue = $objPHPExcel->getActiveSheet()->getCell('G'.$cell)->getCalculatedValue();
				
				$lastYear = $lastYear+$gValue;
				
				$jValue = $objPHPExcel->getActiveSheet()->getCell('J'.$cell)->getCalculatedValue();
				
				$currentYear = $currentYear + $jValue;
				
				$diff = $jValue-$gValue;
				
								
				if($diff < 0){
					$objPHPExcel->getActiveSheet()->getStyle('L'.$cell)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
				}elseif($diff > 0){
					$objPHPExcel->getActiveSheet()->getStyle('L'.$cell)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKGREEN ) );
				}else{
					$objPHPExcel->getActiveSheet()->getStyle('L'.$cell)->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLACK ) );
				}
				
				$objPHPExcel->getActiveSheet()->getStyle('C'.$cell)->getNumberFormat()->setFormatCode('€#,##0');
				$objPHPExcel->getActiveSheet()->getStyle('D'.$cell)->getNumberFormat()->setFormatCode('€#,##0');
				$objPHPExcel->getActiveSheet()->getStyle('F'.$cell)->getNumberFormat()->setFormatCode('€#,##0');
				$objPHPExcel->getActiveSheet()->getStyle('G'.$cell)->getNumberFormat()->setFormatCode('€#,##0');
				$objPHPExcel->getActiveSheet()->getStyle('I'.$cell)->getNumberFormat()->setFormatCode('€#,##0');
				$objPHPExcel->getActiveSheet()->getStyle('J'.$cell)->getNumberFormat()->setFormatCode('€#,##0');
				$objPHPExcel->getActiveSheet()->getStyle('L'.$cell)->getNumberFormat()->setFormatCode('€#,##0');
				
				$objPHPExcel->getActiveSheet()->getStyle('G1')->getNumberFormat()->setFormatCode('€#,##.##');
				$objPHPExcel->getActiveSheet()->getStyle('J1')->getNumberFormat()->setFormatCode('€#,##.##');
				$objPHPExcel->getActiveSheet()->getStyle('K1')->getNumberFormat()->setFormatCode('€#,##.##');
				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$cell, $diff);	
			}
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1', $lastYear);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1',$currentYear);

			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('K1:L1')->setCellValue('K1',$currentYear-$lastYear);
			$objPHPExcel->getActiveSheet()->getStyle('K1')->getNumberFormat()->setFormatCode('€#,##.##');
			
			if($currentYear-$lastYear > 0){
				$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKGREEN ) );
			}elseif($currentYear-$lastYear < 0){
				$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
			}else{
				$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLACK ) );
			}
			
			$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
$fileName = $brandName;
$fileName = str_replace(" ","_",$fileName).'.xlsx';


$fileName_to_save = $fileName;

//$objWriter->save('../files/'.$fileName);
//
//$pathToFile = dirname(pathinfo(__FILE__)['dirname']).'\\files\\'.$fileName;




$objWriter->save('../files/'.$fileName_to_save);


$directory = explode("\\",dirname(dirname(__FILE__)));

$pathToFile = dirname(pathinfo(__FILE__)['dirname']).'\\files\\'.$fileName_to_save;

if (file_exists($pathToFile)){
    echo "Click to download <a href = '/".$directory[count($directory)-1]."/files/".$fileName_to_save."'>".$fileName_to_save."</a>";    
}else{
    echo "Ups.. something went wrong and file wasn't created. Contact Robert.";    
}







//if (file_exists($pathToFile)){
//    //echo "Click to download <a href = '/brand/files/".$fileName."'>".$fileName."</a>";
//	$show = "<br/><div class='row'>";
//        $show .= "<div class='col-xs-12 col-12'>";
//			$show .= "<a href = '/brand/files/".$fileName."'  class='btn btn-primary'><i class='fa fa-download' aria-hidden='true'></i>  Download <b>".$fileName."</b></a>";
//		$show .= "</div>";
//	$show .= "</div>";
//	echo $show;
//}else{
//    echo "Ups.. something went wrong and file wasn't created. Please contact with Robert.";    
//}
?>