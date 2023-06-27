<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class WebBaseTestCase extends WebTestCase
{
    /**
     * Test client.
     */
    protected KernelBrowser $httpClient;

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     */
    protected function createUser(array $roles, string $email): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }

    /**
     * Simulate user log in.
     *
     * @param User $user User entity
     */
    protected function logIn(User $user): void
    {
        $session = self::getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->httpClient->getCookieJar()->set($cookie);
    }

    /**
     * Remove user.
     */
    protected function removeUser(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $entity = $userRepository->findOneBy(array('email' => 'test2@example.com'));


        if ($entity !== null) {
            $userRepository->remove($entity);
        }
    }

    /**
     * Create Category.
     *
     * @return Category
     */
    protected function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('TestCategory');
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }

    /**
     * Create Comment.
     */
    protected function createComment(): Comment
    {
        $comment = new Comment();
        $comment->setContent('TestComment');
        $commentRepository = self::getContainer()->get(Comment::class);
        $commentRepository->save($comment);

        return $comment;
    }

    /**
     * Create Post.
     */
    protected function createPost(User $user, Category $category, Tag $tag): Post
    {
        $post = new Post();
        $post->setTitle('PName');
        $post->setContent('PContent');
        $post->setCategory($category);
        $postRepository = self::getContainer()->get(PostRepository::class);
        $postRepository->save($post, true);

        return $post;
    }

    /**
     * Create Tag.
     */
    protected function createTag(): Tag
    {
        $tag = new Tag();
        $tag->setTitle('TestTag');
        $tagRepository = self::getContainer()->get(TagRepository::class);
        $tagRepository->save($tag);

        return $tag;
    }
}