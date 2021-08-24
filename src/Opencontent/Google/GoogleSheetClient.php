<?php

namespace Opencontent\Google;

class GoogleSheetClient
{
    private $credentialFilepath;

    private $client;

    public function __construct()
    {
        $credentialFilepath = getenv('GOOGLE_CREDENTIAL_JSON_FILE');
        if (!$credentialFilepath && class_exists('\eZSys')){
            \eZSys::rootDir() . '/settings/google_credentials.json';
        }
        $this->credentialFilepath = $credentialFilepath;
    }

    public function getGoogleClient()
    {
        if ($this->client === null) {
            $client = new \Google_Client();
            $client->setApplicationName('Google Sheets Importer');
            $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
            $client->setAccessType('offline');
            $client->setAuthConfig($this->credentialFilepath);
            $this->client = $client;
        }

        return $this->client;
    }

    public function getGoogleSheetService()
    {
        return new \Google_Service_Sheets($this->getGoogleClient());
    }

    /**
     * @return \Google_Client
     */
    public function getClient()
    {
        return $this->client;
    }


}