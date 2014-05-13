<?php

use Fenos\Mex\Conversations\Repositories\ConversationRepository;
use Mockery as m;

class ConversationRepositoryTest extends PHPUnit_Framework_TestCase {

    protected $conversationModel;
    protected $deletedConversationModel;
    protected $db;

    public function setUp()
    {
        $this->convRepo = new ConversationRepository(
            $this->conversationModel = m::mock('Fenos\Mex\Models\Conversation'),
            $this->deletedConversationModel = m::mock('Fenos\Mex\Models\DeletedConversation'),
            $this->db = m::mock('Illuminate\Database\DatabaseManager')
        );
    }

    public function tearDown()
    {
        m::close();
    }

    public function test_create_conversation()
    {
        $informations_conversation = [
          'founder_id' => 1
        ];

        $this->conversationModel->shouldReceive('create')
            ->once()
            ->with($informations_conversation)
            ->andReturn($this->conversationModel);

        $result = $this->convRepo->create($informations_conversation);

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    public function test_get_messages_by_conversation_id()
    {
        $convRepo = m::mock('Fenos\Mex\Conversations\Repositories\ConversationRepository[conversationReadable]',[$this->conversationModel,$this->deletedConversationModel,$this->db]);

        $convRepo->shouldReceive('conversationReadable')
            ->with(1,1)
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('load')
            ->once()
        // Clusure Bit tricky to review test with it
//            ->with([0 => 'participants.participant','messages' => m::on(function($x)
//                {
//                    $x->shouldReceive('whereNotExists')
//                        ->once()
//                        ->with(m::on(function($where) {
//
//                            $where->shouldReceive('select')
//                                ->once()
//                                ->with(1)
//                                ->andReturn($this->db);
//
//                            $where->shouldReceive('from')
//                                ->once()
//                                ->with('deleted_messages')
//                                ->andReturn($this->db);
//
//                            $where->shouldReceive('whereRaw')
//                                ->once()
//                                ->with('deleted_messages.message_id = messages.id')
//                                ->andReturn($this->db);
//
//                            $where->shouldReceive('where')
//                                ->once(1)
//                                ->with('messages.participant_id')
//                                ->andReturn($this->db);
//
//                            return $where;
//                        }));
//
//                    $x->shouldReceive('where')
//                        ->once()
//                        ->with('conversation_id',1)
//                        ->andReturn($this->db);
//
//                    return $x;
//
//                }),1 =>'messages.participant'])

            ->andReturn($this->conversationModel);

        $result = $convRepo->getMessagesById(1,1);

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    public function test_get_messages_by_conversation_id_without_filters()
    {
        $convRepo = m::mock('Fenos\Mex\Conversations\Repositories\ConversationRepository[conversationReadable]',[$this->conversationModel,$this->deletedConversationModel,$this->db]);

        $this->conversationModel->shouldReceive('find')
            ->with(1)
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('load')
            ->once()
            ->with(['participants.participant','messages', 'messages.participant'])
            ->andReturn($this->conversationModel);

        $result = $convRepo->getMessagesById(1,null);

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);

    }

    public function test_get_messages_on_Archived_Conversation()
    {
        $convRepo = m::mock('Fenos\Mex\Conversations\Repositories\ConversationRepository[conversationArchived]',[$this->conversationModel,$this->deletedConversationModel,$this->db]);

        $convRepo->shouldReceive('conversationArchived')
            ->with(1,1)
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('load')
            ->once()
            ->andReturn($this->conversationModel);

        $result = $convRepo->getMessagesOnArchivedConversation(1,1);

        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    public function test_get_lists_conversations()
    {
        $this->conversationModel->shouldReceive('with')
            ->once()
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('whereNotExists')
            ->once()
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('get')
            ->once()
            ->andReturn($this->conversationModel);


        $result = $this->convRepo->getLists(1);
        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    public function test_get_archived_lists_conversations()
    {
        $this->conversationModel->shouldReceive('with')
            ->once()
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('whereExists')
            ->once()
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('get')
            ->once()
            ->andReturn($this->conversationModel);


        $result = $this->convRepo->getArchivedLists(1);
        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    public function test_archive_a_conversation()
    {

        $this->conversationModel->shouldReceive('whereNotExists')
            ->once()
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('find')
            ->once()
            ->andReturn($this->conversationModel);


        $result = $this->convRepo->conversationActive(1,1);
        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    public function test_get_archived_conversation()
    {
        $this->conversationModel->shouldReceive('whereExists')
            ->once()
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('find')
            ->once()
            ->andReturn($this->conversationModel);


        $result = $this->convRepo->conversationArchived(1,1);
        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    public function test_get_readables_conversations()
    {
        $this->conversationModel->shouldReceive('whereNotExists')
            ->once()
            ->andReturn($this->conversationModel);

        $this->conversationModel->shouldReceive('find')
            ->once()
            ->andReturn($this->conversationModel);


        $result = $this->convRepo->conversationReadable(1,1);
        $this->assertInstanceOf('Fenos\Mex\Models\Conversation',$result);
    }

    public function test_to_restore_a_conversation()
    {
        $this->deletedConversationModel->shouldReceive('where')
            ->once()
            ->with('conversation_id',1)
            ->andReturn($this->deletedConversationModel);

        $this->deletedConversationModel->shouldReceive('where')
            ->once()
            ->with('participant_id',1)
            ->andReturn($this->deletedConversationModel);

        $this->deletedConversationModel->shouldReceive('first')
            ->once()
            ->andReturn($this->deletedConversationModel);

        $this->deletedConversationModel->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $result = $this->convRepo->restore(1,1);

        $this->assertTrue($result);
    }

    public function test_to_restore_a_conversation_but_it_is_not_found()
    {
        $this->deletedConversationModel->shouldReceive('where')
            ->once()
            ->with('conversation_id',1)
            ->andReturn($this->deletedConversationModel);

        $this->deletedConversationModel->shouldReceive('where')
            ->once()
            ->with('participant_id',1)
            ->andReturn($this->deletedConversationModel);

        $this->deletedConversationModel->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $result = $this->convRepo->restore(1,1);

        $this->assertFalse($result);
    }

    public function test_force_remove_a_conversation()
    {
        $this->deletedConversationModel->shouldReceive('findOrCreate')
            ->once()
            ->with(1,1)
            ->andReturn($this->deletedConversationModel->makePartial());

        $this->deletedConversationModel->conversation_id = 1;
        $this->deletedConversationModel->participant_id = 1;
        $this->deletedConversationModel->archived = 1;

        $this->deletedConversationModel->shouldReceive('save')
            ->once()
            ->with()
            ->andReturn($this->deletedConversationModel);

        $result = $this->convRepo->forceRemove(1,1);

        $this->assertInstanceOf('Fenos\Mex\Models\DeletedConversation',$result);
    }
}
 