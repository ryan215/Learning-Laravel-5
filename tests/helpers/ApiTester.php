<?php

use App\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;

abstract class ApiTester extends TestCase {

    protected $fake;
    protected $apiBase = 'api/v1';
    protected $user;

    public function __construct()
    {
        $this->fake = Faker::create();
    }

    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate');
        Artisan::call('db:seed');

        $this->user = User::find(1);
        $this->user->logins = [$this->user->email, 'password'];
    }


    protected function getJson($uri, $method = 'GET', $server = [])
    {
        if (is_object($server)) {
            $server = ['PHP_AUTH_USER' => $server->email, 'PHP_AUTH_PW' => TEST_PASSWORD];
        }
        return json_decode($this->call($method, $this->apiBase . $uri, [], [], [], $server)->getContent());

    }

    protected function assertObjectHasAttributes()
    {
        $args = func_get_args();
        $object = array_shift($args);

        foreach ($args as $attribute) {
            $this->assertObjectHasAttribute($attribute, $object);
        }

    }

    public function debug($data, $message)
    {
        echo chr(13) . $message . chr(13);
        print_r($data);
    }

}
