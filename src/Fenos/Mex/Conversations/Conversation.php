<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 08/05/14
 * Time: 10:54
 */

namespace Fenos\Mex\Conversations;


use Fenos\Mex\Conversations\Repositories\ConversationRepository;
use Fenos\Mex\Exceptions\ConversationNotFoundException;
use Fenos\Mex\Messages\Message;

/**
 * Class Conversation
 * @package Fenos\Mex\Conversations
 */
class Conversation {

    /**
     * @var mixed
     */
    public $app;

    /**
     * @var $conversation
     */
    protected $conversation_id;

    /**
     * @var $participants
     */
    protected $participants;

    /**
     * @var $conversation
     */
    protected $conversation;

    /**
     * @var $from
     */
    protected $from;

    /**
     * @var Repositories\ConversationRepository
     */
    private $conversationRepo;
    /**
     * @var ConversationJoined
     */
    private $conversationJoined;

    /**
     * @param null $id
     * @param ConversationRepository $conversationRepo
     * @param ConversationJoined $conversationJoined
     */
    function __construct($id = null,ConversationRepository $conversationRepo, ConversationJoined $conversationJoined)
    {

        $this->conversation_id = $id;
        $this->conversationRepo = $conversationRepo;
        $this->conversationJoined = $conversationJoined;
        $this->app = app();

    }

    /**
     * @return mixed
     */
    public function app()
    {
        return $this->app();
    }

    /**
     * Initialize message class injecting
     * the new message to send
     *
     * @param $message
     * @return Message
     */
    public function message($message)
    {
        return new Message($message,$this,$this->app['app']->make('mex.message.repository'));
    }

    /**
     * Specify the partecipant that do the action
     *
     * @param $id
     * @throws \Fenos\Mex\Exceptions\ConversationNotFoundException
     * @return $this
     */
    public function from($id)
    {
        if ( !is_numeric($id) )
        {
            throw new ConversationNotFoundException('Conversation not found');
        }

        $this->from = $id;

        return $this;
    }

    /**
     * Set the participants of the conversation
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function participants()
    {
        $partecipants = func_get_args();

        if (is_array($partecipants[0]))
        {
            $partecipants = $partecipants[0];
        }

        // the participants of a conversation are minimum 2
        // don't talk alone is sad.
        if (count($partecipants) < 2)
        {
            throw new \InvalidArgumentException('method [participants] require minimum 2 values or an array with 2 values');
        }

        $this->participants = $partecipants;

        return $this;
    }

    /**
     * Crete a new conversation
     *
     * @param $information_conversation
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function create($information_conversation)
    {
        if (!array_key_exists('founder_id',$information_conversation))
        {
            throw new \InvalidArgumentException('You must specify the founder id');
        }

        if (is_null($this->participants))
        {
            throw new \InvalidArgumentException('You must specify minimum 2 participants');
        }

        $conversation_created = $this->conversationRepo->create($information_conversation);

        // you can get the conversation using the getter GetConversation
        // after the conversation has been created
        $this->conversation = $conversation_created;

        $this->conversation_id = $conversation_created->id;

        // check if there is the founder in participants array, just for
        // make sure that the partecipant will be not inserted 2 times or
        // not inserted at all
        if ( !in_array($information_conversation['founder_id'],$this->participants) )
        {
            $this->participants[] = $information_conversation['founder_id'];
        }

        $this->conversationJoined->addPartecipants($this->participants,$this->conversation_id);

        return $this;
    }

    /**
     * Check if a conversation exists giving
     * the partecipats ID or conversation ID
     *
     * @throws \Fenos\Mex\Exceptions\ConversationNotFoundException
     * @throws \BadMethodCallException
     * @return mixed
     */
    public function exists()
    {
        $participants = $this->participants;

        // check if the developer pass the participants ID or the ID
        // of the conversation for check if it exists. If the developer
        // doesn't provide any of them will get an exception. You need to specified
        // one of them
        if (is_null($participants) and is_null($this->conversation_id))
        {
            throw new \BadMethodCallException('participants or conversation ID Not specified');
        }

        // I can pass the if of the current user that want to check if the conversation
        // exists on the method from(). So i can have the lists of participants on the method
        // participants and the session ID on the from() method
        if (!is_null($this->from) and !is_null($participants))
        {
            // search if for some reason there is already that id in
            // participants values
            if( in_array($this->from,$participants) === false )
            {
                $participants[] = $this->from;
            }
        }

        // If you don't pass the Id of the conversation means that
        // you wanna check the conversation by participants ID
        if ( is_null($this->conversation_id) )
        {
            $exist = $this->conversationJoined->checkByPartecipantsId($participants);
        }
        else
        {
            $exist = $this->conversationJoined->checkByConversationID($this->conversation_id);
        }

        if (is_null($exist))
            throw new ConversationNotFoundException;
        else
            return $exist;

    }

    /**
     * Join a partecipant in a conversation
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function join()
    {
        $participants = func_get_args();

        if (is_array($participants[0]))
        {
            $participants = $participants[0];
        }

        // check if the conversation exists between the current participants
        // I don't catch the esception here, because you'll catch when you
        // try to get messages
        $conversation = $this->exists();

        // we need also to check if the current user to join is already
        // on the current conversation we don't want the same user
        // 2 times in the same conversation
        $participants = $this->conversationJoined->areNewPartecipants($conversation->conversation_id,$participants);

        // If you don't pass the Id of the conversation means that
        // you wanna add participants giving the participants ID
        // of the current conversation
        if ( is_null( $this->conversation_id ) )
        {
            if (count($participants) > 0)
            {
                return $this->conversationJoined->addPartecipants($participants,$conversation->conversation_id);
            }
        }
        else
        {
            if (count($participants) > 0)
            {
                return $this->conversationJoined->addPartecipants($participants,$this->conversation_id);

            }
        }

        return null;
    }

    /**
     * Partecipant leave the current conversation
     *
     * @return array|\Illuminate\Database\Eloquent\Model|null|static
     */
    public function leave()
    {
        $partecipants = func_get_args();

        if (is_array($partecipants[0]))
        {
            $partecipants = $partecipants[0];
        }

        $partecipants = (!is_null($this->participants)) ? $this->participants : $partecipants;

        // check if the conversation exists between the current participants
        // I don't catch the esception here, because you'll catch when you
        // try to get messages
        $conversation = $this->exists();

        // If you don't pass the Id of the conversation means that
        // you wanna retrive the conversation messages by participants ID
        if ( is_null( $this->conversation_id ) )
        {
            return $this->conversationJoined->leaveConversation($conversation->conversation_id,$partecipants);
        }
        else
        {
            return $this->conversationJoined->leaveConversation($this->conversation_id,$partecipants);
        }
    }

    /**
     * Get messages of a conversation
     *
     * @param array $filters
     * @throws \BadMethodCallException
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function get(array $filters = [])
    {
        $partecipants = $this->participants;
        $from = $this->from;

        // check if the developer pass the participants ID or the ID
        // of the conversation for retrive the messages of it. If the developer
        // doesn't provide any of them will get an exception. You need to specified
        // one of them
        if (is_null($partecipants) and is_null($this->conversation_id))
        {
            throw new \BadMethodCallException('participants or conversation ID Not specified');
        }

        // Check if the method from() has been used
        if ( !is_null( $from ) and !is_null($this->conversation_id) )
        {
            //  we have to check that the current id of the current partecipant belongs to
            // this conversation, external participants cannot access to it
            // this can throw ConversationNotFoundException
            $this->conversationJoined->partecipantsInConversation($this->conversation_id,$from);
        }

        // check if the conversation exists between the current participants
        // I don't catch the exception here, because you'll catch when you
        // try to get messages
        $conversation = $this->exists();

        return $this->conversationRepo->getMessagesById($conversation->conversation_id,$from,$filters);
    }

    /**
     * @param array $filters
     * @throws \BadMethodCallException
     * @return mixed
     */
    public function getArchived(array $filters = [])
    {
        $from = $this->from;

        // the method from must be used here
        if ( is_null( $from ) or is_null($this->conversation_id) )
        {
            throw new \BadMethodCallException('participants or conversation ID Not specified');
        }

        //  we have to check that the current id of the current partecipant belongs to
        // this conversation, external participants cannot access to it
        // this can throw ConversationNotFoundException
        $this->conversationJoined->partecipantsInConversation($this->conversation_id,$from);

        // check if the conversation exists between the current participants
        // I don't catch the exception here, because you'll catch when you
        // try to get messages
        $this->exists();

        return $this->conversationRepo->getMessagesOnArchivedConversation($this->conversation_id,$from,$filters);

    }

    /**
     * Get the list of active conversations giving
     * an Id partecipant, it retrive the following data:
     * conversations | participants | info participants | last message
     *
     * @param array $filters
     * @throws \InvalidArgumentException
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function lists(array $filters = [])
    {
        $partecipant = $this->from;

        // the partecipant to get the list conversation must be
        // specified
        if (is_null($this->from))
        {
            throw new \InvalidArgumentException('When you try to get the list of conversations you must specify the ID on the [From] method');
        }

        // get list :)
        return $this->conversationRepo->getLists($partecipant,$filters);

    }

    /**
     * Get the lists of archived conversations giving
     * an Id partecipant, it retrive the following data:
     * conversations | participants | info participants | last message
     *
     * @param array $filters
     * @throws \InvalidArgumentException
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function archivedLists(array $filters = [])
    {
        $partecipant = $this->from;

        // the partecipant to get the list conversation must be
        // specified
        if (is_null($this->from))
        {
            throw new \InvalidArgumentException('When you try to get the list of conversations you must specify the ID on the [From] method');
        }

        // get list :)
        return $this->conversationRepo->getArchivedLists($partecipant,$filters);

    }

    /**
     * Archive a conversation
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     * @throws \InvalidArgumentException
     */
    public function archive()
    {
        $partecipants = $this->participants;
        $from = $this->from;

        if (is_null($from))
        {
            throw new \InvalidArgumentException('You must specify the ID of who does the action on the method [from]');
        }

        if (is_null($partecipants) and is_null($this->conversation_id))
        {
            throw new \InvalidArgumentException('You must specify conversation ID or participants');
        }

        // check if the conversation exists between the current participants
        // I don't catch the esception here, because you'll catch when you
        // try to archive the conversation
        $conversation = $this->exists();

        //  we have to check that the current id of the current partecipant belongs to the
        // to this conversation, external user cannot access to it
        // this can throw ConversationNotFoundException
        $this->conversationJoined->partecipantsInConversation($this->conversation_id,$from);

        // If you don't pass the Id of the conversation means that
        // you wanna archive the conversation by participants ID
        if ( is_null( $this->conversation_id ) )
        {
            return $this->conversationRepo->archive($conversation->conversation_id,$from);
        }
        else
        {
            return $this->conversationRepo->archive($this->conversation_id,$from);
        }
    }

    /**
     * Restore a conversation Archived
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function restore()
    {
        $partecipants = $this->participants;
        $from = $this->from;

        if (is_null($from))
        {
            throw new \InvalidArgumentException('You must specify the ID of does the action on the method [from]');
        }

        if (is_null($partecipants) and is_null($this->conversation_id))
        {
            throw new \InvalidArgumentException('You must specify conversation ID or participants');
        }

        // check if the conversation exists between the current participants
        // I don't catch the esception here, because you'll catch when you
        // try to archive the conversation
        $conversation = $this->exists();

        //  we have to check that the current id of the current partecipant belongs to the
        // to this conversation, external user cannot access to it
        // this can throw ConversationNotFoundException
        $this->conversationJoined->partecipantsInConversation($this->conversation_id,$from);

        // If you don't pass the Id of the conversation means that
        // you wanna restore the conversation by participants ID
        if ( is_null( $this->conversation_id ) )
        {
            return $this->conversationRepo->restore($conversation->conversation_id,$from);
        }
        else
        {
            return $this->conversationRepo->restore($this->conversation_id,$from);
        }
    }

    /**
     * Force a conversation to don't be showed from given
     * participant
     */
    public function forceRemove()
    {
        $partecipants = $this->participants;
        $from = $this->from;

        if (is_null($from))
        {
            throw new \InvalidArgumentException('You must specify the ID of does the action on the method [from]');
        }

        if (is_null($partecipants) and is_null($this->conversation_id))
        {
            throw new \InvalidArgumentException('You must specify conversation ID or participants');
        }

        // check if the conversation exists between the current participants
        // I don't catch the esception here, because you'll catch when you
        // try to archive the conversation
        $conversation = $this->exists();

        //  we have to check that the current id of the current partecipant belongs to the
        // to this conversation, external user cannot access to it
        // this can throw ConversationNotFoundException
        $this->conversationJoined->partecipantsInConversation($this->conversation_id,$from);

        // If you don't pass the Id of the conversation means that
        // you wanna archive the conversation by participants ID
        if ( is_null( $this->conversation_id ) )
        {
            return $this->conversationRepo->forceRemove($conversation->conversation_id,$from);
        }
        else
        {
            return $this->conversationRepo->forceRemove($this->conversation_id,$from);
        }
    }

    /**
     * Getter for conversation_id
     *
     * @return null
     */
    public function conversationId()
    {
        return $this->conversation_id;
    }

    /**
     * @return mixed
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param $partecipants
     * @return mixed
     */
    public function setParticipants($partecipants)
    {
        $this->participants = $partecipants;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param $from
     * @return mixed
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return mixed
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @return mixed
     */
    public function getConversationId()
    {
        return $this->conversation_id;
    }

    /**
     * @param mixed $conversation_id
     */
    public function setConversationId($conversation_id)
    {
        $this->conversation_id = $conversation_id;
    }
}