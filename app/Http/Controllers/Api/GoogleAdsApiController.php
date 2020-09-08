<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Google\Ads\GoogleAds\Lib\V5\GoogleAdsClient;
use Google\Ads\GoogleAds\Util\V5\ResourceNames;
use Google\Ads\GoogleAds\V5\Enums\KeywordPlanNetworkEnum\KeywordPlanNetwork;
use Google\Ads\GoogleAds\V5\Services\GenerateKeywordIdeaResult;
use Google\Ads\GoogleAds\V5\Services\KeywordSeed;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Protobuf\StringValue;
use Illuminate\Http\Request;

class GoogleAdsApiController extends Controller
{
    public function searchVolumes(Request $request, GoogleAdsClient $googleAdsClient)
    {
        if ($request->has('keywords')) {
            $keyword = $request->input('keywords');

            $keywords = [$keyword];
            $locationIds = [21167];
            $languageId = 1000;
            $customerId = '6253937795';

//            $data = $this->ad_service->searchVolumes($keyword_ary);
            $keywordPlanIdeaServiceClient = $googleAdsClient->getKeywordPlanIdeaServiceClient();

            $requestOptionalArgs['keywordSeed'] = new KeywordSeed([
                'keywords' => array_map(function ($keyword) {
                    return new StringValue(['value' => $keyword]);
                }, $keywords)
            ]);

            $geoTargetConstants = array_map(function ($locationId) {
                return new StringValue(
                    ['value' => ResourceNames::forGeoTargetConstant($locationId)]
                );
            }, $locationIds);

            try {
                $response = $keywordPlanIdeaServiceClient->generateKeywordIdeas(
                // Set the language resource using the provided language ID.
                    new StringValue(['value' => ResourceNames::forLanguageConstant($languageId)]),
                    [
                        'customerId'         => $customerId,
                        // Add the resource name of each location ID to the request.
                        'geoTargetConstants' => $geoTargetConstants,
                        // Set the network. To restrict to only Google Search, change the parameter below to
                        // KeywordPlanNetwork::GOOGLE_SEARCH.
                        'keywordPlanNetwork' => KeywordPlanNetwork::GOOGLE_SEARCH_AND_PARTNERS
                    ] + $requestOptionalArgs
                );


                // Iterate over the results and print its detail.
                foreach ($response->iterateAllElements() as $result) {
                    /** @var GenerateKeywordIdeaResult $result */
                    // Note that the competition printed below is enum value.
                    // For example, a value of 2 will be returned when the competition is 'LOW'.
                    // A mapping of enum names to values can be found at KeywordPlanCompetitionLevel.php.
                    printf(
                        "Keyword idea text '%s' has %d average monthly searches and competition as %d.%s",
                        $result->getTextUnwrapped(),
                        is_null($result->getKeywordIdeaMetrics()) ?
                            0 : $result->getKeywordIdeaMetrics()->getAvgMonthlySearchesUnwrapped(),
                        is_null($result->getKeywordIdeaMetrics()) ?
                            0 : $result->getKeywordIdeaMetrics()->getCompetition(),
                        PHP_EOL
                    );
                }
            } catch (ApiException $e) {
                var_dump($e->getMessage());
            } catch (ValidationException $e) {
                var_dump($e->getMessage());
            }

            //21180

//            var_dump($keywords);
        }
    }
}
