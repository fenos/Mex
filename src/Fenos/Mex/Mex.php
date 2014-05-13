<?php

namespace Fenos\Mex;


use Fenos\Mex\Conversations\Conversation;
use Fenos\Mex\Models\Conversation as ConversationModel;
use Fenos\Mex\Models\ConversationJoined;
use Fenos\Mex\Models\DeletedConversation;
use Fenos\Mex\Models\DeletedMessage;
use Fenos\Mex\Models\Message;

/**
 * Class Mex
 * Multi users Chat API, created by Fabrizio Fenoglio
 *
 * Version 1.0
 * Licence MIT
 * Exclusive for Laravel 4
 *
 * @package Fenos\Mex
 */
class Mex {


    /**
     * @var mixed
     */
    public $app;

    /**
     *
     */
    function __construct()
    {
        $this->app = app();
    }

    /**
     * @return mixed
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * This is the main method of Imex it
     * instantiate the object to work with conversations.
     *
     *
     * @param null $id
     * @return Conversation
     */
    public function conversation($id = null)
    {
        return new Conversation($id, $this->app['app']->make('mex.conversation.repository'), $this->app['app']->make('mex.conversationJoined'));
    }

    /**
     * @return ConversationModel
     */
    public function emptyConversation()
    {
        return new ConversationModel();
    }

    /**
     * @return Message
     */
    public function emptyMessage()
    {
        return new Message();
    }

    /**
     * @return DeletedMessage
     */
    public function deletedMessages()
    {
        return new DeletedMessage();
    }

    /**
     * @return ConversationJoined
     */
    public function emptyConversationJoined()
    {
        return new ConversationJoined();
    }

    /**
     * @return DeletedConversation
     */
    public function emptyDeletedConversation()
    {
        return new DeletedConversation();
    }

}