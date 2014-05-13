<?php

namespace Fenos\Mex\Conversations;


use Carbon\Carbon;
use Fenos\Mex\Conversations\Repositories\ConversationJoinedRepository;
use Fenos\Mex\Exceptions\ConversationNotFoundException;

/**
 * Class ConversationJoined
 * @package Fenos\Mex\Conversations
 */
class ConversationJoined {


    /**
     * @var ConversationJoinedRepository
     */
    private $convJoinedRepo;
    /**
     * @var \Carbon\Carbon
     */
    private $carbon;

    /**
     * @param ConversationJoinedRepository $convJoinedRepo
     * @param \Carbon\Carbon $carbon
     */
    function __construct(ConversationJoinedRepository $convJoinedRepo,Carbon $carbon)
    {

        $this->convJoinedRepo = $convJoinedRepo;
        $this->carbon = $carbon;
    }

    /**
     * @param $participants
     * @param $conversation_id
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function addPartecipants($participants,$conversation_id)
    {
        $partecipantsConversartion = [];

        if (count($participants) > 1)
        {

            foreach($participants as $key => $partecipant)
            {

                    $partecipantsConversartion[$key] = [

                        'conversation_id' => (int)$conversation_id,
                        'participant_id'  => $partecipant,
                        'created_at'      => $this->carbon->now(),
                        'updated_at'      => $this->carbon->now(),
                    ];
            }

            $multiPartecipants = true;
        }
        else
        {
            $partecipantsConversartion = [

                'conversation_id' => $conversation_id,
                'participant_id'  => $participants[0]
            ];

            $multiPartecipants = false;
        }

        return $this->convJoinedRepo->add($partecipantsConversartion,$multiPartecipants);
    }

    /**
     * Check if a conversation exist giving
     * the participants ID
     *
     * @param array $partecipants
     * @return mixed
     * @throws \Fenos\Mex\Exceptions\ConversationNotFoundException
     */
    public function checkByPartecipantsId(array $partecipants)
    {
        $check = $this->convJoinedRepo->inConversation($partecipants);

        if (count($check) === 0)
        {
            throw new ConversationNotFoundException('Conversation not found');
        }

        return $check[0];
    }

    /**
     * Check if a conversation exists
     * giving the conversation ID
     *
     * @param $conversation_id
     * @return mixed
     * @throws \Fenos\Mex\Exceptions\ConversationNotFoundException
     */
    public function checkByConversationID($conversation_id)
    {
        $check = $this->convJoinedRepo->byConversationId($conversation_id);

        if ( is_null($check) )
        {
            throw new ConversationNotFoundException('Conversation not found');
        }

        return $check;
    }

    /**
     * @param $conversation_id
     * @param $partecipants
     * @return array
     */
    public function areNewPartecipants($conversation_id,$partecipants)
    {

        // if some of the participants are already in a conversation
        // the value will stored in the $check variable
        $check = $this->convJoinedRepo->partecipantsInConversation($conversation_id,$partecipants);

        // the query has found some partecipant already in the
        // conversation so we have to remove it
        if (!is_null($check))
        {
            foreach($check as $partecipant)
            {
                // check witch of thoose participants are alredy in the conversation
                $p_key = array_search($partecipant->participant_id,$partecipants);

                if ($p_key !== false)
                {
                    unset($partecipants[(int)$p_key]);
                }
            }

            // return participants without who already in
            return $partecipants;
        }

        return $partecipants;
    }

    /**
     * Check if the current partecipant belongs to
     * the conversation of the given id
     *
     * @param $conversation_id
     * @param $partecipant
     * @return mixed
     * @throws \Fenos\Mex\Exceptions\ConversationNotFoundException
     */
    public function partecipantsInConversation($conversation_id,$partecipant)
    {
        // if the user that try to see the conversation is not
        // on the conversation it self will receive ConversationNotFoundException
        $check = $this->convJoinedRepo->hasConversation($conversation_id,$partecipant);

        if (is_null($check))
        {
            throw new ConversationNotFoundException('Conversation not found');
        }

        return $check;
    }

    /**
     * Leave conversation participants
     *
     * @param $conversation_id
     * @param array $participants
     * @return \Illuminate\Database\Eloquent\Model|null|static|array
     */
    public function leaveConversation($conversation_id, array $participants)
    {
        $conversationParticipant = [];

        foreach($participants as $key => $partecipant)
        {
            $conversationParticipant[$key] = $this->convJoinedRepo->singlepartecipantInConversation($conversation_id,$partecipant);

            if (!is_null($conversationParticipant[$key]))
            {
                $conversationParticipant[$key]->delete();
            }
            else
            {
                $conversationParticipant[$key] = "error";
            }
        }

        return $conversationParticipant;

    }
}