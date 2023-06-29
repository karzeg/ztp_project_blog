<?php
/**
 * Tag Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Tests\WebBaseTestCase;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class TagControllerTest.
 */
class TagControllerTest extends WebBaseTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/tag';

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * @return void
     */
    public function testIndexRouteAnonymousUser(): void
    {
        //given
        $user = null;
        $expectedStatusCode = 200;
        try {
            $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'tagindexuser@example.com');
        } catch (OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface|ORMException $e) {
        }
        $this->logIn($user);

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        //when
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        //given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'tag_user@example.com');
        $this->httpClient->loginUser($adminUser);

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        //then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for non-authorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteNonAuthorizedUser(): void
    {
        //given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'tag_user2@example.com');
        $this->httpClient->loginUser($user);

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        //then
        $this->assertEquals(200, $resultStatusCode);
    }



    /**
     * Test show single tag.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowTag(): void
    {
        //given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'tag_user2@exmaple.com');
        $this->httpClient->loginUser($adminUser);

        $expectedTag = new Tag();
        $expectedTag->setTitle('Test tag');
        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tagRepository->save($expectedTag);

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $expectedTag->getId());
        $result = $this->httpClient->getResponse();

        //then
        $this->assertEquals(200, $result->getStatusCode());
//        $this->assertSelectorTextContains('html td', $expectedTag->getId());
    }

    //create tag

    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     */
    public function testCreateTag(): void
    {
        //given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value],
            'tag_created_user2@example.com');
        $this->httpClient->loginUser($user);

        $tagRepository = static::getContainer()->get(TagRepository::class);

        $tagTagName = "createdTag";

        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');

        //when
        $this->httpClient->submitForm(
            'Zapisz',
            ['tag' => ['title' => $tagTagName]]
        );

        //then
        $savedTag = $tagRepository->findOneByTitle($tagTagName);
        $this->assertEquals($tagTagName,
            $savedTag->getTitle());


        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());

    }

    /**
     * @return void
     */
    public function testEditTagUnauthorizedUser(): void
    {
        //given
        $expectedHttpStatusCode = 302;

        $tag = new Tag();
        $tag->setTitle('TestTag');
        $tagRepository =
            static::getContainer()->get(TagRepository::class);
        $tagRepository->save($tag);

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' .
            $tag->getId() . '/edit');
        $actual = $this->httpClient->getResponse();

        //then
        $this->assertEquals($expectedHttpStatusCode,
            $actual->getStatusCode());

    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testEditTag(): void
    {
        //given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value],
            'tag_edit_user1@example.com');
        $this->httpClient->loginUser($user);

        $tagRepository =
            static::getContainer()->get(TagRepository::class);
        $testTag = new Tag();
        $testTag->setTitle('TestTag');
        $tagRepository->save($testTag);
        $testTagId = $testTag->getId();
        $expectedNewTagName = 'TestTagEdit';

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' .
            $testTagId . '/edit');

        //when
        $this->httpClient->submitForm(
            'Edytuj',
            ['tag' => ['title' => $expectedNewTagName]]
        );

        //then
        $savedTag = $tagRepository->findOneById($testTagId);
        $this->assertEquals($expectedNewTagName,
            $savedTag->getTitle());
    }


    /**
     */
    public function testNewRoutAdminUser(): void
    {
        //given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'tagCreate1@example.com');
        $this->httpClient->loginUser($adminUser);

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/');

        //then
        $this->assertEquals(301, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * @return void
     */
    public function testDeleteTag(): void
    {
        // given
        $user = null;
        try {
            $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value],
                'tag_deleted_user1@example.com');
        } catch (OptimisticLockException|ORMException|ContainerExceptionInterface $e) {
        }
        $this->httpClient->loginUser($user);

        $tagRepository =
            static::getContainer()->get(TagRepository::class);
        $testTag = $this->createTag('TestTagCreated');

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testTag->getId() . '/delete');

        //when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($tagRepository->findOneByTitle('TestTagCreated'));
    }
}