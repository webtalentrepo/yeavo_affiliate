<?php

namespace App\Http\Repositories\Affiliates;

use Exception;

/**
 * Class Merchant
 * @package Padosoft\AffiliateNetwork
 */
class Merchant
{
    /**
     * @var int
     */
    public $merchant_ID = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $url = null;

    /**
     * @var string
     */
    public $brand_name = null;

    /**
     * @var string
     */
    public $status = null;

    /**
     * @var string
     */
    public $launch_date = null;

    /**
     * @var string
     */
    public $application_date = null;

    /**
     * @var string
     */
    public $termination_date = null;

    /**
     * @method createInstance
     * @return Merchant
     */
    public static function createInstance()
    {
        $obj = null;
        try {
            $obj = new Merchant();
        } catch (Exception $e) {
            throw new Exception('Error creating instance Merchant - ' . $e->getMessage());
        }

        return $obj;
    }

}
