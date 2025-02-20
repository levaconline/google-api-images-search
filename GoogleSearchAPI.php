<?php

/**
 * In current example we no need namaspace nor autoload.
 * Custom google images search api test via cli: (can be modified for usage in web)
 *
 * Three query [parameters] are required with each search request:
 *
 *    API key - Use the key query parameter to identify your application.
 *    Programmable Search Engine ID - Use cx to specify the Programmable Search Engine you want to use to perform this search. The search engine must be created with the Control Panel Note: The Search Engine ID (cx) can be of different format (e.g. 8ac1ab64606d234f1)
 *    Search query - Use the q query parameter to specify your search expression.
 *
 * All other query parameters are optional.
 * Free search gooogle allow only 10 items by req.
 * 
 * Note: The API key and cx query parameters are required for all search requests. The q query parameter is required for all search requests except for image search requests (using the searchType=image query parameter).
 */

class GoogleSearchAPI
{
    private string $query;
    private string $envFilePath = '.env';
    private array $envData = [];

    public function __construct(string $query = 'Trees')
    {
        $this->query = urlencode($query);
    }

    /**
     * @throws Exception
     * @return string|bool
     */
    public function googleApiGet(): string|bool
    {
        $this->parseCustomEnv();

        $requestUrl = sprintf(
            '%s?q=%s&key=%s&searchType=image&cx=%s',
            $this->envData['GOOGLE_API_URL'],
            $this->query,
            $this->envData['GOOGLE_API_KEY'],
            $this->envData['GOOGLE_API_CX']
        );

        $curl = curl_init($requestUrl);

        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception('cURL Error: ' . $error);
        }

        curl_close($curl);
        return $response;
    }

    /**
     * Custom .env file parser
     * 
     * @throws Exception
     * @return void
     */
    private function parseCustomEnv(): void
    {
        if (!file_exists($this->envFilePath)) {
            throw new Exception("Environment file not found: {$this->envFilePath}");
        }

        $envData = [];
        foreach (file($this->envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $envData[$key] = $value;
        }

        $requiredKeys = ['GOOGLE_API_URL', 'GOOGLE_API_KEY', 'GOOGLE_API_CX'];
        foreach ($requiredKeys as $key) {
            if (!isset($envData[$key])) {
                throw new Exception("Missing required environment variable: $key");
            }
        }

        $this->envData = $envData;
    }
}


// Example of call this class
$querey = "Felix silvestris";

$class = new GoogleSearchAPI($querey);
$result = $class->googleApiGet();

// Convert json to array of objects.
$resultArr = json_decode($result);

print_r($resultArr);
