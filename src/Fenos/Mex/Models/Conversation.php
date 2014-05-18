<?php

namespace Fenos\Mex\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Conversation
 * @package Fenos\Mex\Models
 */
class Conversation extends Model{

    /**
     * @var string
     */
    protected $table = "conversations";
    /**
     * @var array
     */
    protected $fillable = ['founder_id','founder_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function founder()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participants()
    {
        return $this->hasMany('Fenos\Mex\Models\ConversationJoined','conversation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany('Fenos\Mex\Models\Message','conversation_id');
    }

}
