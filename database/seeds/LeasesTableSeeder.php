<?php

use App\Lease;
use App\Resource;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class LeasesTableSeeder extends Seeder {

    /**
     * Creates several leases to simplify unit tests.
     */
    public function run()
    {
        $faker = Faker::create();

        $resources = Resource::limit(5)->get()->toArray();
        $users = User::limit(2)->get()->toArray();
        $durations = [10, 20, 60, 120, 240];
        foreach ($resources as $resource) {
            // Making sure that user #1 will have at least 2 leases
            $user = ($resource['id'] < 3) ? current($users) : $faker->randomElement($users);
            $leaseParams = Lease::stub($resource['id'], $user['id'], $faker->randomElement($durations));
            Lease::create($leaseParams);
        }
    }
}