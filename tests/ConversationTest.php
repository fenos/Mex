<?php

use Fenos\Mex\Conversations\Conversation;
use Mockery as m;

/**
 * Class ConversationTest
 */
class ConversationTest extends PHPUnit_Framework_TestCase {

    /**
     * @var $conversation
     */
    protected $conversation;
    /**
     * @var $mockModel
     */
    protected $mockModel;
    /**
     * @var $conversation_id
     */
    protected $conversation_id;
    /**
     * @var $convRepo
     */
    protected $convRepo;
    /**
     * @var $convJoin
     */
    protected $convJoin;

    /**
     * Set Up Unit test
     */
    public function setUp()
    {

        $this->mockModel = m::mock('Illuminate\Database\Eloquent\Model');

        $this->conversation = new Conversation(

            $this->conversation_id = 1,
            $this->convRepo = m::mock('Fenos\Mex\Conversations\Repositories\ConversationRepository'),
            $this->convJoin = m::mock('Fenos\Mex\Conversations\ConversationJoined')

        );
    }


    /**
     *
     */
    public function tearDown()
    {
        m::close();
    }

    public function test_getter_from()
    {

        $result = $this->conversation->from(1);

        $this->assertInstanceOf('Fenos\Mex\Conversations\Conversation',$result);
    }

    /**
     * @expectedException Fenos\Mex\Exceptions\ConversationNotFoundException
     * */
    public function test_from_inserting_a_not_number_value()
    {
        $this->conversation->from('Mex');
    }

    public function test_getter_partecipant()
    {
        $result = $this->conversation->participants(1,2,3);

        $this->assertEquals([1,2,3],$this->conversation->getParticipants());

        $this->assertInstanceOf('Fenos\Mex\Conversations\Conversation',$result);

    }

    public function test_getter_participants_having_from_method_as_well_omitting_on_participants()
    {
        $result = $this->conversation->from(1)->participants(2,3);

        $this->assertEquals([1,2,3],$this->conversation->getParticipants());

        $this->assertInstanceOf('Fenos\Mex\Conversations\Conversation',$result);
    }

    public function test_getter_participants_having_from_method_as_well_both_ids()
    {
        $result = $this->conversation->from(1)->participants(1,2,3);

        $this->assertEquals([1,2,3],$this->conversation->getParticipants());

        $this->assertInstanceOf('Fenos\Mex\Conversations\Conversation',$result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * */
    public function test_getter_partecipants_inserting_only_one_partecipant()
    {
        $this->conversation->participants(3);
    }

    public function test_create_a_new_conversation()
    {
        $informations_new_conversation = [
            'founder_id' => 1
        ];

        $partecipants = [1,2,3];

        $conversationModel = m::mock('Fenos\Mex\Models\Conversation')->makePartial();

        $this->convRepo->shouldReceive('create')
            ->once()
            ->with($informations_new_conversation)
            ->andReturn($conversationModel);

        $conversationModel->id = 1;

        $this->conversation->setParticipants($partecipants);

        $this->convJoin->shouldReceive('addPartecipants')
            ->once()
            ->with($partecipants,1)
            ->andReturn(m::mock('Fenos\Mex\Models\ConversationJoined'));

        $result = $this->conversation->create($informations_new_conversation);

        $this->assertInstanceOf('Fenos\Mex\Conversations\Conversation',$result);

    }

    public function test_exists_conversation_giving_conversation_id()
    {
        $partecipants = [1,2,3];
        $conversation_id = 1;

        $this->conversation->setParticipants($partecipants);
        $this->conversation->setConversationId($conversation_id);

        $this->convJoin->shouldReceive('checkByConversationID')
            ->once()
            ->with(1)
            ->andReturn(m::mock('Fenos\Mex\Models\ConversationJoined'));

        $result = $this->conversation->exists();

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_exists_conversation_giving_only_partecipants()
    {
        $partecipants = [1,2,3];
        $modelConvJoined = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $this->conversation->setParticipants($partecipants);

        $modelConvJoined->conversation_id = 1;

        $this->convJoin->shouldReceive('checkByConversationID')
            ->once()
            ->with(1)
            ->andReturn(m::mock('Fenos\Mex\Models\ConversationJoined'));

        $result = $this->conversation->exists();

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    /**
     * @expectedException \BadMethodCallException
     * */
    public function test_exists_conversation_omitting_partecipants_and_conversation_id()
    {
        $this->conversation->exists();
    }

    /**
     * @expectedException Fenos\Mex\Exceptions\COnversationNotFoundException
     * */
    public function test_exists_conversation_not_founding_any_result()
    {
        $partecipants = [1,2,3];
        $modelConvJoined = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $this->conversation->setParticipants($partecipants);

        $modelConvJoined->conversation_id = 1;

        $this->convJoin->shouldReceive('checkByConversationID')
            ->once()
            ->with(1)
            ->andReturn(null);

        $this->conversation->exists();
    }

    public function test_join_partecipants_in_conversation()
    {
        $partecipants = [1,2,3];
        $this->conversation->setParticipants($partecipants);
        $conversationJoinedModel = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();


        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);

        $conversation->shouldReceive('exists')
            ->once()
            ->andReturn($conversationJoinedModel);

        $this->convJoin->shouldReceive('areNewPartecipants')
            ->once()
            ->with(1,$partecipants)
            ->andReturn([1,2,3]);

        $conversationJoinedModel->conversation_id = 1;

        $this->convJoin->shouldReceive('addPartecipants')
            ->once()
            ->with($partecipants,1)
            ->andReturn(m::mock('Fenos\Mex\Models\ConversationJoined'));

        $result = $conversation->join(1,2,3);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_leave_conversation()
    {
        $partecipants = [1,2,3];
        $this->conversation->setParticipants($partecipants);

        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);

        $conversation->shouldReceive('exists')
            ->once()
            ->andReturn(m::mock('Fenos\Mex\Models\ConversationJoined'));

        $this->convJoin->shouldReceive('leaveConversation')
            ->once()
            ->with(1,$partecipants)
            ->andReturn(m::mock('Fenos\Mex\Models\ConversationJoined'));

        $result = $conversation->leave(1,2,3);

        $this->assertInstanceOf('Fenos\Mex\Models\ConversationJoined',$result);
    }

    public function test_get_messages_conversation_no_filters()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversationJoinedModel = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $conversation->shouldReceive('exists')
            ->once()
            ->andReturn($conversationJoinedModel);

        $conversationJoinedModel->conversation_id = 1;

        $this->convRepo->shouldReceive('getMessagesById')
            ->once()
            ->with(1,null,[])
            ->andReturn(m::mock('Illuminate\Database\Eloquent\Collection'));

        $result = $conversation->get();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection',$result);
    }

    public function test_get_messages_conversation_filtring_by_partecipant()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversationJoinedModel = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $from = 1;
        $conversation->setFrom($from);

        $this->convJoin->shouldReceive('partecipantsInConversation')
            ->once()
            ->with(1, 1)
            ->andReturn($conversationJoinedModel);

        $conversation->shouldReceive('exists')
            ->once()
            ->andReturn($conversationJoinedModel);

        $conversationJoinedModel->conversation_id = 1;

        $this->convRepo->shouldReceive('getMessagesById')
            ->once()
            ->with(1,1,[])
            ->andReturn(m::mock('Illuminate\Database\Eloquent\Collection'));

        $result = $conversation->get();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection',$result);
    }

    public function test_get_conversation_archivied()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversationJoinedModel = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $conversation->setFrom(1);

        $this->convJoin->shouldReceive('partecipantsInConversation')
            ->once()
            ->with(1,1)
            ->andReturn($conversationJoinedModel);

        $conversation->shouldReceive('exists')
            ->once()
            ->andReturn($conversationJoinedModel);

        $this->convRepo->shouldReceive('getMessagesOnArchivedConversation')
            ->once()
            ->with(1,1,[])
            ->andReturn(m::mock('Fenos\Mex\Models\Conversation'));

        $result = $conversation->getArchived();

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    /**
     * @expectedException \BadMethodCallException
     * */
    public function test_get_conversation_archivied_omitting_the_from_id()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);

        $conversation->getArchived();
    }

    public function test_get_lists_coversations()
    {
        $partecipants = [1,2,3];
        $this->conversation->setParticipants($partecipants);
        $this->conversation->setFrom($partecipants);

        $this->convRepo->shouldReceive('getLists')
            ->once()
            ->with($partecipants,[])
            ->andReturn(m::mock('Fenos\Mex\Models\Conversation'));

        $result = $this->conversation->lists();

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * */
    public function test_get_lists_ommitting_the_from_partecipant()
    {
       $this->conversation->lists();
    }

    public function test_get_archived_lists()
    {
        $partecipants = [1,2,3];
        $this->conversation->setParticipants($partecipants);
        $this->conversation->setFrom($partecipants);

        $this->convRepo->shouldReceive('getArchivedLists')
            ->once()
            ->with($partecipants,[])
            ->andReturn(m::mock('Fenos\Mex\Models\Conversation'));

        $result = $this->conversation->archivedlists();

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * */
    public function test_get_archived_lists_omitting_from_partecipant()
    {
        $this->conversation->lists();
    }

    public function test_archive_a_conversation()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversationJoinedModel = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $conversation->setFrom(1);

        $conversation->shouldReceive('exists')
            ->once()
            ->andReturn($conversationJoinedModel);

        $this->convJoin->shouldReceive('partecipantsInConversation')
            ->once()
            ->with(1,1)
            ->andReturn($conversationJoinedModel);

        $this->convRepo->shouldReceive('archive')
            ->once()
            ->with(1,1)
            ->andReturn(m::mock('Fenos\Mex\Models\Conversation'));

        $result = $conversation->archive();

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);

    }

    /**
     * @expectedException \InvalidArgumentException
     * */
    public function test_archive_a_conversation_omitting_from()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversation->archive();
    }

    public function test_restore_conversation()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversationJoinedModel = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $conversation->setFrom(1);

        $conversation->shouldReceive('exists')
            ->once()
            ->andReturn($conversationJoinedModel);

        $this->convJoin->shouldReceive('partecipantsInConversation')
            ->once()
            ->with(1,1)
            ->andReturn($conversationJoinedModel);

        $this->convRepo->shouldReceive('restore')
            ->once()
            ->with(1,1)
            ->andReturn(m::mock('Fenos\Mex\Models\Conversation'));

        $result = $conversation->restore();

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * */
    public function test_restore_conversation_omitting_from()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversation->restore();
    }

    public function test_force_remove_conversation()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversationJoinedModel = m::mock('Fenos\Mex\Models\ConversationJoined')->makePartial();

        $conversation->setFrom(1);

        $conversation->shouldReceive('exists')
            ->once()
            ->andReturn($conversationJoinedModel);

        $this->convJoin->shouldReceive('partecipantsInConversation')
            ->once()
            ->with(1,1)
            ->andReturn($conversationJoinedModel);

        $this->convRepo->shouldReceive('forceRemove')
            ->once()
            ->with(1,1)
            ->andReturn(m::mock('Fenos\Mex\Models\Conversation'));

        $result = $conversation->forceRemove();

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * */
    public function test_force_remove_conversation_omitting_from()
    {
        $conversation = m::mock('Fenos\Mex\Conversations\Conversation[exists]',[$this->conversation_id,$this->convRepo,$this->convJoin]);
        $conversation->forceRemove();
    }



}

