<?php

namespace Opencontent\Google;

use Google\Service\Sheets\Sheet;
use Google\Service\Sheets\Spreadsheet;

class GoogleSheet
{
    /**
     * @var Spreadsheet
     */
    private $spreadsheet;

    private $spreadsheetId;

    private $client;

    private $googleSheetService;

    public function __construct($spreadsheetId, GoogleSheetClient $client = null)
    {
        $this->client = $client ? $client : new GoogleSheetClient();
        $this->googleSheetService = $this->client->getGoogleSheetService();
        $this->spreadsheetId = $spreadsheetId;
        $this->spreadsheet = $this->googleSheetService->spreadsheets->get($spreadsheetId);
    }

    public function getSheetTitleList()
    {
        $list = [];
        foreach ($this->getSheets() as $sheet) {
            $list[] = $sheet->getProperties()->getTitle();
        }

        return $list;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->spreadsheet->getSheets();
    }

    /**
     * @param $sheetTitle
     * @return string
     * @throws \Exception
     */
    public function getSheetDataCsv($sheetTitle)
    {
        $rows = $this->getSheetDataArray($sheetTitle);
        ob_start();
        $fp = fopen('php://output', 'w');
        foreach ($rows as $fields) {
            fputcsv($fp, $fields, ",", '"');
        }
        fclose($fp);
        $str = ob_get_contents();
        ob_end_clean();

        return trim($str, "\r\n");
    }

    /**
     * @param $sheetTitle
     * @return mixed
     * @throws \Exception
     */
    public function getSheetDataArray($sheetTitle)
    {
        $sheet = $this->getByTitle($sheetTitle);

        $rowCount = $sheet->getProperties()->getGridProperties()->getRowCount();
        $colCount = $sheet->getProperties()->getGridProperties()->getColumnCount();

        //Sheet1!R1C1:R2C2
        $range = "{$sheetTitle}!R1C1:R1C{$colCount}";
        $firstRow = $this->googleSheetService->spreadsheets_values->get($this->spreadsheetId, $range)->getValues();
        $realColCount = count($firstRow[0]);
        $range = "{$sheetTitle}!R1C1:R{$rowCount}C{$realColCount}";

        return $this->googleSheetService->spreadsheets_values->get($this->spreadsheetId, $range)->getValues();
    }

    /**
     * @param $sheetTitle
     * @return mixed
     * @throws \Exception
     */
    public function getSheetDataHash($sheetTitle)
    {
        $dataArray = $this->getSheetDataArray($sheetTitle);
        $headers = array_shift($dataArray);
        array_walk($dataArray, function (&$a) use ($headers) {
            $countHeaders = count($headers);
            $countA = count($a);
            if ($countHeaders > $countA){
                $a = array_pad($a, $countHeaders, '');
            }
            $a = array_combine($headers, $a);
        });

        return $dataArray;
    }

    /**
     * @param $sheetTitle
     * @return Sheet
     * @throws \Exception
     */
    public function getByTitle($sheetTitle)
    {
        foreach ($this->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() == $sheetTitle) {
                return $sheet;
            }
        }

        throw new \Exception("Sheet $sheetTitle not found in " . $this->getTitle());
    }

    public function getTitle()
    {
        return $this->spreadsheet->getProperties()->getTitle();
    }
}