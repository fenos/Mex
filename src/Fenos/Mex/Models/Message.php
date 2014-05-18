<?php

namespace Fenos\Mex\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Message
 * @package Fenos\Mex\Models
 */
class Message extends Model {

    /**
     * @var string
     */
    protected $table = 'messages';
    /**
     * @var array
     */
    protected $fillable = ['conversation_id','participant_id','participant_type','text'];

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
    public function conversation()
    {
        return $this->belongsTo('Fenos\Mex\Models\Conversation');
    }
}
