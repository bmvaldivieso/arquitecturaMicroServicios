<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class SearchService
{
    use ConsumesExternalService;

    /**
     * The base uri to be used to consume the search service
     * @var string
     */
    public $baseUri;

    /**
     * The secret to be used to consume the search service
     * @var string
     */
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('services.search.base_uri');
        $this->secret = config('services.search.secret');
        
        // Validate configuration
        if (empty($this->baseUri)) {
            throw new \RuntimeException('SEARCH_SERVICE_BASE_URL is not configured in .env file');
        }
    }

    /**
     * Get general search results from the search service
     * @return string
     */
    public function obtainSearch($params = [])
    {
        return $this->performRequest('GET', '/search', $params);
    }

    /**
     * Get books search results from the search service
     * @return string
     */
    public function obtainBooksSearch($params = [])
    {
        return $this->performRequest('GET', '/search/books', $params);
    }

    /**
     * Get authors search results from the search service
     * @return string
     */
    public function obtainAuthorsSearch($params = [])
    {
        return $this->performRequest('GET', '/search/authors', $params);
    }

    /**
     * Get search suggestions from the search service
     * @return string
     */
    public function obtainSuggestions($params = [])
    {
        return $this->performRequest('GET', '/search/suggestions', $params);
    }

    /**
     * Get popular searches from the search service
     * @return string
     */
    public function obtainPopular()
    {
        return $this->performRequest('GET', '/search/popular');
    }
}
