<?php

namespace App\Test\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Enum\UserRole;
use App\Repository\CommentRepository;
use App\Tests\WebBaseTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebBaseTestCase
{
    private CommentRepository $repository;
    private string $path = '/comment/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Comment::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->delete($object, true);
        }
    }

    /**
     * Test Index
     */
    public function testIndex(): void
    {
        $expectedStatusCode = 404;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'comment_admin1@example.com');
        $this->client->loginUser($adminUser);
        $crawler = $this->client->request('GET', $this->path);
        $result = $this->client->getResponse();

        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
    }

    /**
     * Test New Comment
     */
    public function testNewComment(): void
    {
        $expectedStatusCode = 200;
        $userEmail = 'comment_new_user@example.com';
        $adminUser = $this->createUser([UserRole::ROLE_USER->value], $userEmail);
        $this->client->loginUser($adminUser);
        $category = $this->createCategory();
        $post = $this->createPost($adminUser, $category);

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->client->request('GET', sprintf('%snew', $this->path));
        $result = $this->client->getResponse();


        $this->client->submitForm('Zapisz', [
            'comment[content]' => 'Test Content',
            'comment[author]' => $userEmail,
            'comment[post]' => $post->getId(),
        ]);

        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
        $this->assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }
}