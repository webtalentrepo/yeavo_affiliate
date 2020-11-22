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
//        $data = Product::where('network', 'shareasale.com')->get();
//        if ($data) {
//            foreach ($data as $row) {
//                $p_tAry = explode(' - ', $row['p_title']);
//                $pUrl = isset($p_tAry[count($p_tAry) - 1]) ? $p_tAry[count($p_tAry) - 1] : $p_tAry[0];
//                $pRow = Product::find($row->id);
//                $pRow->program_url = 'https://'. $pUrl;
//                $pRow->save();
//            }
//        }
//
//        return 0;
    }
}
