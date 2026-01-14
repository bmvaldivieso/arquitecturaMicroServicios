<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Services\BookService;
use App\Services\AuthorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchController extends Controller
{
    use ApiResponser;

    /**
     * The service to consume the book service
     * @var BookService
     */
    public $bookService;

    /**
     * The service to consume the author service
     * @var AuthorService
     */
    public $authorService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BookService $bookService, AuthorService $authorService)
    {
        $this->bookService = $bookService;
        $this->authorService = $authorService;
    }

    /**
     * General search across books and authors
     * @return Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $category = $request->get('category');
        $price_min = $request->get('price_min');
        $price_max = $request->get('price_max');
        $rating_min = $request->get('rating_min');
        $sort = $request->get('sort', 'relevance');
        $page = max(1, (int)$request->get('page', 1));
        $limit = min(50, max(1, (int)$request->get('limit', 10)));

        if (empty($query) && empty($category)) {
            return $this->errorResponse('Query or category parameter is required', Response::HTTP_BAD_REQUEST);
        }

        try {
            // Obtener todos los libros y autores
            $books = $this->bookService->obtainBooks();
            $authors = $this->authorService->obtainAuthors();

            // Filtrar libros
            $filteredBooks = $this->filterBooks($books, $query, $category, $price_min, $price_max, $rating_min);
            
            // Filtrar autores
            $filteredAuthors = $this->filterAuthors($authors, $query);

            // Ordenar resultados
            $sortedBooks = $this->sortResults($filteredBooks, $sort, $query);
            $sortedAuthors = $this->sortResults($filteredAuthors, $sort, $query);

            // Paginar
            $bookResults = $this->paginate($sortedBooks, $page, $limit);
            $authorResults = $this->paginate($sortedAuthors, $page, $limit);

            $results = [
                'books' => $bookResults,
                'authors' => $authorResults,
                'total_books' => count($filteredBooks),
                'total_authors' => count($filteredAuthors),
                'query' => $query,
                'filters' => [
                    'category' => $category,
                    'price_min' => $price_min,
                    'price_max' => $price_max,
                    'rating_min' => $rating_min,
                    'sort' => $sort
                ],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => max(1, ceil(max(count($filteredBooks), count($filteredAuthors)) / $limit))
                ]
            ];

            return $this->successResponse($results);

        } catch (\Exception $e) {
            return $this->errorResponse('Search service temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Search books specifically
     * @return Illuminate\Http\Response
     */
    public function searchBooks(Request $request)
    {
        $query = $request->get('q', '');
        $category = $request->get('category');
        $price_min = $request->get('price_min');
        $price_max = $request->get('price_max');
        $rating_min = $request->get('rating_min');
        $sort = $request->get('sort', 'relevance');
        $page = max(1, (int)$request->get('page', 1));
        $limit = min(50, max(1, (int)$request->get('limit', 10)));

        try {
            $books = $this->bookService->obtainBooks();
            $filteredBooks = $this->filterBooks($books, $query, $category, $price_min, $price_max, $rating_min);
            $sortedBooks = $this->sortResults($filteredBooks, $sort, $query);
            $bookResults = $this->paginate($sortedBooks, $page, $limit);

            return $this->successResponse([
                'books' => $bookResults,
                'total' => count($filteredBooks),
                'query' => $query,
                'filters' => [
                    'category' => $category,
                    'price_min' => $price_min,
                    'price_max' => $price_max,
                    'rating_min' => $rating_min,
                    'sort' => $sort
                ],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => max(1, ceil(count($filteredBooks) / $limit))
                ]
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Books search service temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Search authors specifically
     * @return Illuminate\Http\Response
     */
    public function searchAuthors(Request $request)
    {
        $query = $request->get('q', '');
        $sort = $request->get('sort', 'relevance');
        $page = max(1, (int)$request->get('page', 1));
        $limit = min(50, max(1, (int)$request->get('limit', 10)));

        if (empty($query)) {
            return $this->errorResponse('Query parameter is required', Response::HTTP_BAD_REQUEST);
        }

        try {
            $authors = $this->authorService->obtainAuthors();
            $filteredAuthors = $this->filterAuthors($authors, $query);
            $sortedAuthors = $this->sortResults($filteredAuthors, $sort, $query);
            $authorResults = $this->paginate($sortedAuthors, $page, $limit);

            return $this->successResponse([
                'authors' => $authorResults,
                'total' => count($filteredAuthors),
                'query' => $query,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => max(1, ceil(count($filteredAuthors) / $limit))
                ]
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Authors search service temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Get search suggestions
     * @return Illuminate\Http\Response
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        $limit = min(10, max(1, (int)$request->get('limit', 5)));

        if (strlen($query) < 2) {
            return $this->successResponse(['suggestions' => []]);
        }

        try {
            $books = $this->bookService->obtainBooks();
            $authors = $this->authorService->obtainAuthors();

            $suggestions = [];

            // Sugerencias de libros
            foreach ($books as $book) {
                if (stripos($book['title'], $query) !== false) {
                    $suggestions[] = [
                        'type' => 'book',
                        'id' => $book['id'],
                        'title' => $book['title'],
                        'highlight' => str_ireplace($query, "<strong>{$query}</strong>", $book['title'])
                    ];
                }
            }

            // Sugerencias de autores
            foreach ($authors as $author) {
                if (stripos($author['name'], $query) !== false) {
                    $suggestions[] = [
                        'type' => 'author',
                        'id' => $author['id'],
                        'title' => $author['name'],
                        'highlight' => str_ireplace($query, "<strong>{$query}</strong>", $author['name'])
                    ];
                }
            }

            // Limitar y ordenar por relevancia
            $suggestions = array_slice($suggestions, 0, $limit);

            return $this->successResponse(['suggestions' => $suggestions]);

        } catch (\Exception $e) {
            return $this->errorResponse('Suggestions service temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Get popular searches
     * @return Illuminate\Http\Response
     */
    public function popular()
    {
        // En una implementación real, esto vendría de una base de datos de búsquedas populares
        $popularSearches = [
            ['query' => 'Harry Potter', 'count' => 1250],
            ['query' => 'Stephen King', 'count' => 980],
            ['query' => 'Science Fiction', 'count' => 756],
            ['query' => 'Mystery', 'count' => 645],
            ['query' => 'Romance', 'count' => 532],
            ['query' => 'Fantasy', 'count' => 489],
            ['query' => 'History', 'count' => 421],
            ['query' => 'Biography', 'count' => 387],
            ['query' => 'Programming', 'count' => 298],
            ['query' => 'Psychology', 'count' => 234]
        ];

        return $this->successResponse(['popular_searches' => $popularSearches]);
    }

    /**
     * Filter books based on criteria
     */
    private function filterBooks($books, $query, $category, $price_min, $price_max, $rating_min)
    {
        return array_filter($books, function($book) use ($query, $category, $price_min, $price_max, $rating_min) {
            $match = true;

            // Búsqueda por texto
            if (!empty($query)) {
                $titleMatch = stripos($book['title'], $query) !== false;
                $authorMatch = isset($book['author']) && stripos($book['author'], $query) !== false;
                $descMatch = isset($book['description']) && stripos($book['description'], $query) !== false;
                $match = $titleMatch || $authorMatch || $descMatch;
            }

            // Filtro por categoría
            if ($match && !empty($category)) {
                $match = isset($book['category']) && stripos($book['category'], $category) !== false;
            }

            // Filtro por precio
            if ($match && $price_min !== null) {
                $match = isset($book['price']) && $book['price'] >= $price_min;
            }
            if ($match && $price_max !== null) {
                $match = isset($book['price']) && $book['price'] <= $price_max;
            }

            // Filtro por rating
            if ($match && $rating_min !== null) {
                $match = isset($book['rating']) && $book['rating'] >= $rating_min;
            }

            return $match;
        });
    }

    /**
     * Filter authors based on query
     */
    private function filterAuthors($authors, $query)
    {
        return array_filter($authors, function($author) use ($query) {
            if (empty($query)) return true;
            
            $nameMatch = stripos($author['name'], $query) !== false;
            $bioMatch = isset($author['bio']) && stripos($author['bio'], $query) !== false;
            
            return $nameMatch || $bioMatch;
        });
    }

    /**
     * Sort results based on criteria
     */
    private function sortResults($results, $sort, $query = '')
    {
        if (empty($results)) return $results;

        switch ($sort) {
            case 'title_asc':
                usort($results, function($a, $b) {
                    $titleA = $a['title'] ?? $a['name'] ?? '';
                    $titleB = $b['title'] ?? $b['name'] ?? '';
                    return strcasecmp($titleA, $titleB);
                });
                break;
            
            case 'title_desc':
                usort($results, function($a, $b) {
                    $titleA = $a['title'] ?? $a['name'] ?? '';
                    $titleB = $b['title'] ?? $b['name'] ?? '';
                    return strcasecmp($titleB, $titleA);
                });
                break;
            
            case 'price_asc':
                usort($results, function($a, $b) {
                    $priceA = $a['price'] ?? PHP_FLOAT_MAX;
                    $priceB = $b['price'] ?? PHP_FLOAT_MAX;
                    return $priceA <=> $priceB;
                });
                break;
            
            case 'price_desc':
                usort($results, function($a, $b) {
                    $priceA = $a['price'] ?? 0;
                    $priceB = $b['price'] ?? 0;
                    return $priceB <=> $priceA;
                });
                break;
            
            case 'rating_desc':
                usort($results, function($a, $b) {
                    $ratingA = $a['rating'] ?? 0;
                    $ratingB = $b['rating'] ?? 0;
                    return $ratingB <=> $ratingA;
                });
                break;
            
            case 'relevance':
            default:
                if (!empty($query)) {
                    usort($results, function($a, $b) use ($query) {
                        $titleA = $a['title'] ?? $a['name'] ?? '';
                        $titleB = $b['title'] ?? $b['name'] ?? '';
                        
                        $posA = stripos($titleA, $query);
                        $posB = stripos($titleB, $query);
                        
                        if ($posA === false && $posB === false) return 0;
                        if ($posA === false) return 1;
                        if ($posB === false) return -1;
                        
                        return $posA <=> $posB;
                    });
                }
                break;
        }

        return array_values($results);
    }

    /**
     * Paginate results
     */
    private function paginate($results, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        return array_slice($results, $offset, $limit);
    }
}
