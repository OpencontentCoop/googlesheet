<?php
require 'autoload.php';

$spreadsheetId = '1Sr21vupXSjru__6NfteiFbM6kmarNhyETzYgjSp_ngc';
$sheetTitle = 'import: decreti del dirigente (it)';

$sheet = new \Opencontent\Google\GoogleSheet($spreadsheetId);
echo $sheet->getTitle();
