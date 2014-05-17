<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 08/05/14
 * Time: 10:55
 */

namespace Fenos\Mex\Conversations\Repositories;


use Fenos\Mex\Exceptions\ConversationNotFoundException;
use Fenos\Mex\Models\Conversation;
use Fenos\Mex\Models\DeletedConversation;
use Illuminate\Database\DatabaseManager as Db;

/**
 * Class ConversationRepository
 * @package Fenos\Mex\Conversations\Repositories
 */
class ConversationRepository {


    /**
     * @var \Fenos\Mex\Models\Conversation
     */
    private $conversation;
    /**
     * @var DeletedConversation
     */
    private $deletedConversation;
    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    private $db;

    /**
     * @param Conversation $conversation
     * @param \Fenos\Mex\Models\DeletedConversation $deletedConversation
     * @param \Illuminate\Database\DatabaseManager $db
     */
    function __construct(Conversation $conversation, DeletedConversation $deletedConversation,Db $db)
    {
        $this->conversation = $conversation;
        $this->deletedConversation = $deletedConversation;
        $this->db = $db;
    }

    /**
     * Create a conversation
     *
     * @param array $info_conversation
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function create(array $info_conversation)
    {
        return $this->conversation->create($info_conversation);
    }

    /**
     * Get messages of a conversation by
     * conversation ID
     *
     * @param $conversation_id
     * @param bool|null $from
     * @param $filters
     * @throws \Fenos\Mex\Exceptions\ConversationNotFoundException
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function getMessagesById($conversation_id,$from,$filters)
    {
        // get the conversation information
        if (!is_null($from))
        {
            $conversation = $this->conversationReadable($conversation_id, $from);
        }
        else
        {
            $conversation = $this->conversation->find($conversation_id);
        }

        // if the conversation has been found go to associate the
        // relations to it, else throw excpetion
        if (!is_null( $conversation ))
        {
            // If $from value is null means that has been not used
            // the from() method to specify wich user get messages.
            if ( ! is_null($from) )
            {
                // the method from() has been used so i get the messages without the deleted messages
                // of this partecipant
                return $conversation->load(['participants.participant','messages' => function($messages) use($from, $conversation_id,$filters){
                        $messages->whereNotExists(function($query) use($from, $conversation_id,$filters){
                            $query->select($this->db->raw($from))
                                  ->from('deleted_messages')
                                  ->whereRaw('deleted_messages.message_id = messages.id')
                                  ->where('messages.participant_id',$from);
                        })->where('conversation_id',$conversation_id);

                        $this->addFilters($messages,$filters);

                    }, 'messages.participant']);
            }

            // Get all messages of the conversation
            return $conversation->load(['participants.participant','messages', 'messages.participant']);
        }

        // the conversation has been not found
        throw new ConversationNotFoundException('Conversation Not found');
    }

    /**
     * Get messages of a conversation archived
     *
     * @param $conversation_id
     * @param $from
     * @param $filters
     * @throws \Fenos\Mex\Exceptions\ConversationNotFoundException
     * @return mixed
     */
    public function getMessagesOnArchivedConversation($conversation_id, $from,$filters)
    {
        $conversationArchived = $this->conversationArchived($conversation_id, $from);

        if (!is_null($conversationArchived))
        {
            return $conversationArchived->load(['participants.participant','messages' => function($messages) use($from, $conversation_id, $filters){
                    $messages->whereNotExists(function($query) use($from, $conversation_id){
                        $query->select($this->db->raw($from))
                            ->from('deleted_messages')
                            ->whereRaw('deleted_messages.message_id = messages.id')
                            ->where('messages.participant_id',$from);
                    })->where('conversation_id',$conversation_id);

                    $this->addFilters($messages,$filters);

                }, 'messages.participant']);
        }

        // the conversation has been not found
        throw new ConversationNotFoundException('Conversation Not found');
    }

    /**
     * Update time of a conversation
     *
     * @param $conversation_Object
     * @return mixed
     */
    public function updateTime($conversation_Object)
    {
        return $conversation_Object->touch();
    }

    /**
     * Return lists of conversations with participants
     * informations, and last message of the conversation
     *
     * @param $from
     * @param $filters
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getLists($from,$filters)
    {
        $conversation = $this->conversation->with(['participants.participant','participants' => function($query) use ($from,$filters){

                if (array_key_exists('founder',$filters))
                {
                    if (!$filters['founder'])
                    {
                        $query->where('id','!=',$from);
                    }
                }

            },'messages' => function($message) use($from) {

                    $message->whereNotExists(function($query) use($from){

                        $query->select($this->db->raw($from))
                            ->from('deleted_messages')
                            ->whereRaw('deleted_messages.message_id = messages.id')
                            ->where('messages.participant_id',$from);

                    })->groupBy('conversation_id')
                      ->where('messages.created_at','=',function($query){
                            $query->select($this->db->raw('Max(m.created_at)'))
                                  ->from('messages as m')
                                ->whereRaw('m.conversation_id = messages.conversation_id');
                      });

                 }])->whereNotExists(function ($conv) use ($from) {

                        $conv->select($this->db->raw($from))
                                ->from('deleted_conversations')
                                ->whereRaw('deleted_conversations.conversation_id = conversations.id')
                                ->where('deleted_conversations.participant_id', $from);

            });

            $this->addFilters($conversation,$filters);

            return $conversation->get();
    }

    /**
     * Return lists of conversations with participants
     * participants informations, and last message of the conversation
     *
     * @param $from
     * @param $filters
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getArchivedLists($from,$filters)
    {
        $conversationLists = $this->conversation->with(['participants.partecipant','participants' => function($query) use ($from,$filters){

                if (array_key_exists('founder',$filters))
                {
                    if (!$filters['founder'])
                    {
                        $query->where('id','!=',$from);
                    }
                }

            }, 'messages' => function($message) use($from) {

                $message->whereNotExists(function($query) use($from){

                    $query->select($this->db->raw($from))
                        ->from('deleted_messages')
                        ->whereRaw('deleted_messages.message_id = messages.id')
                        ->where('messages.participant_id',$from);

                })->groupBy('conversation_id')
                    ->where('messages.created_at','=',function($query){
                        $query->select($this->db->raw('Max(m.created_at)'))
                            ->from('messages as m')
                            ->whereRaw('m.conversation_id = messages.conversation_id');
                    });

            }])->whereExists(function ($conv) use ($from) {

                $conv->select($this->db->raw($from))
                    ->from('deleted_conversations')
                    ->whereRaw('deleted_conversations.conversation_id = conversations.id')
                    ->where('deleted_conversations.participant_id', $from)
                    ->where('deleted_conversations.archived','=',0);
            });

        $this->addFilters($conversationLists,$filters);

        return $conversationLists->get();
    }

    /**
     * Archive a conversation
     *
     * @param $conversation_id
     * @param $partecipant
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function archive($conversation_id,$partecipant)
    {
        $conversationDeleted = $this->deletedConversation->findOrCreate($conversation_id,$partecipant);

        $conversationDeleted->conversation_id = $conversation_id;
        $conversationDeleted->participant_id = $partecipant;
        $conversationDeleted->archived = 0;

        if ($conversationDeleted->save())
        {
            return $conversationDeleted;
        }

        return false;
    }

    /**
     * Select only the conversations active
     *
     * @param $conversation_id
     * @param $from
     * @return mixed
     */
    public function conversationActive($conversation_id, $from)
    {
        $conversation = $this->conversation->whereNotExists(function ($query) use ($from, $conversation_id) {

            $query->select($this->db->raw($from))
                ->from('deleted_conversations')
                ->whereRaw('deleted_conversations.conversation_id = conversations.id')
                ->where('deleted_conversations.participant_id', $from);

        })->find($conversation_id);

        return $conversation;
    }

    /**
     * Get conversation archivied giving the id
     * of it and the user
     *
     * @param $conversation_id
     * @param $from
     * @return mixed
     */
    public function conversationArchived($conversation_id, $from)
    {
        $conversation = $this->conversation->whereExists(function ($query) use ($from, $conversation_id) {

            $query->select($this->db->raw($from))
                ->from('deleted_conversations')
                ->whereRaw('deleted_conversations.conversation_id = conversations.id')
                ->where('deleted_conversations.participant_id', $from)
                ->where('deleted_conversations.archived','=',0);

        })->find($conversation_id);

        return $conversation;
    }


    /**
     * Get conversation archived and not
     * of the given partecipant
     *
     * @param $conversation_id
     * @param $from
     * @return mixed
     */
    public function conversationReadable($conversation_id, $from)
    {
        return $this->conversation->whereNotExists(function ($query) use ($from, $conversation_id) {

            $query->select($this->db->raw($from))
                ->from('deleted_conversations')
                ->whereRaw('deleted_conversations.conversation_id = conversations.id')
                ->where('deleted_conversations.participant_id', $from)
                ->where('deleted_conversations.archived','=',1);

        })->find($conversation_id);
    }

    /**
     * Restore conversation archived
     *
     * @param $conversation_id
     * @param $from
     * @return bool
     */
    public function restore($conversation_id, $from)
    {
        $conversation_archived = $this->deletedConversation->where('conversation_id',$conversation_id)
                                        ->where('participant_id',$from)
                                        ->first();

        if (!is_null($conversation_archived))
        {
            return $conversation_archived->delete();
        }

        return false;
    }

    /**
     * Force to remove a conversation of the
     * given partecipant this kind of delete
     * will not show the conversation to the current
     * partecipant
     *
     * @param $conversation_id
     * @param $participant
     * @return bool
     */
    public function forceRemove($conversation_id, $participant)
    {
        $conversationDeleted = $this->deletedConversation->findOrCreate($conversation_id,$participant);

        $conversationDeleted->conversation_id = $conversation_id;
        $conversationDeleted->participant_id = $participant;
        $conversationDeleted->archived = 1;

        if ($conversationDeleted->save())
        {
            return $conversationDeleted;
        }

        return false;
    }

    /**
     * Add filters to the selects queries
     *
     * @param $objectBuilder
     * @param array $filters
     */
    public function addFilters($objectBuilder,array $filters)
    {
        if (count($filters) > 0)
        {
            foreach($filters as $key => $filter)
            {
                if ($key == "orderBy")
                {
                    $field = 'created_at';
                    $objectBuilder->{$key}($field,$filter);
                }

                if ($key == "limit")
                {
                    $objectBuilder->{$key}($filter);
                }

                if ($key == "paginate")
                {
                    return $objectBuilder->{$key}($filter);
                }
            }
        }
    }
}