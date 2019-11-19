<?php
date_default_timezone_set('Europe/Lisbon');
/**
 *     API to receive two base64/pdf , merge them and return as base64/pdf
*  is validated by token
 */
function toLog($param) {
    
    $time = date('Y-m-d H:i:s');
    
    $txt = "Recebido: ".$time."  Log: ".$param;
    $newLine = PHP_EOL;
    file_put_contents('./log.txt',$txt.$newLine,FILE_APPEND);
    return;
}

require_once('./resources/src/autoload.php');
require_once './resources/fpdf181/fpdf.php';

require './resources/php-pdf-merge/src/Jurosh/PDFMerge/PDFMerger.php';
require './resources/php-pdf-merge/src/Jurosh/PDFMerge/PDFObject.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

$stamp = time();



if(!isset($dt->cover) || !$dt->cover ){
    toLog("ERRO: cover");
    echo "ERRO: cover";
    return;
}
if(!isset($dt->report) || !$dt->report ){
    toLog("ERRO: report");
    echo "ERRO: report";
    return;
}

try {
        $pdf = fopen('./cover'.$stamp.'.pdf','w');
        fwrite($pdf,base64_decode($dt->cover));
        fclose($pdf);
} catch (Exception $exc) {
   
    toLog("ERRO: create tmp cover.pdf! ".$exc->getTraceAsString());
    echo "ERRO: create tmp cover.pdf! ";
    unlink('cover'.$stamp.'.pdf'); 
    return;
}


try{        
        $pdf = fopen('./report'.$stamp.'.pdf','w');
        fwrite($pdf,base64_decode($dt->report));
        fclose($pdf);
} catch (Exception $exc) {

    toLog("ERRO: create tmp report.pdf! ".$exc->getTraceAsString());
    echo "ERRO: create tmp report.pdf! ";
    unlink('cover'.$stamp.'.pdf'); 
    unlink('report'.$stamp.'.pdf'); 
    return;
}



$merge = new \Jurosh\PDFMerge\PDFMerger;
//merge
try {
    $merge->addPDF( 'cover'.$stamp.'.pdf', 'all', 'vertical')->addPDF('report'.$stamp.'.pdf', 'all', 'vertical');
} catch (Exception $exc) {
    
    toLog("ERRO: merging! " .$exc->getTraceAsString());
    echo "ERRO: merging! ";
    unlink('cover'.$stamp.'.pdf'); 
    unlink('report'.$stamp.'.pdf'); 
    return;
}


//create new file pdf
try {
    $merge->merge('file', './cover_report'.$stamp.'.pdf');
} catch (Exception $exc) {
    toLog("ERRO: creating file! ".$exc->getTraceAsString());
    echo "ERRO: creating file! ";
    return;
}




//convert to base64 
try {
    $b64Doc = base64_encode(file_get_contents('cover_report'.$stamp.'.pdf'));
} catch (Exception $exc) {

    toLog("ERRO: converting to base64! ". $exc->getTraceAsString());
    echo "ERRO: converting to base64! ";
    unlink('cover'.$stamp.'.pdf'); 
    unlink('report'.$stamp.'.pdf'); 
    unlink('cover_report'.$stamp.'.pdf');
    return;
}


toLog("Sucesso");
echo $b64Doc;
//clear temp files
unlink('cover'.$stamp.'.pdf'); 
unlink('report'.$stamp.'.pdf');
unlink('cover_report'.$stamp.'.pdf');
return;

