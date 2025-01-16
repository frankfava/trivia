<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a Personal access Client for Passport
        if (Client::where('name', $name = 'Trivia Personal Access Client')->doesntExist()) {
            $client = app(ClientRepository::class)->createPersonalAccessClient(
                userId: null,
                name: $name,
                redirect: url('/')
            );
            $this->updateEnvFileForPassport($client->id, $client->plainSecret);
        }

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /** Update the .env file with the Passport client ID and secret. */
    protected function updateEnvFileForPassport($clientId, $clientSecret)
    {
        // Read the current .env file
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Update or add the entries for Passport client
        $envContent = preg_replace(
            '/^PASSPORT_PERSONAL_ACCESS_CLIENT_ID=.*/m',
            "PASSPORT_PERSONAL_ACCESS_CLIENT_ID=\"$clientId\"",
            $envContent
        );

        $envContent = preg_replace(
            '/^PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=.*/m',
            "PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=\"$clientSecret\"",
            $envContent
        );

        // If the lines do not exist, add them
        if (! preg_match('/^PASSPORT_PERSONAL_ACCESS_CLIENT_ID=/m', $envContent)) {
            $envContent .= "\nPASSPORT_PERSONAL_ACCESS_CLIENT_ID=\"$clientId\"";
        }

        if (! preg_match('/^PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=/m', $envContent)) {
            $envContent .= "\nPASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=\"$clientSecret\"";
        }

        // Write the updated content back to .env
        file_put_contents($envPath, $envContent);
    }
}
