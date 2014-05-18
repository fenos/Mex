<?php namespace Fenos\Mex;

use Fenos\Mex\Conversations\Conversation;
use Fenos\Mex\Conversations\ConversationJoined;
use Fenos\Mex\Conversations\Repositories\ConversationJoinedRepository;
use Fenos\Mex\Conversations\Repositories\ConversationRepository;
use Fenos\Mex\Messages\Message;
use Fenos\Mex\Messages\Repositories\MessageRepository;
use Fenos\Mex\Models\Conversation as ModelConversation;
use Fenos\Mex\Models\ConversationJoined as ConversationJoinedModel;
use Fenos\Mex\Models\DeletedConversation;
use Fenos\Mex\Models\DeletedMessage;
use Fenos\Mex\Models\Message as MessageModel;
use Illuminate\Support\ServiceProvider;

class MexServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('fenos/mex');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mex();
        $this->conversation();
        $this->conversationJoined();
        $this->message();
        $this->repositories();
    }

    public function mex()
    {
        $this->app['mex'] = $this->app->share(function($app)
        {
           return new Mex();
        });
    }

    public function conversation()
    {
        $this->app->bind('mex.conversation', function($app)
        {
            return new Conversation(

                null,
                $app->make('mex.conversation.repository')
            );
        });
    }

    public function conversationJoined()
    {
        $this->app->bind('mex.conversationJoined', function($app){

            return new ConversationJoined(

                $app->make('mex.conversationJoined.repository'),
                $app->make('Carbon\Carbon')
            );

        });
    }

    public function message()
    {
        $this->app->bind('mex.message', function($app){
           return new Message(
               "",
               $app->make('mex.conversation'),
               $app->make('mex.message.repository')
           );
        });
    }

    public function repositories()
    {
        $this->app->bind('mex.conversationJoined.repository', function($app)
        {
           return new ConversationJoinedRepository(
               new ConversationJoinedModel(),
               $app['db']
           );
        });

        $this->app->bind('mex.conversation.repository', function($app){

            return new ConversationRepository(
                new ModelConversation,
                new DeletedConversation,
                $app['db']
            );

        });

        $this->app->bind('mex.message.repository', function($app){

            return new MessageRepository(
              new MessageModel(),
              new DeletedMessage()
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

}
