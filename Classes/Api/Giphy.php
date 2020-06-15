<?php
declare(strict_types=1);
namespace Webandco\Giphy\Api;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Http\Client\CurlEngine;

class Giphy
{

    private static $GIPHY_API_URL = 'http://api.giphy.com';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var CurlEngine
     */
    private $curlEngine;

    /**
     * Giphy constructor.
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->curlEngine = new CurlEngine();
    }

    /**
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function getById(string $id)
    {
        $endpoint = "/v1/gifs/$id";
        return $this->request($endpoint);
    }

    /**
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @throws \Exception
     */
    public function search(string $query, int $limit = 25, int $offset = 0)
    {
        $endpoint = '/v1/gifs/search';
        $params = array(
            'q' => urlencode($query),
            'limit' => $limit,
            'offset' => $offset
        );
        return $this->request($endpoint, $params);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @throws \Exception
     */
    public function trending(int $limit = 25, int $offset = 0)
    {
        $endpoint = '/v1/gifs/trending';
        $params = array(
            'limit' => $limit,
            'offset' => $offset
        );
        return $this->request($endpoint, $params);
    }

    /**
     * @param $endpoint
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    private function request ($endpoint, array $params = array())
    {
        $params['api_key'] = $this->apiKey;

        $uri = new Uri(self::$GIPHY_API_URL . $endpoint);
        $request = new ServerRequest('GET', $uri->withQuery(http_build_query($params)));

        $response = $this->curlEngine->sendRequest($request);

        if ($response->getStatusCode() != 200) {
            throw new \Exception('Error connecting to the API ' . $request->getUri()->getHost() . $request->getUri()->getPath());
        }
        return json_decode($response->getBody()->getContents());
    }

}
