<?php

use App\Resource;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class ResourcesTableSeeder extends Seeder {

    protected $distros = [
        'ubuntu' => ['15.10', '15.04', '14.04', '12.04'],
        'freebsd' => ['10.2', '10.1'],
        'fedora' => ['22', '21'],
        'debian' => ['8.1', '7.0', '6.0'],
        'coreos' => ['845', '835', '766'],
        'centos' => ['7.1', '6.7', '5.10'],
    ];

    /**
     * Creates enough resources to ensure that all distros are available as resources.
     */
    public function run()
    {

        $faker = Faker::create();

        foreach (range(1, 50) as $index)
        {
            $os = $faker->randomElement(array_keys($this->distros));

            Resource::create([
                // Generate a version 5 (name-based and hashed with SHA1) UUID object
                'os' => $os,
                'os_version' => $faker->randomElement($this->distros[$os]),
                'local_ip' => $faker->localIpv4(),
            ]);
        }
    }
}