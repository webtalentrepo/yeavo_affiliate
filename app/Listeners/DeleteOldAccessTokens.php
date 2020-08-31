<?php

namespace App\Listeners;

use Laravel\Passport\Client;
use Laravel\Passport\Events\AccessTokenCreated;

class DeleteOldAccessTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param AccessTokenCreated $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        $client = Client::find($event->clientId);
        if ($client) {
            $client->tokens()
                ->where('id', '<>', $event->tokenId)
                ->where('user_id', $event->userId)
                ->where('created_at', '<', now()->subDays(2))
                ->delete();
        }
    }
}
