<?php
/**
 * Created by Fabrizio Fenoglio.
 */

namespace Fenos\Mex\Models;

trait MexRelations {

    public function conversations()
    {
        return $this->morphMany('Fenos\Mex\Models\ConversationJoined', 'participant');
    }

    public function messages()
    {
        return $this->morphMany('Fenos\Mex\Models\Messages', 'participant');
    }

} 