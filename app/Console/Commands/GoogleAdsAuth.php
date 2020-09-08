<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

//use React\Http\Response;

class GoogleAdsAuth extends Command
{
    /**
     * @var string the OAuth2 scope for the Google Ads API
     * @see https://developers.google.com/google-ads/api/docs/oauth/internals#scope
     */
    private const SCOPE = 'https://www.googleapis.com/auth/adwords';
    /**
     * @var string the Google OAuth2 authorization URI for OAuth2 requests
     * @see https://developers.google.com/identity/protocols/OAuth2InstalledApp#step-2-send-a-request-to-googles-oauth-20-server
     */
    private const AUTHORIZATION_URI = 'https://accounts.google.com/o/oauth2/v2/auth';
    /**
     * @var string the OAuth2 call back URL path.
     */
    private const OAUTH2_CALLBACK_PATH = '/api/oauth';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googleAds:Auth';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Google ads api authorization.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }
}
