Mex
==========

[![Build Status](https://travis-ci.org/fenos/Mex.svg?branch=1.0.0)](https://travis-ci.org/fenos/Mex)
[![License](https://poser.pugx.org/fenos/mex/license.png)](https://packagist.org/packages/fenos/mex)
[![Latest Stable Version](https://poser.pugx.org/fenos/mex/v/stable.png)](https://packagist.org/packages/fenos/mex)

Mex is a simple but powerfull Api for build a internal multi participants chat system. This Api come with a lots feautres, and the synstax is pretty
straightforward, Now you have the tools for build your own multi partecipants chat with laravel 4. Enjoy it.

* [Installation](#installation)
* [Documentation](#documentation)
* [Conversations](#conversations)
    * [Conversation Exists](#conversation-exists)
    * [Create Conversations](#create-conversations)
    * [Archive & Restore Conversations](#archive--restore-conversations)
    * [Join & Leave Conversations](#join---leave-conversations)
    * [Lists Conversations](#lists-conversations)
    * [Force remove Conversations](#force-remove-conversations)
* [Messages](#messages)
    * [Send Messages](#send-messages)
    * [Get Messages Conversation](#get-messages-conversation)
    * [Delete Messages](#delete-messages)
* [Note](#note)
* [Credits](#credits)

## Installation ##

### Step 1 ###

Add it on your composer.json

~~~
"fenos/mex": "1.0.*"
~~~

and run **composer update**


### Step 2 ###

Add the following string to **app/config/app.php**

**Providers array:**

~~~
'Fenos\Mex\MexServiceProvider'
~~~

**Aliases array:**

~~~
'Notifynder'	=> 'Fenos\Mex\Facades\Mex'
~~~

### Step 3 ###

#### Migration ####

Make sure that your settings on **app/config/database.php** are correct, then make the migration typing:

~~~
php artisan migrate --package="fenos/mex"
~~~

### Step 4 ###

#### Include relations ###

Mex will be bring it to be polymorphic on the next releases so even if now is not, you have to place this 2 relations on your `User` Model

~~~

    public function conversations()
    {
        return $this->morphToMany('Fenos\Mex\Models\ConversationJoined', 'participant');
    }

    public function messages()
    {
        return $this->morphToMany('Fenos\Mex\Models\Messages', 'participant');
    }

~~~

That's it your have done.

## Documentation ##

As first approch to Mex you have to know that this package is based on conversations, so before any action, you have to retrive the current conversation
to work with. Said that we can start.

### Conversations ###

The code that you will use, in few words, always, for initialize a conversation will be:

~~~
Mex::conversation($conversation_id);
~~~

Or another one that you will use less but does the same thing "Initialize a conversation" but this time not passing the ID of the conversation
but the participants that belongs the conversation.

So let's say that you want to check if `user with ID 1` as a conversation with `user 3 and 4` you will use the method `participants` for initialize
the current conversation **If exists**

~~~
Mex::conversation()->participants(1,2,3,4);
~~~

#### Conversation Exists ####

If you wanna check if a conversation exists as said above you can do it in 2 different ways.

**Having the ID of the conversation**

~~~
try
{
    Mex::conversation($conversation_id)->exists();
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // do your staff
}
~~~

**Having only the participants ID** (This check you will use it on creating a conversation)

~~~
try
{
    Mex::conversation()->participants(1,2,3,4)->exists();
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // do your staff
}
~~~

On the `participants()` method you will pass the IDS of the participants "Users" (for now). But remember that you can
pass an even an **array** of them!

#### Create Conversations ####

For create conversations is really easy, Let me show the code is more easy to explain in this case. Then review it.

~~~
// this will create a multi conversation between users 1 - 2 and 3
Mex::conversation()->participants(1,2,3)->create(['founder_id' => 1]);

~~~

So what's going on here I'm telling to `Mex` that i'm working with a `conversation`, and I know, that I need to create it,
so I pass the participants that "will have fun to chat", and then I pass the founder id in an array easy hum?.

Keep in mind that the `founder_id` must be specified but you can even omit it on the participants values/array.

Also **very important** the create method doesn't check if the conversation has been already start between thoose participants, so before create it make sure you check that.

~~~

$participants = [1,2,3];
$founder = Auth::user()->id;
try
{
    Mex::conversation()->participants($participants)->exists();

    // here means that exists so you can just send a message in the conversation we will see it later
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
    Mex::conversation()->participants($participants)->create(['founder_id' => $founder]);
}

~~~

That's it!

#### Archive & Restore Conversations ####

##### Archive #####

Archive conversations doesn't mean delete it, but just store it as archived maybe a old conversation that you don't wanna have as active.
With Mex you can do it really easy and quick let me show you.

~~~
try
{
    Mex::conversation($conversation_id)->from($user_id)->archive(); // that's it, the user 1 has archived the conversation
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

Second approch

~~~
try
{
    Mex::conversation()->participants(1,2,3)->from($user_id)->archive(); // that's it, the user 1 has archived the conversation
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

How you saw here I used a new method called `from()` this method is really important for determinate who does the action.
Because do you know that if the `user with ID 1` archive the conversation that even `User ID 2` is in, **only** `User ID 1` will have it as archived,
while the user 2 still normal. So speicfy always the user with `from()` when you do any action that focus only 1 User.

##### Restore #####

For restore a conversation and have it again as "active" use the method `restore()` instead:

~~~
try
{
    Mex::conversation($conversation_id)->from($user_id)->restore(); // that's it, the user 1 has restored the conversation
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

You can use even partecipants method here.

#### Lists Conversations ####

Do you need to retrive a nice lists of the conversations that the current user has started or has been invited? Ok well use this one,
but keep in mind that the archived conversations will be not showed here.

What you aspect from this method? Lists of conversations / informations participants / last message sent

~~~
Mex::conversation()->from($user_id)->lists();
~~~

This method right now will get all conversations about that user, but if the user has 1000 conversations? Relax you have 3 filters for that
and i'm sure you know thoose.

~~~
// limit the result to 10 conversation and order it
Mex::conversation()->from($user_id)->lists( array('limit' => 10,'orderBy' => 'DESC') );

// paginate it
Mex::conversation()->from($user_id)->lists( array(['orderBy' => 'ASC', 'paginate' => 10) );
~~~

If instead you need to retrive the archived lists of conversations you use this method:

~~~
// you can have the parameters as above
Mex::conversation()->from($user_id)->archivedLists();
~~~

#### Join & Leave Conversations ####

The user has already a conversation started with others but he want to add someone else. You can give this feature to you chat using the `join()` method
see how:

~~~
try
{
    Mex::conversation($conversation_id)->join(4,5); // user 4 and 5 are joined now in the conversation

catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

Instead if you wanna leave the conversation use `leave()` instead

~~~
try
{
    Mex::conversation($conversation_id)->leave(4,5); // user 4 and 5 are leaved now in the conversation

catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

#### Force remove Conversations ####

Removing the conversations means that, the user will not see the conversation anymore **Only the user that does the action** others participants will still see
the conversation. And the conversation can be restored using the `restore` method just in case. Keep also in mind that the user will not leave the conversation if
he delete the conversation this is up to you. Let's see how force delete the conversation.

~~~
try
{
    Mex::conversation($id)->from($user_id)->forceRemove();

catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

## Messages ##

After you learned about conversations now is time to focus on the messages (Nothing more hard then conversations).
See how it easilly works.

### Send Messages ###

for send message is really easy let me show the code and then review it.

~~~
try
{
    Mex::conversation($conversation_id)->message('Text of your message')->from(1)->send();

catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

I told you, is almost self explained but I review it. We are getting the conversation and passing the id of it, in this conversation we know that we have to send the message.
Next, we use `message()` method with inside the text of the message. Next witch user send the message? i specific him with `from()` method. Now Mex know everything
for deliver the message on the right place and use `send()`.

Now I want even review Create a conversation and sending the message on the same time! That is the main action that an chat must to have.

~~~

try
{
    // check if the conversation exists
    $conversation = Mex::conversation()->participants(1,2,3)->exists();

    // yes exists the method above gave to me the ID of it and i send only the message
    Mex::conversation($conversation->conversation_id)->message('Conversation exists')->from(1)->send();
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // the conversation doesn't exists so I create a new
    $newConversation = Mex::conversation()->participants(1,2,3)->create(['founder_id'=> 1]);

    // and I send the message to the current conversation
    $newConversation->message('New conversation')->from(1)->send();
}
~~~

Cool isn't?

### Get Messages Conversation ###

Finally we have almost done, now is time to get the messages of a conversation. Here you have 2 ways to get messages of a conversation.

First of all how I mentioned on the documentation the method `from()` is very important for let Mex know wich user want to retreive the messages
so I'll use the following.

~~~
try
{
    Mex::conversation($conversation_id)->from(1)->get();
}
catch()
{
    // conversation not found
}
~~~

Using `from()` method **no external users can access to conversation if an user ID that not belongs to any of the currents IDS on the conversation**, it will throw
`ConversationNotFoundException`.

Also if the user has some massage deleted using `from()` will not show it obviously,they are deleted for this user, but **not for others in the conversation**.

The second method get the entirely conversation with all deleted messages as well, so is not useful for users but I document it.

~~~
try
{
    Mex::conversation($conversation_id)->get();
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

If you want to `limit` or `order` or `paginate` the messages pass the array on the `get()` method

~~~
Mex::conversation($conversation_id)->from(1)->get(['limit' => 10,'orderBy' => 'DESC']);
Mex::conversation($conversation_id)->from(1)->get(['orderBy' => 'DESC','paginate' => '10']); // paginate as last paramater of the array
~~~

Also Important, you can retrive the messages of conversations "active" and "Archived" but **Not conversation that has been ForceDeleted** it will throw `ConversationNotFoundException`

### Delete Messages ###

The good feature of deleting messages is that only the user that delete the message will not see it anymore but the others participants still see it.
Let's see how a user delete a message.

~~~
try
{
    // this delete the message with id 1 for the user with 1 on the conversation with id 1!! :)
    Mex::conversation($conversaton_id)->message($message_id)->from($user_id)->delete();

    // if you prefer to use from() method after the conversation() method do it is the same
    Mex::conversation($conversaton_id)->from($user_id)->message($message_id)->delete();
}
catch(\Fenos\Mex\Exceptions\ConversationNotFoundException $e)
{
    // conversation not found
}
~~~

### Note ###

How you can see this is the first realease and it seem to promises well. Soon if time permit `Mex` will have new features already designed.
Example it will come Polymorphic as well, how you can see on the migration files the tables are already setted for it :).
For the rest feautures will come out more as surprise. :)

I made it with <3

### Tests ###

For run the tests make sure to have phpUnit and Mockery installed

### Credits ###

© Copyright Fabrizio Fenoglio

Released package under MIT Licence.