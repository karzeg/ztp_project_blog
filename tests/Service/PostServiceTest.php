<?php
/**
 * Post Service Test.
 */

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\Enum\UserRole;
use App\Repository\PostRepository;
use App\Service\PostService;
use App\Tests\WebBaseTestCase;
use Doctrine\ORM\Exception\ORMException;

/**
 * Class PostServiceTest.
 *
 * @property $entityManager
 */
class PostServiceTest extends WebBaseTestCase
{
    /**
     * Post service.
     */
    private ?PostService $postService;

    /**
     * @return void void
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->postService = $container->get(PostService::class);
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Post::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    /**
     * @return void
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testDelete(): void
    {
        $postRepository =
            static::getContainer()->get(PostRepository::class);

        $user = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'post_ser_delete_admin@example.com');
        $category = $this->createCategory();
        $postToDelete = $this->createPost($user, $category);

        $postRepository->save($postToDelete);

        $before = $postRepository->findAll();

        $this->postService->delete($postToDelete);
        $after = $postRepository->findAll();

        $this->assertEquals(count($before), count($after) + 1);
    }
}