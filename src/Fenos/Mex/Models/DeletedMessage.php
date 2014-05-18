<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 08/05/14
 * Time: 10:54
 */

namespace Fenos\Mex\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class DeletedMessage
 * @package Fenos\Mex\Models
 */
class DeletedMessage extends Model {

    /**
     * @var string
     */
    protected $table = "deleted_messages";
    /**
     * @var array
     */
    protected $fillable = ['conversation_id','participant_id','partecipant_type','message_id'];

    /**
     * @param $query
     * @param $conversation_id
     * @param $participant_id
     * @param $message_id
     * @return static
     */
    public function scopeFindOrCreate($query, $conversation_id, $participant_id,$message_id)
    {
        $obj = $query->where('conversation_id',$conversation_id)
                    ->where('participant_id',$participant_id)
                    ->where('message_id',$message_id)->first();
        return $obj ?: new static;
    }
}
