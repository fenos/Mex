<?php

namespace Fenos\Mex\Conversations\Repositories;


use Fenos\Mex\Models\ConversationJoined;
use Illuminate\Database\DatabaseManager;

/**
 * Class ConversationJoinedRepository
 * @package Fenos\Mex\Conversations\Repositories
 */
class ConversationJoinedRepository {


    /**
     * @var \Fenos\Mex\Models\ConversationJoined
     */
    private $convJoined;
    /**
     * @var DatabaseManager
     */
    private $db;

    /**
     * @param ConversationJoined $convJoined
     * @param DatabaseManager $db
     */
    function __construct(ConversationJoined $convJoined, DatabaseManager $db)
    {
        $this->convJoined = $convJoined;
        $this->db = $db;
    }

    /**
     * Add participants to a conversation
     *
     * @param $partecipants
     * @param bool $multiPartecipants
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function add($partecipants,$multiPartecipants = false)
    {
        if (!$multiPartecipants)
        {
            $insert = $this->convJoined->create($partecipants);
        }
        else
        {
            $insert = $this->db->table('conversation_joined')->insert($partecipants);
        }

        return $insert;

    }

    /**
     * Check if given participants are
     * in a conversation
     *
     * @param $participants
     * @return mixed
     */
    public function inConversation($participants)
    {
        list($num_user_in_conversation, $valuesEscaped) = $this->getEscapedValues($participants);

        // prepare question marks for the query
        $questionmarks = str_repeat("?,", $num_user_in_conversation-1) . "?";

        $query = $this->db->select(
                $this->db->raw('select cu.conversation_id, cu.deleted_at
                        from conversation_joined cu
                        group by cu.conversation_id, cu.deleted_at
                        having SUM(cu.participant_id in ( '.$questionmarks.' )) = ? and
                               SUM(cu.participant_id not in ( '.$questionmarks.' )) = 0
                               '),
                $valuesEscaped);

        return $query;

    }

    /**
     * @param $conversation_id
     * @param $participant
     * @return mixed
     */
    public function hasConversation($conversation_id,$participant)
    {
        return $this->convJoined->where('participant_id',$participant)
                                ->where('conversation_id',$conversation_id)
                                ->groupBy('conversation_id')
                                ->first();
    }

    /**
     * @param $conversation_id
     * @return mixed
     */
    public function byConversationId($conversation_id)
    {
        return $this->convJoined->groupBy('conversation_id')
                                ->having('conversation_id','=',$conversation_id)
                                ->first();
    }

    /**
     * @param $conversation_id
     * @param array $partecipants
     * @return mixed
     */
    public function partecipantsInConversation($conversation_id,array $partecipants)
    {
        return $this->convJoined->whereIn('participant_id',$partecipants)
                         ->where('conversation_id',$conversation_id)->get();
    }

    /**
     * @param $conversation_id
     * @param $partecipant
     * @return mixed
     */
    public function singlepartecipantInConversation($conversation_id,$partecipant)
    {

        return $this->convJoined->where('participant_id',$partecipant)
            ->where('conversation_id',$conversation_id)->first();
    }

    /**
     * @param $user_id
     * @param $conversation_id
     * @return mixed
     */
    public function checkById($user_id, $conversation_id)
    {
        return $this->convJoined->where('conversation_id',$conversation_id)
                    ->where('participant_id',$user_id)->get();
    }

    /**
     * Get the escaped values for the exists
     * method, it is raw query so we improve security
     *
     * @param $partecipants
     * @return array
     */
    public function getEscapedValues($partecipants)
    {
        // Get the number of participants
        $num_user_in_conversation = count($partecipants);

        // Need to prepare the array for retrive the real
        // values that will be switched with the questions
        // marks
        $arrayReplace = [];

        foreach ($partecipants as $partecipant) {
            // Partecipants Array
            $arrayReplace[] = $partecipant;
        }

        // After the first value "participants", I put the numbers of them
        $arrayReplace[] = $num_user_in_conversation;

        // as third parameter I need to duplicate the participants
        // I duplicate the array. It has even the numbers of participants
        // duplicated so next step.
        $valuesEscaped = array_merge($arrayReplace, $arrayReplace);

        // Here remove the extra value from the array
        // and now I have the values ready
        array_pop($valuesEscaped);

        return array($num_user_in_conversation, $valuesEscaped);
    }
}