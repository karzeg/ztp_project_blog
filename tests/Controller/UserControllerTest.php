<?php
/**
 * User controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Class HelloControllerTest.
 */
class UserControllerTest extends WebTestCase
{

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test registration route.
     */
    public function testRegistrationRoute(): void
    {
        $expectedStatusCode = 200;

        $this->httpClient->request('GET', '/registration');

        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $resultHttpStatusCode);
    }

    /**
     * Test manage users route for anonymous user.
     */
    public function testManageRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', '/user');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test manage users route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testManageRouteAdminUser(): void
    {
        // given
        $this->removeUser('user_admin@email.com');
        $this->removeUser('user_user@email.com');
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'user_admin@email.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->followRedirects(true);
        $this->httpClient->request('GET', '/user');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->removeUser('user_admin@email.com');
    }

    /**
     * Test show single user.
     */
    public function testShowUser(): void
    {
        $this->removeUser('user_admin@email.com');
        $this->removeUser('user_user@email.com');
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'user_admin@email.com');
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', '/user/' . $adminUser->getId());
        $result = $this->httpClient->getResponse();

        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createUser(array $roles, string $email): User
    {
        $this->removeUser1();
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('test3@example.com');
        $user->setRoles($roles);
        $user->setLogin('user1');
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

    private function removeUser1(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $entity = $userRepository->findOneBy(array('email' => 'test3@example.com'));

        if ($entity != null){
            $userRepository->delete($entity);
        }
    }

    private function removeUser(string $email): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $entity = $userRepository->findOneBy(array('email' => $email));

        if ($entity != null){
            $userRepository->remove($entity);
        }
    }
}