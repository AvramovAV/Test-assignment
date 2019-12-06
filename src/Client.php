<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 06.12.19
 * Time: 2:10
 */

namespace TimeSpeak\ApiClient;
use TimeSpeak\ApiClient\Comment as Comment;
use GuzzleHttp\Client as HttpClient;
use TimeSpeak\ApiClient\Exception\ApiClientException;

class Client
{
    const SERVICE_URI = 'http://example.com';

    /**
     * @var HttpClient|null
     */
    private $client;

    /**
     * Client constructor.
     * @param HttpClient|null $httpClient
     * @param string $serviceUri
     */
    function __construct(?HttpClient $httpClient, $serviceUri = self::SERVICE_URI)
    {
        $this->client = $httpClient?? new HttpClient([
            // Base URI is used with relative requests
            'base_uri' => $serviceUri,
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
    }


    /**
     * Возвращает список комментариев.
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(): array
    {
        $response = $this->client->request('GET', 'comments');
        $comments = \GuzzleHttp\json_decode($response->getBody());
        return Comment::getArrayOfObjects($comments);
    }


    /**
     * Добавление комментария.
     * @param string $name
     * @param string $text
     * @return \TimeSpeak\ApiClient\Comment
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TimeSpeak\ApiClient\Exception\ApiClientException
     */
    public function add(string $name, string $text): Comment
    {
        $comment = new Comment(null, $name, $text);
        $response = $this->client->request('POST','comment', [
            'json' => $comment->getAsArray()
        ]);
        $this->checkStatusCode($response->getStatusCode());

        return Comment::createFromObject(
            \GuzzleHttp\json_decode($response->getBody())
        );
    }

    /**
     * Обновление комментария.
     * @param int $id
     * @param string $name
     * @param string $text
     * @return \TimeSpeak\ApiClient\Comment
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TimeSpeak\ApiClient\Exception\ApiClientException
     */
    public function update(int $id, string $name, string $text): Comment
    {
        $comment = new Comment($id, $name, $text);
        $response = $this->client->request('PUT', "comment/".$id, [
            'json' => $comment->getAsArray()
        ]);
        $this->checkStatusCode($response->getStatusCode());

        return Comment::createFromObject(
            \GuzzleHttp\json_decode($response->getBody())
        );
    }

    /**
     * Проверка статуса ответа.
     * @param int $status
     * @throws \TimeSpeak\ApiClient\Exception\ApiClientException
     */
    private function checkStatusCode(int $status):void
    {
        if($status!==200) {
            throw new ApiClientException("Error: Api return {$status} status code.");
        }
    }


}