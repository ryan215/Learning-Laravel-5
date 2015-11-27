<?php

use App\Resource;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder {

    /**
     * Creates several users - as of now, that is the only way the users are added to the system.
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 2) as $userId) {
            User::create([
                'id' => $userId,
                'username' => $userId == 1 ? 'tester' : $faker->userName(),
                'name' => $faker->name,
                'email' => $userId == 1 ? 'test@example.com' : $faker->email(),
                'password' => bcrypt(TEST_PASSWORD),
            ]);
        }

    }
}