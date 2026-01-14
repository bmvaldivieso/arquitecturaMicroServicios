<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Services\BookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Review;

class ReviewController extends Controller
{
    use ApiResponser;

    /**
     * The service to consume the book service
     * @var BookService
     */
    public $bookService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * Return the list of reviews
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $reviews = Review::all();
        return $this->successResponse($reviews);
    }

    /**
     * Create one new review
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'comment' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'book_id' => 'required|integer'
        ]);

        // Verify that the book exists
        try {
            $book = $this->bookService->obtainBook($request->book_id);
        } catch (\Exception $e) {
            return $this->errorResponse('The specified book does not exist', Response::HTTP_NOT_FOUND);
        }

        $review = Review::create($request->all());
        return $this->successResponse($review, Response::HTTP_CREATED);
    }

    /**
     * Obtains and show one review
     * @return Illuminate\Http\Response
     */
    public function show($review)
    {
        $review = Review::findOrFail($review);
        return $this->successResponse($review);
    }

    /**
     * Update an existing review
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $review)
    {
        $this->validate($request, [
            'comment' => 'string',
            'rating' => 'integer|min:1|max:5',
            'book_id' => 'integer'
        ]);

        $review = Review::findOrFail($review);

        // If book_id is being updated, verify the new book exists
        if ($request->has('book_id') && $request->book_id != $review->book_id) {
            try {
                $book = $this->bookService->obtainBook($request->book_id);
            } catch (\Exception $e) {
                return $this->errorResponse('The specified book does not exist', Response::HTTP_NOT_FOUND);
            }
        }

        $review->fill($request->all());
        if ($review->isClean()) {
            return $this->errorResponse('At least one value must change', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $review->save();
        return $this->successResponse($review);
    }

    /**
     * Remove an existing review
     * @return Illuminate\Http\Response
     */
    public function destroy($review)
    {
        $review = Review::findOrFail($review);
        $review->delete();
        return $this->successResponse($review);
    }
}
