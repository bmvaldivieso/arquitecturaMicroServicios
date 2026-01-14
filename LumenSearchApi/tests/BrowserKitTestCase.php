<?php

namespace Tests;

use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;

abstract class BrowserKitTestCase extends TestCase
{
    use MakesHttpRequests;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
