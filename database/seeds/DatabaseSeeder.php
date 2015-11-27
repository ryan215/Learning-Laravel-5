<?php

use App\Resource;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (App::environment() == 'production') {
            exit('Can not seed the database in production mode.');
        }

        $tables = [
            'users',
            'resources',
            'leases',
        ];

        Model::unguard();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($tables as $table) {
            $this->call(ucfirst($table) . 'TableSeeder');
        }

        Model::reguard();
    }
}
