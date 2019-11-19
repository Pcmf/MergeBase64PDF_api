<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$fileArray= array("./testes/cover1569857756.pdf","./testes/report1569857756.pdf");

$datadir = "./testes/";
$outputName = $datadir."merged.pdf";

$cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";
//Add each pdf file to the end of the command
foreach($fileArray as $file) {
    $cmd .= $file." ";
}
$result = shell_exec($cmd);
echo "Fim. ".$result.$cmd;