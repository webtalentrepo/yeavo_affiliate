<?php

namespace App\Console\Commands;

use App\Http\Repositories\ShareSale;
use Illuminate\Console\Command;

class ShareASaleInsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shareasale:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ShareASale data insert';

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
        $shareSale = new ShareSale();

        $shareSale->dataInsertFromAPI('shareasale.com');

        return 0;
    }
}
