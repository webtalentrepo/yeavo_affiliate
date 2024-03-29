<?php

/**
 * Api Methods Interface.
 *
 * A protocol specific class that implements this interface must implement all
 * methods defined below. This interface contains all methods supported by
 * the zanox Api.
 *
 * Supported Version: PHP >= 5.0
 *
 * @author      Thomas Nicolai (thomas.nicolai@sociomantic.com)
 * @author      Lars Kirchhoff (lars.kirchhoff@sociomantic.com)
 *
 * @see         http://wiki.zanox.com/en/Web_Services
 * @see         http://apps.zanox.com
 *
 * @package     ApiClient
 * @version     2011-03-01
 * @copyright   Copyright (c) 2007-2011 zanox.de AG
 */
interface IMethods
{

    /**
     * Get a single product.
     *
     * @param string $productId product id hash
     * @param int $adspaceId adspace id (optional)
     *
     * @access     public
     * @return     object or string            single product item or false
     * @category   nosignature
     *
     */
    public function getProduct($productId, $adspaceId = null);


    /**
     * Get product categories.
     *
     * @param int $rootCategory category id (optional)
     * @param bool $includeChilds include child nodes (optional)
     *
     * @access     public
     * @return     object or string            single product item or false
     * @category   nosignature
     *
     */
    public function getProductCategories($rootCategory = 0,
                                         $includeChilds = false);


    /**
     * Search for products.
     *
     * @param string $query search string
     * @param string $searchType search type (optional)
     *                                         (contextual or phrase)
     * @param string $region limit search to region (optional)
     * @param int $categoryId limit search to categorys (optional)
     * @param array $programId limit search to program list of
     *                                         programs (optional)
     * @param boolean $hasImages products with images (optional)
     * @param float $minPrice minimum price (optional)
     * @param float $maxPrice maximum price (optional)
     * @param int $adspaceId adspace id (optional)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            list of products or false
     * @category   nosignature
     *
     */
    public function searchProducts($query, $searchType = 'phrase',
                                   $region = null, $categoryId = null, $programId = [],
                                   $hasImages = true, $minPrice = 0, $maxPrice = null, $adspaceId = null,
                                   $page = 0, $items = 10);


    /**
     * Get single incentive.
     *
     * @param int $incentiveId incentive id (mandatory)
     * @param int $adspaceId adspace id (optional)
     *
     * @access      public
     * @return      object or string            incentive or false
     * @category    nosignature
     *
     */
    public function getIncentive($incentiveId, $adspaceId = null);


    /**
     * Get single exclusive incentive.
     *
     * @param int $incentiveId incentive id (mandatory)
     * @param int $adspaceId adspace id (optional)
     *
     * @access      public
     * @return      object or string            incentive or false
     * @category    signature
     *
     */
    public function getExclusiveIncentive($incentiveId, $adspaceId = null);


    /**
     * Search for incentives.
     *
     * @param int $programId limit search to program list of
     *                                          programs (optional)
     * @param int $adspaceId adspace id (optional)
     * @param string $incentiveType type of incentive (optional)
     *                                          (coupons, samples, bargains,
     *                                          freeProducts, noShippingCosts,
     *                                          lotteries)
     * @param string $region program region (optional)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access      public
     * @return      object or string            list of incentives or false
     * @category    nosignature
     *
     */
    public function searchIncentives($programId = null, $adspaceId = null,
                                     $incentiveType = null, $region = null, $page = 0, $items = 10);


    /**
     * Search for exclusive incentives.
     *
     * @param int $programId limit search to program list of
     *                                          programs (optional)
     * @param int $adspaceId adspace id (optional)
     * @param string $incentiveType type of incentive (optional)
     *                                          (coupons, samples, bargains,
     *                                          freeProducts, noShippingCosts,
     *                                          lotteries)
     * @param string $region program region (optional)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access      public
     * @return      object or string            list of incentives or false
     * @category    signature
     *
     */
    public function searchExclusiveIncentives($programId = null, $adspaceId = null,
                                              $incentiveType = null, $region = null, $page = 0, $items = 10);


    /**
     * Get a single admedium.
     *
     * @param int $admediumId advertising medium id
     * @param int $adspaceId advertising space id (optional)
     *
     * @access     public
     * @return     object or string            single product item or false
     * @category   nosignature
     *
     */
    public function getAdmedium($admediumId, $adspaceId = null);


    /**
     * Get admedium categories.
     *
     * @param int $programId program admedium categories
     *
     * @access     public
     * @return     object or string            list of admedium categories
     * @category   nosignature
     *
     */
    public function getAdmediumCategories($programId);


    /**
     * Retrieve all advertising media items.
     *
     * @param int $programId advertiser program id (optional)
     * @param string $region limit search to region (optional)
     * @param string $format admedia format (optional)
     * @param string $partnerShip partnership status (optional)
     *                                         (direct or indirect)
     * @param string $purpose purpose of admedia (optional)
     *                                         (startPage, productDeeplink,
     *                                         categoryDeeplink, searchDeeplink)
     * @param string $admediumType type of admedium (optional)
     *                                         (html, script, lookatMedia, image,
     *                                         imageText, text)
     * @param int $categoryId admedium category id (optional)
     * @param int $adspaceId adspace id (optional)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            admedia result set or false
     * @category   nosignature
     *
     */
    public function getAdmedia($programId = null, $region = null,
                               $format = null, $partnerShip = null, $purpose = null,
                               $admediumType = null, $categoryId = null, $adspaceId = null, $page = 0,
                               $items = 10);


    /**
     * Get single application.
     *
     * @param int $applicationId application id (mandatory)
     *
     * @access      public
     * @return      object or string                application item or false
     * @category    signature
     *
     */
    public function getApplication($applicationId);


    /**
     * Get applications.
     *
     * @param string $name name of the application (optional)
     * @param int $width width of application (optional)
     * @param int $height height of application (optional)
     * @param string $format format of application (optional)
     * @param string $role role of the application (optional)
     *                                              (developer, customer, tester)
     * @param string $applicationType type of application (optional)
     *                                              (widget, saas, software)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access      public
     * @return      object or string            application item or false
     * @category    signature
     *
     */
    public function getApplications($name = null, $width = null,
                                    $height = null, $format = null, $role = null, $applicationType = null,
                                    $page = 0, $items = 0);


    /**
     * Create an application
     *
     * @param string $name name (optional)
     * @param string $version version (optional)
     * @param int $adrank adrank (optional)
     * @param string $tags tags (optional)
     * @param int $status status (optional)
     * @param boolean $mediaSlotCompatible compatible to media slot (optional)
     * @param boolean $inline
     * @param string $integrationCode integration code (optional)
     * @param string $integrationNotes integration notes (optional)
     * @param string $description description (optional)
     * @param string $terms terms of service (optional)
     * @param string $connectRole role of the application (optional)
     *                                                  (developer, customer, tester)
     * @param string $connectId connect id (optional)
     * @param string $connectStatus connect status (optional)
     *                                                  (active, inactive)
     * @param string $connectUrl connect url (optional)
     * @param string $cancelUrl cancel url (optional)
     * @param string $documentationUrl documentation url (optional)
     * @param string $companyUrl company url (optional)
     * @param string $developer developer (optional)
     * @param float $pricingShare price for share model (optional)
     * @param float $pricingSetup price for setup (optional)
     * @param float $pricingMonthly price for monthly usage (optional)
     * @param string $pricingCurrency pricing currency (optional)
     * @param string $pricingDescription pricing description (optional)
     * @param string $startDate start date (optional)
     * @param string $modifiedDate modification date (optional)
     * @param string $installableTo who can install the (optional)
     *                                                  application
     *                                                  (advertiser, publisher)
     * @param string $applicationType type of application (optional)
     *                                                  (widget, saas, software)
     * @param int $width width of application (optional)
     * @param int $height height of application (optional)
     * @param string $format format of application (optional)
     * @param string $technique technique (optional)
     * @param string $logoUrl logo url (optional)
     * @param string $previewUrl preview url (optional)
     *
     * @access      public
     * @return      object or string            application item or false
     * @category    signature
     *
     */
    public function createApplication($name = null, $version = null, $adrank = 0,
                                      $tags = null, $status = 0, $mediaSlotCompatible = false, $inline = false,
                                      $integrationCode = null, $integrationNotes = null, $description = null,
                                      $terms = null, $connectRole = null, $connectId = null,
                                      $connectStatus = null, $connectUrl = null, $cancelUrl = null,
                                      $documentationUrl = null, $companyUrl = null, $developer = null,
                                      $pricingShare = 0, $pricingSetup = 0, $pricingMonthly = 0,
                                      $pricingCurrency = null, $pricingDescription = null, $startDate = null,
                                      $modifiedDate = null, $installableTo = null, $applicationType = null,
                                      $width = null, $height = null, $format = null, $technique = null,
                                      $logoUrl = null, $previewUrl = null);


    /**
     * Update an application
     *
     * @param int $applicationId application id (mandatory)
     * @param string $name name (optional)
     * @param string $version version (optional)
     * @param int $adrank adrank (optional)
     * @param string $tags tags (optional)
     * @param int $status status (optional)
     * @param boolean $mediaSlotCompatible compatible to media slot (optional)
     * @param boolean $inline
     * @param string $integrationCode integration code (optional)
     * @param string $integrationNotes integration notes (optional)
     * @param string $description description (optional)
     * @param string $terms terms of service (optional)
     * @param string $connectRole role of the application (optional)
     *                                                  (developer, customer, tester)
     * @param string $connectId connect id (optional)
     * @param string $connectStatus connect status (optional)
     *                                                  (active, inactive)
     * @param string $connectUrl connect url (optional)
     * @param string $cancelUrl cancel url (optional)
     * @param string $documentationUrl documentation url (optional)
     * @param string $companyUrl company url (optional)
     * @param string $developer developer (optional)
     * @param float $pricingShare price for share model (optional)
     * @param float $pricingSetup price for setup (optional)
     * @param float $pricingMonthly price for monthly usage (optional)
     * @param string $pricingCurrency pricing currency (optional)
     * @param string $pricingDescription pricing description (optional)
     * @param string $startDate start date (optional)
     * @param string $modifiedDate modification date (optional)
     * @param string $installableTo who can install the (optional)
     *                                                  application
     *                                                  (advertiser, publisher)
     * @param string $applicationType type of application (optional)
     *                                                  (widget, saas, software)
     * @param int $width width of application (optional)
     * @param int $height height of application (optional)
     * @param string $format format of application (optional)
     * @param string $technique technique (optional)
     * @param string $logoUrl logo url (optional)
     * @param string $previewUrl preview url (optional)
     *
     * @access      public
     * @return      object or string            application item or false
     * @category    signature
     *
     */
    public function updateApplication($applicationId, $name = null,
                                      $version = null, $adrank = 0, $tags = null, $status = 0,
                                      $mediaSlotCompatible = false, $inline = false, $integrationCode = null,
                                      $integrationNotes = null, $description = null, $terms = null,
                                      $connectRole = null, $connectId = null, $connectStatus = null,
                                      $connectUrl = null, $cancelUrl = null, $documentationUrl = null,
                                      $companyUrl = null, $developer = null, $pricingShare = 0,
                                      $pricingSetup = 0, $pricingMonthly = 0, $pricingCurrency = null,
                                      $pricingDescription = null, $startDate = null, $modifiedDate = null,
                                      $installableTo = null, $applicationType = null, $width = null,
                                      $height = null, $format = null, $technique = null, $logoUrl = null,
                                      $previewUrl = null);


    /**
     * Delete application.
     *
     * @param int $applicationId application id (mandatory)
     *
     * @access      public
     * @return      boolean                true on success
     * @category    signature
     *
     */
    public function deleteApplication($applicationId);


    /**
     * Get single setting.
     *
     * @param int $applicationId application id (mandatory)
     * @param int $mediaslotId media slot id (optional)
     * @param key $key application specific key
     *
     * @access      public
     * @return      object or string            application item or false
     * @category    signature
     *
     */
    public function getSetting($applicationId, $mediaslotId = null, $key);


    /**
     * Get settings.
     *
     * @param int $applicationId application id (mandatory)
     * @param int $mediaslotId media slot id (optional)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access      public
     * @return      object or string                list of settings or false
     * @category    signature
     *
     */
    public function getSettings($applicationId, $mediaslotId = null,
                                $page = 0, $items = 0);


    /**
     * Create setting
     *
     * @param int $applicationId application id (mandatory)
     * @param int $mediaslotId media slot id (optional)
     * @param string $key settings key (mandatory)
     * @param string $value settings value (optional)
     * @param string $customValue settings custom value (optional)
     * @param string $type settings type (optional)
     *                                              (boolean, color, number,
     *                                               string, date)
     * @param string $name settings name (optional)
     * @param string $description settings description (optional)
     *
     * @access      public
     * @return      object or string                setting
     * @category    signature
     *
     */
    public function createSetting($applicationId, $mediaslotId = null, $key,
                                  $value, $customValue, $type = null, $name = null, $description = null);


    /**
     * Update setting
     *
     * @param int $applicationId application id (mandatory)
     * @param int $mediaslotId media slot id (optional)
     * @param string $key settings key (mandatory)
     * @param string $value settings value (optional)
     * @param string $customValue settings custom value (optional)
     * @param string $type settings type (optional)
     *                                              (boolean, color, number,
     *                                               string, date)
     * @param string $name settings name (optional)
     * @param string $description settings description (optional)
     *
     * @access      public
     * @return      object or string                setting
     * @category    signature
     *
     */
    public function updateSetting($applicationId, $mediaslotId = null, $key,
                                  $value, $customValue, $type = null, $name = null, $description = null);


    /**
     * Delete setting
     *
     * @param int $applicationId application id (mandatory)
     * @param string $mediaslotId mediaslot id (optional)
     * @param string $key settings key (mandatory)
     *
     * @access      public
     * @return      object or string                setting
     * @category    signature
     *
     */
    public function deleteSetting($applicationId, $mediaslotId, $key);


    /**
     * Get media slots.
     *
     * @param int $mediaslotId media slot id (optional)
     *
     * @access      public
     * @return      object or string                    media slot object or false
     * @category    signature
     *
     */
    public function getMediaSlot($mediaSlotId);


    /**
     * Get media slots.
     *
     * @param int $adspaceId advertising space id (optional)
     * @param int $width width of application (optional)
     * @param int $height height of application (optional)
     * @param string $format format of application (optional)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access      public
     * @return      object or string                    list of media slot objects or false
     * @category    signature
     *
     */
    public function getMediaSlots($adspaceId, $width = 0, $height = 0,
                                  $format = null, $page = 0, $items = 0);


    /**
     * Create media slot.
     *
     * @param string $name media slot name (mandatory)
     * @param int $adspaceId adspace id (mandatory)
     * @param string $applicationId application id (mandatory)
     * @param string $status media slot status (mandatory)
     *                                                  (active, deleted)
     * @param int $width width of application (optional)
     * @param int $height height of application (optional)
     * @param string $format format of application (optional)
     * @param string $createDate create date (optional)
     * @param string $modifiedDate modified date (optional)
     *
     * @access      public
     * @return      object or string                    list of media slot objects or false
     * @category    signature
     *
     */
    public function createMediaSlot($name, $adspaceId, $applicationId, $status,
                                    $width = 0, $height = 0, $format = null, $createDate = null,
                                    $modifiedDate = null);


    /**
     * Update media slot.
     *
     * @param int $mediaslotId media slot id (mandatory)
     * @param string $name media slot name (mandatory)
     * @param int $adspaceId adspace id (mandatory)
     * @param string $adspaceName name of the adspace (optional)
     * @param string $applicationId application id (mandatory)
     * @param string $applicationName name of the application (optional)
     * @param string $status media slot status (mandatory)
     *                                                  (active, deleted)
     * @param int $width width of application (optional)
     * @param int $height height of application (optional)
     * @param string $format format of application (optional)
     * @param string $createDate create date (optional)
     * @param string $modifiedDate modified date (optional)
     *
     * @access      public
     * @return      object or string                    list of media slot objects or false
     * @category    signature
     *
     */
    public function updateMediaSlot($mediaslotId, $name, $adspaceId,
                                    $adspaceName = null, $applicationId, $applicationName = null,
                                    $status = null, $width = 0, $height = 0, $format = null,
                                    $createDate = null, $modifiedDate = null);


    /**
     * Delete media slot.
     *
     * @param int $mediaslotId media slot id (mandatory)
     *
     * @access      public
     * @return      object or string                    true if success
     * @category    signature
     *
     */
    public function deleteMediaSlot($mediaslotId);


    /**
     * Retrieve a single zanox advertiser program item.
     *
     * @param int $programId id of program to retrieve
     *
     * @access     public
     * @return     object or string            program item or false
     * @category   nosignature
     *
     */
    public function getProgram($programId);


    /**
     * Get advertiser program categories.
     *
     * @access     public
     * @return     object or string            category result set or false
     * @category   nosignature
     *
     */
    public function getProgramCategories();


    /**
     * Search zanox advertiser programs.
     *
     * @param string $query search string
     * @param string $startDate program start date (optional)
     * @param string $partnerShip partnership status (optional)
     *                                         (direct or indirect)
     * @param boolean $hasProducts program has product data
     * @param string $region program region
     * @param string $categoryId program category id
     * @param int $page page of result set
     * @param int $items items per page
     *
     * @access     public
     * @return     object or string            programs result set or false
     * @category   nosignature
     *
     */
    public function searchPrograms($query = null, $startDate = null,
                                   $partnerShip = null, $hasProducts = false, $region = null,
                                   $categoryId = null, $page = 0, $items = 10);


    /**
     * Get advertiser program applications for a user.
     *
     * @param int $programId restrict results to applications (optional)
     *                                          to the id of this program (optional)
     * @param int $adspaceId advertising space id (optional)
     * @param string $status restrict results to program applications
     *                                          with this status:
     *                                          "open", "confirmed", "rejected",
     *                                          "deferred", "waiting", "blocked",
     *                                          "terminated", "canceled", "called",
     *                                          "declined", "deleted"
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            program result set or false
     * @category   signature
     *
     */
    public function getProgramApplications($adspaceId = null,
                                           $programId = null, $status = null, $page = 0, $items = 10);


    /**
     * Create program application for a given adspace.
     *
     * @param int $programId advertiser program id
     * @param int $adspaceId advertising space id
     *
     * @access     public
     * @return     boolean                    true or false
     * @category   signature
     *
     */
    public function createProgramApplication($programId, $adspaceId);


    /**
     * Delete program application.
     *
     * @param int $programId advertiser program id
     * @param int $adspaceId advertising space id
     *
     * @access     public
     * @return     boolean                     true or false
     * @category   signature
     *
     */
    public function deleteProgramApplication($programId, $adspaceId);


    /**
     * Get single sale item.
     *
     * @param int $leadId lead id
     *
     * @access     public
     * @return     object or string            sales result set or false
     * @category   signature
     *
     */
    public function getLead($leadId);


    /**
     * Get single sale item.
     *
     * @param int $saleId sale id
     *
     * @access     public
     * @return     object or string            sales result set or false
     * @category   signature
     *
     */
    public function getSale($saleId);


    /**
     * Get sales report.
     *
     * @param string $date date of sales
     * @param string $dateType type of date to filter by (optional)
     *                                         (clickDate, trackingDate,
     *                                         modifiedDate)
     * @param int $programId filter by program id (optional)
     * @param int $adspaceId filter by adspace id (optional)
     * @param array $reviewState filter by review status (optional)
     *                                         (confirmed, open, rejected or
     *                                         approved)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            sales result set or false
     * @category   signature
     *
     */
    public function getSales($date, $dateType = null, $programId = null,
                             $adspaceId = null, $reviewState = null, $page = 0, $items = 10);


    /**
     * Get leads report.
     *
     * @param string $date date of sales
     * @param string $dateType type of date to filter by (optional)
     *                                         (clickDate, trackingDate,
     *                                         modifiedDate)
     * @param int $programId filter by program id (optional)
     * @param int $adspaceId filter by adspace id (optional)
     * @param array $reviewState filter by review status (optional)
     *                                         (confirmed, open, rejected or
     *                                         approved)
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            sales result set or false
     * @category   signature
     *
     */
    public function getLeads($date, $dateType = null, $programId = null,
                             $adspaceId = null, $reviewState = null, $page = 0, $items = 10);


    /**
     * Get basic sales/leads report.
     *
     * @param string $fromDate report start date
     * @param string $toDate report end date
     * @param string $dateType type of date to filter by (optional)
     *                                         (clickDate, trackingDate,
     *                                         modifiedDate)
     * @param string $currency currency (optional)
     * @param int $programId program id (optional)
     * @param int $admediumId admedium id (optional)
     * @param int $admediumFormat admedium format id (optional)
     * @param int $adspaceId adspace id (optional)
     * @param string $reviewState filter by review status (optional)
     *                                         (confirmed, open, rejected or
     *                                         approved)
     * @param string $groupBy group report by option (optional)
     *                                         (country, region, city, currency,
     *                                         admedium, program, adspace,
     *                                         linkFormat, reviewState,
     *                                         trackingCategory, month, day,
     *                                         hour, year, dayOfWeek)
     *
     * @access     public
     * @return     object or string            payment item or false
     * @category   signature
     *
     */
    public function getReportBasic($fromDate, $toDate, $dateType = null,
                                   $currency = null, $programId = null, $admediumId = null,
                                   $admediumFormat = null, $adspaceId = null, $reviewState = null,
                                   $groupBy = null);


    /**
     * Get payment transactions of the current zanox account.
     *
     * @param int $page page of result set (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            payments result set or false
     * @category   signature
     *
     */
    public function getPayments($page = 0, $items = 10);


    /**
     * Get a single payment item.
     *
     * @param int $paymentId payment item id
     *
     * @access     public
     * @return     object or string            payment item or false
     * @category   signature
     *
     */
    public function getPayment($paymentId);


    /**
     * Get account balance
     *
     * @param int $currency currence code of balance account
     *
     * @access     public
     * @return     object or string            payment item or false
     * @category   signature
     *
     */
    public function getBalance($currency);


    /**
     * Get currency account balances.
     *
     * @param int $page result set page (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            account balances result set or
     *                                         false
     * @category   signature
     *
     */
    public function getBalances($page = 0, $items = 10);


    /**
     * Get back accounts.
     *
     * @param int $page result set page (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            account balances result set or
     *                                         false
     * @category   signature
     *
     */
    public function getBankAccounts($page = 0, $items = 10);


    /**
     * Get single back account.
     *
     * @param int $bankAccountId result set page
     *
     * @access     public
     * @return     object or string            account balances result set or
     *                                         false
     * @category   signature
     *
     */
    public function getBankAccount($bankAccountId);


    /**
     * Returns a single advertising spaces.
     *
     * @param int $adspaceId advertising space id
     *
     * @access     public
     * @return     object or string            adspace item or false
     * @category   signature
     *
     */
    public function getAdspace($adspaceId);


    /**
     * Returns all advertising spaces.
     *
     * @param int $page result set page (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            adspaces result set or false
     * @category   signature
     *
     */
    public function getAdspaces($page = 0, $items = 10);


    /**
     * Create advertising space (signature).
     *
     * @param string $name adspace name
     * @param string $lang language of adspace (e.g. en)
     * @param string $url url of adspace
     * @param string $contact contact address (email)
     * @param string $description description of adspace
     * @param string $adspaceType adspace typ (website, email or searchengine)
     * @param array $scope adspace scope (private or business)
     * @param int $visitors adspace monthly visitors
     * @param int $impressions adspace monthly page impressions
     * @param string $keywords keywords for adspace (optional)
     * @param array $regions adspace customer regions (optional)
     * @param array $categories adspace categories (optional)
     * @param int $checkNumber
     *
     * @access     public
     * @return     object or string            adspace item or false
     * @category   signature
     *
     */
    public function createAdspace($name, $lang, $url, $contact, $description,
                                  $adspaceType, $scope, $visitors, $impressions, $keywords = null,
                                  $regions = [], $categories = [], $checkNumber);


    /**
     * Update advertising space.
     *
     * @param int $adspaceId adspace id
     * @param string $name adspace name
     * @param string $lang language of adspace (e.g. en)
     * @param string $url url of adspace
     * @param string $contact contact address (email)
     * @param string $description description of adspace
     * @param string $adspaceType adspace typ (website, email or searchengine)
     * @param array $scope adspace scope (private or business)
     * @param int $visitors adspace monthly visitors
     * @param int $impressions adspace monthly page impressions
     * @param string $keywords keywords for adspace (optional)
     * @param array $regions adspace customer regions (optional)
     * @param array $categories adspace categories (optional)
     * @param int $checkNumber
     *
     * @access     public
     * @return     object or string            adspace item or false
     * @category   signature
     *
     */
    public function updateAdspace($adspaceId, $name, $lang, $url, $contact,
                                  $description, $adspaceType, $scope, $visitors, $impressions,
                                  $keywords = null, $regions = [], $categories = [], $checkNumber);


    /**
     * Removes advertising space.
     *
     * @param int $adspaceId advertising space id
     *
     * @access     public
     * @return     boolean                     true on success
     * @category   signature
     *
     */
    public function deleteAdspace($adspaceId);


    /**
     * Return zanox user profile.
     *
     * @access     public
     * @return     object or string            profile item
     * @category   signature
     *
     */
    public function getProfile();


    /**
     * Update zanox user profile.
     *
     * @param array $profileId user profile id
     * @param string $loginName login name
     * @param string $userName user name
     * @param string $firstName first name
     * @param string $lastName last name
     * @param string $email email address
     * @param string $country country or residence
     * @param string $street1 street 1
     * @param string $street2 street 2 (optional)
     * @param string $city city
     * @param string $company name of company (optional)
     * @param string $phone phone number (optional)
     * @param string $mobile mobile number (optional)
     * @param string $fax fax number (optional)
     * @param boolean $isAdvertiser is Advertiser account
     * @param boolean $isSublogin is Sublogin account
     *
     * @access     public
     * @return     boolean                     true on success
     * @category   signature
     *
     */
    public function updateProfile($profileId, $loginName, $userName,
                                  $firstName = null, $lastName = null, $email = null, $country = null,
                                  $street1 = null, $street2 = null, $city = null, $zipcode = null,
                                  $company = null, $phone = null, $mobile = null, $fax = null,
                                  $isAdvertiser, $isSublogin);


    /**
     * Returns new OAuth user session
     *
     * @param string $authToken authentication token
     *
     * @access     public
     * @return     object                      user session
     * @category   signature
     *
     */
    public function getSession($authToken);


    /**
     * Closes OAuth user session
     *
     * @param string $connectId connect ID
     *
     * @access     public
     * @return     bool                        returns true on success
     * @category   signature
     *
     */
    public function closeSession($connectId);


    /**
     * Get zanox User Interface Url
     *
     * @param string $connectId connect ID
     * @param string $sessionKey session key
     *
     * @access     public
     * @return     bool                        returns true on success
     * @category   signature
     *
     */
    public function getUiUrl($connectId, $sessionKey);


    /**
     * Get tracking categories for ad space; if not program member, returns program's default categories
     *
     * @param int $adspaceId adspace id (mandatory)
     * @param int $programId advertiser program id (mandatory)
     * @param int $page result set page (optional)
     * @param int $items items per page (optional)
     *
     * @access     public
     * @return     object or string            program result set of trackingCategoryItems
     * @category   signature
     *
     */
    public function getTrackingCategories($adspaceId, $programId, $page = 0, $items = 50);


}

?>
