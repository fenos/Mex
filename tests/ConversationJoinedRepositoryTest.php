<?php

use Fenos\Mex\Conversations\Repositories\ConversationJoinedRepository;
use Mockery as m;

/**
 * Class ConversationJoinedRepositoryTest
 */
class ConversationJoinedRepositoryTest extends PHPUnit_Framework_TestCase{

    /**
     * @var $conversationJoined
     */
    protected $conversationJoined;
    /**
     * @var $mockModel
     */
    protected $mockModel;
    /**
     * @var $conversationJoinedModel
     */
    protected $conversationJoinedModel;

    /**
     *SetUp unit Test
     */
    public function setUp()
    {
        $this->mockModel = m::mock('Illuminate\Database\Eloquent\Model');

        $this->conversationJoined = new ConversationJoinedRepository(

            $this->conversationJoinedModel = m::mock('Fenos\Mex\Models\ConversationJoined'),
            $this->db = m::mock('Illuminate\Database\DatabaseManager')

        );
    }

    /**
     *TearDown
     */
    public function tearDown()
   {
       m::close();
   }

    public function test_add_single_participant()
    {
        $partecipantsConversartion = [

            [
                'conversation_id' => 4,
                'participant_id'  => 1,
            ],
        ];

        $this->conversationJoinedModel->shouldReceive('create')
            ->once()
            ->with($partecipantsConversartion)
            ->andReturn($this->conversationJoinedModel);

        $result = $this->conversationJoined->add($partecipantsConversartion);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_add_multiples_participants()
    {
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

        $this->db->shouldReceive('table')
            ->once()
            ->with('conversation_joined')
            ->andReturn($this->db);

        $this->db->shouldReceive('insert')
            ->with($partecipantsConversartion)
            ->once()
            ->andReturn(true);

        $result = $this->conversationJoined->add($partecipantsConversartion,true);

        $this->assertTrue($result);
    }

   public function test_check_if_the_participants_are_in_the_same_conversation()
   {
       $participants = [1,2,3];
       $escapedValues = [1,2,3,3,1,2,3];

       $conversationJoined = m::mock('Fenos\Mex\Conversations\Repositories\ConversationJoinedRepository[getEscapedValues]',[$this->conversationJoinedModel,$this->db]);

       $conversationJoined->shouldReceive('getEscapedValues')
           ->once()
           ->with($participants)
           ->andReturn([3,$escapedValues]);

       $this->db->shouldReceive('raw')
           ->once()
           ->with('select cu.conversation_id, cu.deleted_at
                        from conversation_joined cu
                        group by cu.conversation_id, cu.deleted_at
                        having SUM(cu.participant_id in ( ?,?,? )) = ? and
                               SUM(cu.participant_id not in ( ?,?,? )) = 0
                               ')
           ->andReturn($this->db);

       $this->db->shouldReceive('select')
           ->once()
           ->with($this->db,$escapedValues)
           ->andReturn(new StdClass);

       $result = $conversationJoined->inConversation([1,2,3]);

       $this->assertInstanceOf('stdClass',$result);
   }

    public function test_check_has_conversation()
    {
        $this->conversationJoinedModel->shouldReceive('where')
            ->once()
            ->with('participant_id',1)
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('where')
            ->once()
            ->with('conversation_id',1)
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('groupBy')
            ->once()
            ->with('conversation_id')
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('first')
            ->once()
            ->andReturn($this->conversationJoinedModel);

        $result = $this->conversationJoined->hasConversation(1,1);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_check_by_conversation_id()
    {
        $this->conversationJoinedModel->shouldReceive('groupBy')
            ->once()
            ->with('conversation_id')
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('having')
            ->once()
            ->with('conversation_id','=',1)
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('first')
            ->once()
            ->andReturn($this->conversationJoinedModel);

        $result = $this->conversationJoined->byConversationId(1);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_partecipants_in_conversation()
    {
        $this->conversationJoinedModel->shouldReceive('whereIn')
            ->once()
            ->with('participant_id',[1,2,3])
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('where')
            ->once()
            ->with('conversation_id',1)
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('get')
            ->once()
            ->andReturn($this->conversationJoinedModel);

        $result = $this->conversationJoined->partecipantsInConversation(1,[1,2,3]);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_single_partecipant_in_conversation()
    {
        $this->conversationJoinedModel->shouldReceive('where')
            ->once()
            ->with('participant_id',1)
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('where')
            ->once()
            ->with('conversation_id',1)
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('first')
            ->once()
            ->with()
            ->andReturn($this->conversationJoinedModel);

        $result = $this->conversationJoined->singlepartecipantInConversation(1,1);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_check_by_id()
    {
        $this->conversationJoinedModel->shouldReceive('where')
            ->once()
            ->with('participant_id',1)
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('where')
            ->once()
            ->with('conversation_id',1)
            ->andReturn($this->conversationJoinedModel);

        $this->conversationJoinedModel->shouldReceive('get')
            ->once()
            ->with()
            ->andReturn($this->conversationJoinedModel);

        $result = $this->conversationJoined->checkById(1,1);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_get_escaped_values()
    {
        $partecipants = [1,2,3];

        $result = $this->conversationJoined->getEscapedValues($partecipants);

        $this->assertEquals($result,[3,[1,2,3,3,1,2,3]]);
    }

}
