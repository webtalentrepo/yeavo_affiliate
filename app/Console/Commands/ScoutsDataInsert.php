<?php

namespace App\Console\Commands;

use App\Http\Repositories\CommissionJunction;
use App\Http\Repositories\ScoutRepository;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScoutsDataInsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scoutsdata:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Affiliate Scouts Data.';

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
        $links = ['clickbank.com', 'cj.com'];

        for ($i = 0; $i < count($links); $i++) {
            if ($links[$i] == 'cj.com') {
                $cj = new CommissionJunction();
                $re = $cj->getMerchants([]);

//                Log::info(json_encode($re));

                if ($re) {
                    $cj->saveDataToTable($re, $links[$i]);
                }
            } elseif ($links[$i] == 'clickbank.com') {
                $path = public_path('downloads');

                if (!file_exists($path)) {
                    mkdir($path);
                }

                chmod($path, 0777);
                if (file_exists(public_path('downloads/marketplace_feed_v2.xml.zip'))) {
                    unlink(public_path('downloads/marketplace_feed_v2.xml.zip'));
                }

                if (file_exists(public_path('downloads/marketplace_feed_v2.xml'))) {
                    unlink(public_path('downloads/marketplace_feed_v2.xml'));
                }

                if (file_exists(public_path('downloads/marketplace_feed_v2.dtd'))) {
                    unlink(public_path('downloads/marketplace_feed_v2.dtd'));
                }

                //https://accounts.clickbank.com/feeds/marketplace_feed_v2.xml.zip
                $content = file_get_contents('https://accounts.clickbank.com/feeds/marketplace_feed_v2.xml.zip');

                if (file_put_contents(public_path('downloads/marketplace_feed_v2.xml.zip'), $content)) {
                    $zip = new \ZipArchive();
                    $res = $zip->open(public_path('downloads/marketplace_feed_v2.xml.zip'));
                    if ($res === TRUE) {
                        $zip->extractTo($path);
                        $zip->close();

                        if (file_exists(public_path('downloads/marketplace_feed_v2.xml'))) {
                            $re = file_get_contents(public_path('downloads/marketplace_feed_v2.xml'));

                            $xml = \simplexml_load_string($re, null, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOCDATA);

                            $json = json_encode($xml);
                            $array = json_decode($json, true);

                            if ($array && isset($array['Category'])) {
                                $cb = new ScoutRepository();
                                $cb->setClickBankData($array['Category'], $links[$i]);

                                if (file_exists(public_path('downloads/marketplace_feed_v2.xml.zip'))) {
                                    unlink(public_path('downloads/marketplace_feed_v2.xml.zip'));
                                }

                                if (file_exists(public_path('downloads/marketplace_feed_v2.xml'))) {
                                    unlink(public_path('downloads/marketplace_feed_v2.xml'));
                                }

                                if (file_exists(public_path('downloads/marketplace_feed_v2.dtd'))) {
                                    unlink(public_path('downloads/marketplace_feed_v2.dtd'));
                                }
                            }
                        }
                    }
                }
            }
        }

        return 0;
    }
}
