<?php

use App\Lease;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class ResourceTest extends ApiTester
{
    /**
     * Test /resources
     * @test
     * @return void
     */
    public function gets_resource_index()
    {
        $response = $this->getJson('/resources');

        $data = get_object_vars($response->data);

        $this->assertResponseOk();
        $this->assertTrue(count($data) > 1);

    }

    /**
     * Test /resources/{searchString}
     * @test
     * @return void
     */
    public function gets_resource_search()
    {
        $response = $this->getJson('/resources/ubuntu');

        $data = get_object_vars($response->data);

        $this->assertResponseOk();
        $this->assertTrue(count($data['ubuntu']) >= 1);
    }

    /**
     * Test /resources/{searchString}/lease
     * @test
     * @return void
     */
    public function posts_resource_lease()
    {
        $response = $this->getJson('/resources/ubuntu/lease', 'POST', $this->user);

        $this->assertResponseStatus(201);
        $this->assertObjectHasAttributes($response->data, 'leased_at', 'links');
    }
}
