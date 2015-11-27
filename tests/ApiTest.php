<?php

use App\Lease;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class ApiTest extends ApiTester
{
    /**
     * Calls method that does not exist.
     * @test
     * @return void
     */
    public function validates_response_structure()
    {
        $response = $this->getJson('/resources');

        $this->assertObjectHasAttributes($response, 'meta', 'data');
        $this->assertObjectHasAttributes($response->meta, 'http_code', 'duration', 'documentation');

        $this->assertResponseStatus(200);
    }


    /**
     * Calls method that does not exist.
     * @test
     * @return void
     */
    public function calls_missing_method()
    {
        $this->getJson('/doesnotexit');

        $this->assertResponseStatus(404);
    }

    /**
     * Calls method with HTTP method that is not allowed.
     * @test
     * @return void
     */
    public function calls_wrongHttpType_method()
    {
        $this->getJson('/resources', 'POST');

        $this->assertResponseStatus(501);
    }

    /**
     * Calls method that requires authenticated user.
     * @test
     * @return void
     */
    public function calls_unauthenticated_method()
    {
        $this->getJson('/leases');

        $this->assertResponseStatus(401);
    }
}
