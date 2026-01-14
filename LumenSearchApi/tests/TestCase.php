<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
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
     * Simulate a GET request to the application.
     *
     * @param string $uri
     * @param array $headers
     * @return \Illuminate\Http\Response
     */
    public function get($uri, array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);
        $response = $this->call('GET', $uri, [], [], [], [], $server);
        return $response;
    }

    /**
     * Transform headers to server variables.
     *
     * @param array $headers
     * @return array
     */
    protected function transformHeadersToServerVars(array $headers)
    {
        $server = [];
        foreach ($headers as $name => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $value;
        }
        return $server;
    }

    /**
     * Call the given URI and method.
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string $content
     * @return \Illuminate\Http\Response
     */
    abstract public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null);
}
