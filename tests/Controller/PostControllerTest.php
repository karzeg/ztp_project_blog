<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\Enum\UserRole;
use App\Repository\PostRepository;
use App\Tests\WebBaseTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebBaseTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/post';

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Post::class);
    }

    /**
     * Test index route
     *
     * @return void
     */
    public function testIndex(): void
    {
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'post_index_admin@example.com');
        $this->httpClient->loginUser($adminUser);
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $result = $this->httpClient->getResponse();

        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
    }

    /**
     * Test new post
     *
     * @return void
     */
    public function testNewPost(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'post_new_admin@example.com');
        $this->httpClient->loginUser($user);

        $category = $this->createCategory();
        $tag = $this->createTag();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');

        $this->httpClient->submitForm('Zapisz', [
            'post[title]' => 'Testing',
            'post[content]' => 'Testing',
            'post[category]' => $category->getId(),
        ]);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals(302, $resultStatusCode);
    }

    public function testShow(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'post_show_admin@example.com');
        $category = $this->createCategory();
        $tag = $this->createTag();
        $fixture = $this->createPost($user, $category, $tag);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$fixture->getId());

        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals(200, $resultStatusCode);
    }
}
