<?php

namespace Opencontent\Google;

class GoogleSheetClient
{
    const EZSITEDATA_KEY = 'oc_google_sheet_credentials';

    private $credentials = null;

    private $credentialsSource = null;

    private $client;

    public function __construct(array $credentials = null)
    {
        if ($credentials) {
            $this->credentials = $credentials;
        } else {
            if (class_exists('\eZSys')) {
                $this->provideCredentialFromEzSiteData();
                if (!$this->credentials && class_exists('\eZSiteData')) {
                    $this->provideCredentialFromEzSettingsFilePath();
                }
            }
            if (!$this->credentials) {
                $this->provideCredentialFromEnvJsonFilePath();
            }
        }
    }

    private function provideCredentialFromEnvJsonFilePath()
    {
        $credentials = getenv('GOOGLE_CREDENTIAL_JSON_FILE');
        if ($credentials && file_exists($credentials)) {
            $this->credentials = json_decode(file_get_contents($credentials), true);
            $this->credentialsSource = 'DEFAULT_ENV';
        }
    }

    private function provideCredentialFromEzSettingsFilePath()
    {
        $credentials = \eZSys::rootDir() . '/settings/google_credentials.json';
        if (file_exists($credentials)) {
            $this->credentials = json_decode(file_get_contents($credentials), true);
            $this->credentialsSource = 'DEFAULT_SETTINGS';
        }
    }

    private function provideCredentialFromEzSiteData()
    {
        $siteData = \eZSiteData::fetchByName(self::EZSITEDATA_KEY);
        if ($siteData instanceof \eZSiteData) {
            $data = json_decode($siteData->attribute('value'), true);
            if ($data) {
                $this->credentials = $data;
                $this->credentialsSource = 'CUSTOM';
            }
        }
    }

    public function getGoogleClient()
    {
        if ($this->client === null) {
            $client = new \Google_Client();
            $client->setApplicationName('Google Sheets Importer');
            $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
            $client->setAccessType('offline');
            $client->setAuthConfig($this->credentials);
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

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function getCredentialsSource()
    {
        return $this->credentialsSource;
    }
}