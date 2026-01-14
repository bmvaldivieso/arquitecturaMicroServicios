<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\SearchService;

class SearchController extends Controller
{
    use ApiResponser;

    /**
     * The service to consume the search service
     * @var SearchService
     */
    public $searchService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Retrieve and show general search results
     * @return Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $params = $request->all();
        
        // Validar parámetros básicos
        if (empty($params['q']) && empty($params['category'])) {
            return $this->errorResponse('Query or category parameter is required', Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse($this->searchService->obtainSearch($params));
    }

    /**
     * Retrieve and show books search results
     * @return Illuminate\Http\Response
     */
    public function searchBooks(Request $request)
    {
        $params = $request->all();
        
        return $this->successResponse($this->searchService->obtainBooksSearch($params));
    }

    /**
     * Retrieve and show authors search results
     * @return Illuminate\Http\Response
     */
    public function searchAuthors(Request $request)
    {
        $params = $request->all();
        
        // Validar parámetro q para búsqueda de autores
        if (empty($params['q'])) {
            return $this->errorResponse('Query parameter is required', Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse($this->searchService->obtainAuthorsSearch($params));
    }

    /**
     * Retrieve and show search suggestions
     * @return Illuminate\Http\Response
     */
    public function suggestions(Request $request)
    {
        $params = $request->all();
        
        return $this->successResponse($this->searchService->obtainSuggestions($params));
    }

    /**
     * Retrieve and show popular searches
     * @return Illuminate\Http\Response
     */
    public function popular()
    {
        return $this->successResponse($this->searchService->obtainPopular());
    }
}
