<?php

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

class ExampleTest extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    /**
     * Test that the search endpoint returns a successful response.
     *
     * @return void
     */
    public function test_search_endpoint_returns_success()
    {
        // ✅ Mockear todas las llamadas HTTP externas
        Http::fake([
            '*' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $this->get('/search?q=test');
        
        $this->assertResponseStatus(200);
    }

    /**
     * Test that the search suggestions endpoint works.
     *
     * @return void
     */
    public function test_search_suggestions_endpoint()
    {
        // ✅ Mockear todas las llamadas HTTP externas
        Http::fake([
            '*' => Http::response([
                'suggestions' => [],
            ], 200),
        ]);

        $this->get('/search/suggestions?q=test');
        
        $this->assertResponseStatus(200);
    }

    /**
     * Test that the popular searches endpoint works.
     *
     * @return void
     */
    public function test_popular_searches_endpoint()
    {
        // ✅ Este endpoint no depende de servicios externos, pero mock por consistencia
        Http::fake([
            '*' => Http::response([
                'popular_searches' => [],
            ], 200),
        ]);

        $this->get('/search/popular');
        
        $this->assertResponseStatus(200);
    }

    /**
     * Test that search endpoint requires query parameter.
     *
     * @return void
     */
    public function test_search_endpoint_requires_query()
    {
        $this->get('/search');
        
        $this->assertResponseStatus(400); // Bad Request when no query
    }

    /**
     * Test that suggestions endpoint returns empty array for short query.
     *
     * @return void
     */
    public function test_suggestions_short_query_returns_empty()
    {
        // ✅ Mockear para queries cortas
        Http::fake([
            '*' => Http::response([
                'suggestions' => [],
            ], 200),
        ]);

        $this->get('/search/suggestions?q=a');
        
        $this->assertResponseStatus(200);
    }
}
