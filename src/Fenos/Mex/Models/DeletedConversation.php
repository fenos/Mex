<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 12/05/14
 * Time: 15:48
 */

namespace Fenos\Mex\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class DeletedConversation
 * @package Fenos\Mex\Models
 */
class DeletedConversation extends Model {

    /**
     * @var string
     */
    protected $table = "deleted_conversations";
    /**
     * @var array
     */
    protected $fillable = ['conversation_id','participant_id','partecipant_type','archived'];

    /**
     * Find or create scope
     *
     * @param $query
     * @param $conversation_id
     * @param $participant_id
     * @return static
     */
    public function scopeFindOrCreate($query, $conversation_id, $participant_id)
    {
        $obj = $query->where('conversation_id',$conversation_id)->where('participant_id',$participant_id)->first();
        return $obj ?: new static;
    }
} 