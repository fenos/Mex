<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 08/05/14
 * Time: 13:44
 */

namespace Fenos\Mex\Messages\Repositories;


use Fenos\Mex\Models\DeletedMessage;
use Fenos\Mex\Models\Message;

/**
 * Class MessageRepository
 * @package Fenos\Mex\Messages\Repositories
 */
class MessageRepository {


    /**
     * @var Message
     */
    private $message;
    /**
     * @var \Fenos\Mex\Models\DeletedMessage
     */
    private $deletedMessage;

    /**
     * @param Message $message
     * @param DeletedMessage $deletedMessage
     */
    function __construct(Message $message, DeletedMessage $deletedMessage)
    {
        $this->message = $message;
        $this->deletedMessage = $deletedMessage;
    }

    /**
     * @param array $message_info
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function add(array $message_info)
    {
        return $this->message->create($message_info);
    }

    /**
     * Delete message adding a row on the table
     * deleted_messages
     *
     * @param $conversation_id
     * @param $message_id
     * @param $participant_id
     * @return bool
     */
    public function delete($conversation_id,$message_id,$participant_id)
    {

        $messageDeleted = $this->deletedMessage->findOrCreate($conversation_id,$participant_id,$message_id);

        $messageDeleted->conversation_id = $conversation_id;
        $messageDeleted->participant_id = $participant_id;
        $messageDeleted->message_id = $message_id;

        if ($messageDeleted->save())
        {
            return $messageDeleted;
        }

        return false;
    }
}