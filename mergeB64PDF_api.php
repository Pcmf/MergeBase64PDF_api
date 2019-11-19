<?php
date_default_timezone_set('Europe/Lisbon');
/**
 *     API to receive two base64/pdf , merge them and return as base64/pdf
*  is validated by token
 */

require_once('./resources/src/autoload.php');
require_once './resources/fpdf181/fpdf.php';

require './resources/php-pdf-merge/src/Jurosh/PDFMerge/PDFMerger.php';
require './resources/php-pdf-merge/src/Jurosh/PDFMerge/PDFObject.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

$stamp = time();



if(!isset($dt->cover) || !$dt->cover ){
    echo "ERRO: cover";
    return;
}
if(!isset($dt->report) || !$dt->report ){
    echo "ERRO: report";
    return;
}

try {
        $pdf = fopen('./cover'.$stamp.'.pdf','w');
        fwrite($pdf,base64_decode($dt->cover));
        fclose($pdf);
} catch (Exception $exc) {
    echo "ERRO: create tmp cover.pdf! ";
    unlink('cover'.$stamp.'.pdf'); 
    return;
}


try{        
        $pdf = fopen('./report'.$stamp.'.pdf','w');
        fwrite($pdf,base64_decode($dt->report));
        fclose($pdf);
} catch (Exception $exc) {
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
    echo "ERRO: merging! ";
    unlink('cover'.$stamp.'.pdf'); 
    unlink('report'.$stamp.'.pdf'); 
    return;
}


//create new file pdf
try {
    $merge->merge('file', './cover_report'.$stamp.'.pdf');
} catch (Exception $exc) {
    echo "ERRO: creating file! ";
    unlink('cover'.$stamp.'.pdf'); 
    unlink('report'.$stamp.'.pdf'); 
    return;
}




//convert to base64 
try {
    $b64Doc = base64_encode(file_get_contents('cover_report'.$stamp.'.pdf'));
} catch (Exception $exc) {
    echo "ERRO: converting to base64! ";
    unlink('cover'.$stamp.'.pdf'); 
    unlink('report'.$stamp.'.pdf'); 
    unlink('cover_report'.$stamp.'.pdf');
    return;
}

echo $b64Doc;
//clear temp files
unlink('cover'.$stamp.'.pdf'); 
unlink('report'.$stamp.'.pdf');
unlink('cover_report'.$stamp.'.pdf');
return;

