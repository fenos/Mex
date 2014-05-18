<?php

use Mockery as m;

/**
 * Class MessageRepositoryTest
 */
class MessageRepositoryTest extends PHPUnit_Framework_TestCase {

    /**
     * @var $deletedMessage
     */
    protected $deletedMessage;
    /**
     * @var $message
     */
    protected $message;
    /**
     * @var $messageRepo
     */
    protected $messageRepo;

    /**
     * SetUp Unit test
     */
    public function setUp()
    {
        $this->messageRepo = new \Fenos\Mex\Messages\Repositories\MessageRepository(
          $this->message = m::mock('Fenos\Mex\Models\Message'),
          $this->deletedMessage = m::mock('Fenos\Mex\Models\DeletedMessage')
        );
    }

    /**
     * TearDown
     */
    public function tearDown()
    {
        m::close();
    }

    public function test_add_message()
    {
        $info_message = [
            'text'              => "new Message",
            'participant_id'    => 1,
            'conversation_id'   => 1
        ];

        $this->message->shouldReceive('create')
            ->once()
            ->with($info_message)
            ->andReturn($this->message);

        $result = $this->messageRepo->add($info_message);

        $this->assertInstanceOf('Fenos\Mex\Models\Message',$result);
    }

    public function test_delete_message()
    {
        $modelDelete = m::mock('Fenos\Mex\Models\DeletedMessage')->makePartial();

        $this->deletedMessage->shouldReceive('findOrCreate')
            ->with(1,1,1)
            ->once()
            ->andReturn($modelDelete);

        $modelDelete->shouldReceive('save')
            ->once()
            ->andReturn($modelDelete);

        $result = $this->messageRepo->delete(1,1,1);

        $this->assertInstanceOf('Fenos\Mex\Models\DeletedMessage',$result);

    }

}
