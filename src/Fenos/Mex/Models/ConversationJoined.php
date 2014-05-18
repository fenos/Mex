<?php


namespace Fenos\Mex\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class ConversationJoined
 * @package Fenos\Mex\Models
 */
class ConversationJoined extends Model {

    /**
     * @var string
     */
    protected $table = 'conversation_joined';
    /**
     * @var array
     */
    protected $fillable = ['conversation_id','participant_id','partecipant_type'];
    /**
     * @var bool
     */
    protected $softDelete = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function participant()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function covnersations()
    {
        return $this->belongsTo('Mex\Fenos\Models\Conversation','conversation_id');
    }

}
