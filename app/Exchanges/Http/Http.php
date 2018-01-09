<?php
namespace App\Exchanges\Http;

use Goutte\Client;

class Http {

    const GET   = 'GET';
    const POST  = 'POST';
    const PUT   = 'PUT';
    const DELETE = 'DELETE';


    /***
     * @param string $method
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return HttpResponse
     * @throws \Exception
     */
    public static function request(string $method, string $url, array $params=[], array $headers=[])
    {
        $client = new Client();

        // set headers
        foreach($headers as $name => $value){
            $client->setHeader($name, $value);
        }

        $retries = 0;
        $retriesMax = config('autocoin.http_retries', 5); // retry max
        $retriesInterval = config('autocoin.http_retries_interval', 1);

        $res = null;
        $responseCode = null;

        // Try request until you get 200, or reach out max trial
        while($retries < $retriesMax){
            $res = $client->request($method, $url, $params);
            $responseCode = $client->getResponse()->getStatus();

            if($responseCode == 200){
                break;
            }
            else {
                usleep($retriesInterval * 1000000);
                $retries++;
            }
        }

        // Throw error if not 200
        if($responseCode != 200){
            throw new \Exception(sprintf('Error while requesting %s. Status Code: %d', $url, $responseCode));
        }


        // return
        $return = new HttpResponse();
        $return->content = $client->getResponse()->getContent();
        $return->dom = $res;
        $return->status = $responseCode;

        return $return;
    }

}

class HttpResponse {
    public $content;
    public $dom;
    public $status;
}