<?php

namespace Fenos\Mex\Messages;

use Fenos\Mex\Conversations\Conversation;
use Fenos\Mex\Exceptions\ConversationNotFoundException;
use Fenos\Mex\Messages\Repositories\MessageRepository;

/**
 * Class Message
 * @package Fenos\Mex\Messages
 */
class Message {

    /**
     * @var $conversation_id
     */
    protected $conversation_id;
    /**
     * @var $message_id
     */
    protected $message_id;

    /**
     * @var $message
     */
    private $message;

    /**
     * @var $from
     */
    protected $from;

    /**
     * @var MessageRepository
     */
    private $messRepo;

    /**
     * @param $message
     * @param \Fenos\Mex\Conversations\Conversation
     * @param MessageRepository $messRepo
     */
    function __construct($message, Conversation $conversation,MessageRepository $messRepo)
    {
        $this->messRepo = $messRepo;
        $this->message = $message;
        $this->from = $conversation->getFrom();
        $this->conversation_id = $conversation->conversationId();
    }

    /**
     * Insert message body text
     *
     * @param $message
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function message($message)
    {
        if (!is_string($message))
        {
            throw new \InvalidArgumentException('The message must be a string');
        }

        return $message;
    }

    /**
     * Specify the partecipant that do the action
     *
     * @param $id
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function from($id)
    {
        $this->from = $id;

        return $this;
    }

    /**
     * Send a message a given conversation
     *
     * @throws \InvalidArgumentException
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function send()
    {
        if (is_null($this->conversation_id))
        {
            throw new \InvalidArgumentException('You must specify the ID of the conversation');
        }

        $conversation_id = $this->conversation_id;

        $info_message = [
            'text'              => $this->message($this->message),
            'participant_id'    => $this->from,
            'conversation_id'   => $conversation_id
        ];

        return $this->messRepo->add($info_message);
    }

    /**
     * Delete a message inserting it on the deleted_messages
     * table
     *
     * @throws \BadMethodCallException
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function delete()
    {
        if (is_null($this->from))
        {
            throw new \BadMethodCallException('You must specify the ID of participant that do the action on the method [ from() ]');
        }

        return $this->messRepo->delete($this->conversation_id,$this->message,$this->from);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param mixed $conversation_id
     */
    public function setConversationId($conversation_id)
    {
        $this->conversation_id = $conversation_id;
    }

    public function getFrom()
    {
        return $this->from;
    }

}