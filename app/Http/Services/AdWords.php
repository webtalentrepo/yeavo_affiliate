<?php


namespace App\Http\Services;


use App\Http\Repositories\AdWordsRepository;
use Google\AdsApi\AdWords\v201809\o\AttributeType;
use Google\AdsApi\AdWords\v201809\o\RequestType;
use Google\AdsApi\AdWords\v201809\o\TargetingIdeaService;
use Google\AdsApi\Common\Util\MapEntries;
use Illuminate\Support\Collection;

class AdWords
{
    const CHUNK_SIZE = 5;
    /** @var bool */
    protected $withTargetedMonthlySearches = false;
    /** @var bool */
    protected $convertNullToZero = false;
    /** @var int|null */
    protected $language = null;
    /** @var int|null */
    protected $location = null;
    /** @var array|null */
    protected $include = null;
    /** @var array|null */
    protected $exclude = null;
    /**
     * @var TargetingIdeaService
     */
    private $service;

    /**
     * AdWords constructor.
     * @param TargetingIdeaService $targetingIdeaService
     */
    public function __construct(TargetingIdeaService $targetingIdeaService)
    {
        $this->service = new AdWordsRepository($targetingIdeaService);
    }

    /**
     * @param array $keywords
     *
     * @return Collection
     */
    public function searchVolumes(array $keywords)
    {
        $keywords = $this->prepareKeywords($keywords);


        $requestType = RequestType::STATS;

        $searchVolumes = new Collection();
        $chunks = array_chunk($keywords, self::CHUNK_SIZE);

        foreach ($chunks as $index => $keywordChunk) {
            $results = $this->service->performQuery($keywordChunk, $requestType, $this->language, $this->location, $this->withTargetedMonthlySearches);

            if ($results->getEntries() !== null) {
                foreach ($results->getEntries() as $targetingIdea) {
                    $keyword = $this->extractKeyword($targetingIdea);
                    $searchVolumes->push($keyword);
                }
            }
        }

        $missingKeywords = array_diff($keywords, $searchVolumes->pluck('keyword')->toArray());

        foreach ($missingKeywords as $missingKeyword) {
            $missingKeywordInstance = [
                'keyword'       => $missingKeyword,
                'search_volume' => $this->convertNullToZero ? 0 : null,
                'cpc'           => $this->convertNullToZero ? 0 : null,
                'competition'   => $this->convertNullToZero ? 0 : null,
            ];

            if ($this->withTargetedMonthlySearches) {
                $missingKeywordInstance->targeted_monthly_searches = $this->convertNullToZero ? collect() : null;
            }

            $searchVolumes->push($missingKeywordInstance);
        }

        return $searchVolumes;
    }

    /**
     * Private Functions.
     */
    private function prepareKeywords(array $keywords)
    {
        $keywords = array_map('trim', $keywords);
        $keywords = array_map('mb_strtolower', $keywords);
        $keywords = array_filter($keywords);
        $keywords = array_unique($keywords);
        $keywords = array_values($keywords);

        return $keywords;
    }

    /**
     * @param $targetingIdea
     * @return array
     */
    private function extractKeyword($targetingIdea)
    {
        $data = MapEntries::toAssociativeArray($targetingIdea->getData());
        $keyword = $data[AttributeType::KEYWORD_TEXT]->getValue();
        $search_volume =
            ($data[AttributeType::SEARCH_VOLUME]->getValue() !== null)
                ? $data[AttributeType::SEARCH_VOLUME]->getValue() : 0;

        $average_cpc =
            ($data[AttributeType::AVERAGE_CPC]->getValue() !== null)
                ? $data[AttributeType::AVERAGE_CPC]->getValue()->getMicroAmount() : 0;
        $competition =
            ($data[AttributeType::COMPETITION]->getValue() !== null)
                ? $data[AttributeType::COMPETITION]->getValue() : 0;

        $result = [
            'keyword'                   => $keyword,
            'search_volume'             => $search_volume,
            'cpc'                       => $average_cpc,
            'competition'               => $competition,
            'targeted_monthly_searches' => null,
        ];

        if ($this->withTargetedMonthlySearches) {
            $targeted_monthly_searches =
                ($data[AttributeType::TARGETED_MONTHLY_SEARCHES]->getValue() !== null)
                    ? $data[AttributeType::TARGETED_MONTHLY_SEARCHES]->getValue() : 0;
            $targetedMonthlySearches = collect($targeted_monthly_searches)
                ->transform(function ($item, $key) {
                    return [
                        'year'  => $item->getYear(),
                        'month' => $item->getMonth(),
                        'count' => $item->getCount(),
                    ];
                });

            $result->targeted_monthly_searches = $targetedMonthlySearches;
        }

        return $result;
    }

    public function keywordIdeas($keyword)
    {
        $keyword = $this->prepareKeywords([$keyword]);
        $requestType = RequestType::IDEAS;

        $keywordIdeas = new Collection();

        $results = $this->service->performQuery($keyword, $requestType, $this->language, $this->location, $this->withTargetedMonthlySearches, $this->include, $this->exclude);
        if ($results->getEntries() !== null) {
            foreach ($results->getEntries() as $targetingIdea) {
                $keyword = $this->extractKeyword($targetingIdea);
                $keywordIdeas->push($keyword);
            }
        }

        return $keywordIdeas;
    }

    /**
     * Include Targeted Monthly Searches.
     *
     * @return $this
     */
    public function withTargetedMonthlySearches()
    {
        $this->withTargetedMonthlySearches = true;

        return $this;
    }

    /**
     * Convert Null Values To Zero.
     *
     * @return $this
     */
    public function convertNullToZero()
    {
        $this->convertNullToZero = true;

        return $this;
    }

    /**
     * Add Language Search Parameter.
     *
     * @return $this
     */
    public function language($language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Add Location Search Parameter.
     *
     * @return $this
     */
    public function location($location = null)
    {
        $this->location = $location;

        return $this;
    }

    public function include(array $words)
    {
        $this->include = $this->prepareKeywords($words);

        return $this;
    }

    public function exclude(array $words)
    {
        $this->exclude = $this->prepareKeywords($words);

        return $this;
    }

    /**
     * @return TargetingIdeaService
     */
    public function getTargetingIdeaService(): TargetingIdeaService
    {
        return $this->service->getTargetingIdeaService();
    }
}
