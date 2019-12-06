<?php
namespace TimeSpeak\ApiClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use TimeSpeak\ApiClient\Client as ApiClient;
use TimeSpeak\ApiClient\Exception\ApiClientException;
use TimeSpeak\ApiClient\Exception\CommentException;

class ApiClientTest extends TestCase
{
    protected $apiClient;

    protected $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();

        $httpClient = new Client([
            'handler' => $this->mockHandler,
        ]);

        $this->apiClient = new ApiClient($httpClient);
    }

    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/comments.json')));
        $comments = $this->apiClient->get();
        $this->assertIsArray($comments);
        $this->assertContainsOnlyInstancesOf(Comment::class, $comments);
    }

    public function testGetWithError()
    {
        $this->mockHandler->append(new Response(401, [], ""));
        $this->expectException(\GuzzleHttp\Exception\InvalidArgumentException::class);
        $this->apiClient->get();
    }

    public function testAdd()
    {
        $jsonComment = file_get_contents(__DIR__ . '/fixtures/comment.json');
        $this->mockHandler->append(new Response(200, [], $jsonComment));
        $arrComment = \GuzzleHttp\json_decode($jsonComment, true);
        $comment = $this->apiClient->add($arrComment['name'], $arrComment['text']);
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals(Comment::createFromArray($arrComment), $comment);
    }

    public function testAddGetExceptionForName()
    {
        $jsonComment = file_get_contents(__DIR__ . '/fixtures/comment.json');
        $this->mockHandler->append(new Response(200, [], $jsonComment));
        $arrComment = \GuzzleHttp\json_decode($jsonComment, true);
        $this->expectException(CommentException::class);
        $this->expectExceptionMessage("The name field is empty.");
        $this->apiClient->add("", $arrComment['text']);
    }

    public function testAddGetExceptionWithErrorStatus()
    {
        $jsonComment = file_get_contents(__DIR__ . '/fixtures/comment.json');
        $status = 401;
        $this->mockHandler->append(new Response($status, [], '{}'));
        $arrComment = \GuzzleHttp\json_decode($jsonComment, true);
        $this->expectException(ApiClientException::class);
        $this->expectExceptionMessage("Error: Api return {$status} status code.");
        $this->apiClient->add( $arrComment['name'], $arrComment['text']);

    }

    public function testAddGetExceptionForText()
    {
        $jsonComment = file_get_contents(__DIR__ . '/fixtures/comment.json');
        $this->mockHandler->append(new Response(200, [], $jsonComment));
        $arrComment = \GuzzleHttp\json_decode($jsonComment, true);
        $this->expectException(CommentException::class);
        $this->expectExceptionMessage("The text field is empty.");
        $this->apiClient->add($arrComment['name'], "");
    }

    public function testUpdate()
    {
        $jsonComment = file_get_contents(__DIR__ . '/fixtures/comment.json');
        $arrComment = \GuzzleHttp\json_decode($jsonComment, true);
        $newName = 'NewAuthorName';
        $newText = 'NewText';
        $arrNewComment = array_merge($arrComment, ['name' => $newName, 'text' => $newText]);

        $this->mockHandler->append(new Response(200, [], json_encode($arrNewComment)));

        $updatedComment = $this->apiClient->update($arrComment['id'], $newName, $newText);
        $this->assertInstanceOf(Comment::class, $updatedComment);
        $this->assertEquals(Comment::createFromArray($arrNewComment), $updatedComment);
    }

    public function testUpdateWrong()
    {
        $jsonComment = file_get_contents(__DIR__ . '/fixtures/comment.json');
        $arrComment = \GuzzleHttp\json_decode($jsonComment, true);
        $newName = '';
        $newText = 'NewText';
        $arrNewComment = array_merge($arrComment, ['name' => $newName, 'text' => $newText]);

        $this->mockHandler->append(new Response(200, [], json_encode($arrNewComment)));
        $this->expectException(CommentException::class);
        $this->expectExceptionMessage("The name field is empty.");
        $this->apiClient->update($arrComment['id'], $newName, $newText);
    }

    public function testUpdateEmptyNameAndText()
    {
        $jsonComment = file_get_contents(__DIR__ . '/fixtures/comment.json');
        $arrComment = \GuzzleHttp\json_decode($jsonComment, true);
        $newName = '';
        $newText = '';
        $arrNewComment = array_merge($arrComment, ['name' => $newName, 'text' => $newText]);

        $this->mockHandler->append(new Response(200, [], json_encode($arrNewComment)));
        $this->expectException(CommentException::class);
        $this->expectExceptionMessage("The name field is empty.");
        $this->apiClient->update($arrComment['id'], $newName, $newText);
    }

    public function testUpdateExceptionWithErrorStatus()
    {
        $jsonComment = file_get_contents(__DIR__ . '/fixtures/comment.json');
        $arrComment = \GuzzleHttp\json_decode($jsonComment, true);
        $newName = 'NewAuthorName';
        $newText = 'NewText';
        $status = 401;
        $this->mockHandler->append(new Response($status, [], ''));
        $this->expectException(ApiClientException::class);
        $this->expectExceptionMessage("Error: Api return {$status} status code.");
        $this->apiClient->update($arrComment['id'], $newName, $newText);
    }
}