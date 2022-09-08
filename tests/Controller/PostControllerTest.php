<?php

namespace App\Tests\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Enum\UserRole;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Generator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class PostControllerTest.
 */
class PostControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/post';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test route for anonymous user.
     */
    public function testRoute(): void
    {
        $expectedStatusCode = 200;

        $this->httpClient->request('GET', self::TEST_ROUTE);

//        $zmienna = $this->httpClient->request('GET', self::TEST_ROUTE);
//        var_dump($zmienna);

        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testPostAdminUser(): void
    {
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * @param array $roles
     *
     * @return User
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User;
        $user->setEmail('user@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword($user, 'password')
        );

        return $user;
    }
}