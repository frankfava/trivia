<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        if (Client::where('name', $name = 'Test PAC')->doesntExist()) {
            $client = app(ClientRepository::class)->createPersonalAccessClient(
                userId: null,
                name: $name,
                redirect: url('/')
            );
            Config::set('passport.personal_access_client.id', $client->id);
            Config::set('passport.personal_access_client.secret', $client->plainSecret);
        }
    }

    /**
     * Decode Response
     */
    protected function content($response, $asArray = true)
    {
        return json_decode($response->content(), !!$asArray) ?? $response->content();
    }

    /**
     * Get Original Response
     */
    protected function original($response)
    {
        $original = $response->original;
        return ($original instanceof JsonResource) ? $original->resource : $original;
    }
}
