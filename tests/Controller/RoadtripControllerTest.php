<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoadtripTest extends WebTestCase
{
    public function testSearchResultsPageDisplaysCorrectly(): void
    {
        $client = static::createClient();
        $client->request('GET', '/roadtrip/recherche', [
            'steps[0][town]' => 'Toulouse',
            'steps[0][meals]' => 1,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.my-h1');
        $this->assertSelectorTextContains('.my-h1', 'Résultats de la recherche');
    }

    public function testSaveRoadtripRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/roadtrip/recherche', [
            'steps[0][town]' => 'Toulouse',
            'steps[0][meals]' => 1,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href*="/connexion"]');
    }
}