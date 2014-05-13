<?php

use Mockery as m;

/**
 * Class ConversationJoinedTest
 */
class ConversationJoinedTest extends PHPUnit_Framework_TestCase {

    /**
     * @var $conversationJoined
     */
    protected $conversationJoined;
    /**
     * @var $convJoinedRepo
     */
    protected $convJoinedRepo;
    /**
     * @var $carbon
     */
    protected $carbon;

    /**
     * SetUp unit Test
     */
    public function setUp()
    {
        $this->conversationJoined = new \Fenos\Mex\Conversations\ConversationJoined(
          $this->convJoinedRepo = m::mock('Fenos\Mex\Conversations\Repositories\ConversationJoinedRepository'),
          $this->carbon = m::mock('Carbon\Carbon')
        );
    }

    /**
     * TearDown
     */
    public function tearDown()
    {
        m::close();
    }

    public function test_add_multi_participants_in_conversation()
    {
        $partecipants = [1,2,3];

        $this->carbon->shouldReceive('now')
            ->times(6)
            ->andReturn('12-12-12 12:12');

        $partecipantsConversartion = [

             [
                'conversation_id' => 4,
                'participant_id'  => 1,
                'created_at'      => '12-12-12 12:12',
                'updated_at'      => '12-12-12 12:12',
             ],

             [
                'conversation_id' => 4,
                'participant_id'  => 2,
                'created_at'      => '12-12-12 12:12',
                'updated_at'      => '12-12-12 12:12',
             ],

             [
                'conversation_id' => 4,
                'participant_id'  => 3,
                'created_at'      => '12-12-12 12:12',
                'updated_at'      => '12-12-12 12:12',
             ]
        ];

        $this->convJoinedRepo->shouldReceive('add')
            ->with($partecipantsConversartion,true)
            ->once()
            ->andReturn(true);

        $result = $this->conversationJoined->addPartecipants($partecipants,4);

        $this->assertTrue($result);
    }

    public function test_add_single_participants_in_conversation()
    {
        $partecipants = [1];

        $partecipantsConversartion = [

            [
                'conversation_id' => 4,
                'participant_id'  => 1,
            ],
        ];

        $this->convJoinedRepo->shouldReceive('add')
            ->with($partecipantsConversartion[0],false)
            ->once()
            ->andReturn(true);

        $result = $this->conversationJoined->addPartecipants($partecipants,4);

        $this->assertTrue($result);
    }

    public function test_check_by_participantsId()
    {
        $partecipants = [1,2,3];

        $this->convJoinedRepo->shouldReceive('inConversation')
            ->once()
            ->with($partecipants)
            ->andReturn([new StdClass]);

        $result = $this->conversationJoined->checkByPartecipantsId($partecipants);

        $this->assertInstanceOf('stdClass',$result);
    }

    /**
     * @expectedException Fenos\Mex\Exceptions\ConversationNotFoundException
     * */
    public function test_check_conversation_by_participants_id_but_is_not_match()
    {
        $partecipants = [1,2,3];

        $this->convJoinedRepo->shouldReceive('inConversation')
            ->once()
            ->with($partecipants)
            ->andReturn(null);

        $this->conversationJoined->checkByPartecipantsId($partecipants);
    }

    public function test_check_if_conversation_exists_by_conversation_id()
    {
        $conversation_id = 1;

        $this->convJoinedRepo->shouldReceive('byConversationId')
            ->once()
            ->with($conversation_id)
            ->andReturn(m::mock('Fenos\Mex\Models\ConversationJoined'));

        $result = $this->conversationJoined->checkByConversationID($conversation_id);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    /**
     * @expectedException Fenos\Mex\Exceptions\ConversationNotFoundException
     * */
    public function test_check_if_conversation_exists_by_conversation_id_but_it_not_found()
    {
        $conversation_id = 1;

        $this->convJoinedRepo->shouldReceive('byConversationId')
            ->once()
            ->with($conversation_id)
            ->andReturn(null);

        $this->conversationJoined->checkByConversationID($conversation_id);
    }

    public function test_if_the_partecipants_that_want_to_join_in_a_conversation_are_already_not_there()
    {
        $participants = [1,2,3];
        $modelJoined = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $this->convJoinedRepo->shouldReceive('partecipantsInConversation')
            ->once()
            ->with(1,$participants)
            ->andReturn($modelJoined);

        $result = $this->conversationJoined->areNewPartecipants(1,$participants);

        $this->assertEquals($participants,$result);

    }

    public function test_if_the_partecipants_that_want_to_join_in_a_conversation_are_already_not_there_yes_1_is_there()
    {
        $participants = [1,2,3];
        $modelJoined = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $this->convJoinedRepo->shouldReceive('partecipantsInConversation')
            ->once()
            ->with(1,$participants)
            ->andReturn($modelJoined);

        $result = $this->conversationJoined->areNewPartecipants(1,$participants);

        unset($result[0]);

        $this->assertEquals([1=>2,2=>3],$result);
    }

    public function test_partecipants_in_conversation()
    {
        $partecipants = [1,2,3];

        $this->convJoinedRepo->shouldReceive('hasConversation')
            ->once()
            ->with(1,$partecipants)
            ->andReturn(m::mock('Fenos\Mex\Models\ConversationJoined'));

        $result = $this->conversationJoined->partecipantsInConversation(1,$partecipants);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    /**
     * @expectedException Fenos\Mex\Exceptions\ConversationNotFoundException
     * */
    public function test_partecipants_in_conversation_but_not_found()
    {
        $partecipants = [1,2,3];

        $this->convJoinedRepo->shouldReceive('hasConversation')
            ->once()
            ->with(1,$partecipants)
            ->andReturn(null);

        $this->conversationJoined->partecipantsInConversation(1,$partecipants);
    }

    public function test_leave_conversation_participants()
    {
        $modelJoined = m::mock('Fenos\Mex\Models\ConversationJoined');
        $participants = [1,2];


        $this->convJoinedRepo->shouldReceive('singlepartecipantInConversation')
            ->once()
            ->with(1,1)
            ->andReturn($modelJoined);

        $modelJoined->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->convJoinedRepo->shouldReceive('singlepartecipantInConversation')
            ->once()
            ->with(1,2)
            ->andReturn($modelJoined);

        $modelJoined->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $result = $this->conversationJoined->leaveConversation(1,$participants);

        $this->assertEquals($result,[$modelJoined,$modelJoined]);
    }
}
