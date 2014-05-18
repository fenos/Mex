<?php

use Mockery as m;

/**
 * Class MessageTest
 */
class MessageTest extends PHPUnit_Framework_TestCase {

    /**
     * @var $message
     */
    protected $message;
    /**
     * @var $messRepo
     */
    protected $messRepo;
    /**
     * @var $conversation
     */
    protected $conversation;

    /**
     * SetUp Unit test
     */
    public function setUp()
    {
        $this->message = new \Fenos\Mex\Messages\Message(
          "new message",
          $this->conversation = m::mock('Fenos\Mex\Conversations\Conversation')->makePartial(),
          $this->messRepo = m::mock('Fenos\Mex\Messages\Repositories\MessageRepository')
        );
    }

    /**
     * TearDown
     */
    public function tearDown()
    {
        m::close();
    }

    public function test_message_that_is_a_string()
    {
        $result = $this->message->message('is a string');

        $this->assertEquals($result,'is a string');
    }

    /**
     * @expectedException \InvalidArgumentException
     * */
    public function test_message_that_is_not_string()
    {
        $this->message->message(1234);
    }

    public function test_from_participant_is_a_number()
    {
        $result = $this->message->from(1);

        $this->assertInstanceOf('Fenos\Mex\Messages\Message',$result);

        $this->assertEquals($this->message->getFrom(),1);
    }

    public function test_send_message()
    {
        $mockMessage = m::mock('Fenos\Mex\Messages\Message[message]',['message',$this->conversation,$this->messRepo]);

        $mockMessage->setConversationId(1);
        $mockMessage->from(1);

        $mockMessage->shouldReceive('message')
            ->once()
            ->andReturn('new message');

        $info_message = [
            'text'              => 'new message',
            'participant_id'    => 1,
            'conversation_id'   => 1
        ];

        $this->messRepo->shouldReceive('add')
            ->once()
            ->with($info_message)
            ->andReturn(m::mock('Fenos\Mex\Models\Message'));

        $result = $mockMessage->send();

        $this->assertInstanceOf('Fenos\Mex\Models\Message',$result);
    }

    public function test_delete_message()
    {
        $this->message->from(1);
        $this->message->setConversationId(1);

        $this->messRepo->shouldReceive('delete')
            ->once()
            ->with(1,'new message',1)
            ->andReturn(m::mock('Fenos\Mex\Models\DeletedMessage'));

        $result = $this->message->delete();

        $this->assertInstanceOf('Fenos\Mex\Models\DeletedMessage',$result);
    }

    /**
     * @expectedException \BadMethodCallException
     * */
    public function test_delete_message_but_from_is_not_specified()
    {
        $this->message->setConversationId(1);

        $this->message->delete();
    }

    public function mockGetters()
    {
        $this->conversation->shouldReceive('getFrom')
            ->once()
            ->andReturn(1);

        $this->conversation->shouldReceive('conversationId')
            ->once()
            ->andReturn(1);
    }
}

