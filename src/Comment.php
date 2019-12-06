<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 06.12.19
 * Time: 2:10
 */

namespace TimeSpeak\ApiClient;

use TimeSpeak\ApiClient\Exception\CommentException;

class Comment
{
    private $id = null;
    private $name;
    private $text;

    /**
     * Comment constructor.
     * @param null $id
     * @param $name
     * @param $text
     */
    public function __construct(?int $id, string $name, string $text)
    {
        $this->id = $id;
        $this->name = $name;
        $this->text = $text;

        $this->checkField('name');
        $this->checkField('text');

        return $this;
    }

    /**
     * Returns the comment fields as an array.
     * @return array
     */
    public function getAsArray()
    {
        $data = [];
        foreach ($this as $field => $value) {
            if(!empty($value)) {
                $data[$field] = $value;
            }
        }
        return $data;
    }

    /**
     * @param array $objects
     * @return array
     */
    public static function getArrayOfObjects(array $objects): array
    {
        $comments = [];
        foreach ($objects as $comment) {
            $comments[] = new Comment($comment->id, $comment->name, $comment->text);
        }

        return $comments;
    }

    /**
     * @param $comment
     * @return Comment
     */
    public static function createFromObject($comment): Comment
    {
        if(!isset($comment->id)) {
            throw new CommentException("Wrong id.");
        }
        return new Comment($comment->id, $comment->name, $comment->text);
    }

    /**
     * @param $comment
     * @return Comment
     */
    public static function createFromArray($comment): Comment
    {
        if(!isset($comment["id"])) {
            throw new CommentException("Wrong id.");
        }
        return new Comment($comment["id"], $comment["name"], $comment["text"]);
    }


    /**
     * @param $field
     * @return bool
     */
    private function checkField($field)
    {
        if(empty($this->$field)) {
            throw new CommentException("The {$field} field is empty.");
        }
        return true;
    }

}