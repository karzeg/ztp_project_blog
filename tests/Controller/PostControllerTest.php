<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\Enum\UserRole;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testPostRoute(): void
    {
        $client = static::createClient();

        $client->request('GET', '/post');
        $resultHttpStatusCode = $client->getResponse()->getStatusCode();

        $this->assertEquals(200, $resultHttpStatusCode);
    }
}
