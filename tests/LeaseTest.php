<?php

use App\Lease;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LeaseTest extends ApiTester
{
    /**
     * Test /leases
     * @test
     */
    public function gets_active_leases()
    {
        $response = $this->getJson('/leases', 'GET', $this->user);

        $this->assertResponseStatus(200);

        if (count($response->data) < 2) {
            $this->debug($response->data, 'This was supposed to contain at least 2 leases');
        }

        // Based on database seeds, we should have more than 1 active leases
        $this->assertTrue(count($response->data) > 1);
    }

    /**
     * Test /leases/{leaseId}
     * @test
     */
    public function gets_specific_lease()
    {
        $lease = Lease::mine($this->user->id)->active()->get()->last();

        $response = $this->getJson('/leases/' . $lease->uuid, 'GET', $this->user);

        $this->assertResponseStatus(200);
        $this->assertObjectHasAttributes($response->data, 'local_ip', 'expires_at', 'links');
    }

    /**
     * Test /leases/{leaseId}/terminate
     * @test
     */
    public function terminates_specific_lease()
    {
        $lease = Lease::mine($this->user->id)->active()->get()->last();

        $response = $this->getJson('/leases/' . $lease->uuid . '/terminate', 'DELETE', $this->user);

        $this->assertResponseStatus(200);
        $this->assertObjectHasAttributes($response->data, 'terminated_at');
    }
}
