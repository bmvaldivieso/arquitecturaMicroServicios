<?php

use Laravel\Lumen\Testing\TestCase;

class ExampleTest extends TestCase
{
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
        $response = $this->get('/search?q=test');
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that the search suggestions endpoint works.
     *
     * @return void
     */
    public function test_search_suggestions_endpoint()
    {
        $response = $this->get('/search/suggestions?q=test');
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that the popular searches endpoint works.
     *
     * @return void
     */
    public function test_popular_searches_endpoint()
    {
        $response = $this->get('/search/popular');
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
