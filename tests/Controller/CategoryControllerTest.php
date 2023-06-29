<?php
/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Repository\CategoryRepository;
use App\Tests\WebBaseTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Monolog\DateTimeImmutable;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebBaseTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

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
        $user = null;
        $expectedStatusCode = 200;
        try {
            $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'categoryindexuser@example.com');
        } catch (OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface|ORMException $e) {
        }
        $this->logIn($user);

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'category_user@example.com');
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for non-authorized user.
     */
    public function testIndexRouteNonAuthorizedUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value], 'category_user2@example.com');
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals(200, $resultStatusCode);
    }

    /**
     * Test show single category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowCategory(): void
    {
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'category_user2@exmaple.com');
        $this->httpClient->loginUser($adminUser);

        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test category');
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($expectedCategory);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $expectedCategory->getId());
        $result = $this->httpClient->getResponse();

        $this->assertEquals(200, $result->getStatusCode());
    }

    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     */
    public function testCreateCategory(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value],
            'category_created_user2@example.com');
        $this->httpClient->loginUser($user);

        $categoryCategoryName = "createdCategory";
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');

        $this->httpClient->submitForm('Zapisz',
            ['category' => ['title' => $categoryCategoryName]]
        );

        $savedCategory = $categoryRepository->findOneByTitle($categoryCategoryName);
        $this->assertEquals($categoryCategoryName,
            $savedCategory->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * @return void
     */
    public function testEditCategoryUnauthorizedUser(): void
    {
        $expectedHttpStatusCode = 302;

        $category = new Category();
        $category->setTitle('TestCategory');
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' .
            $category->getId() . '/edit');
        $actual = $this->httpClient->getResponse();

        $this->assertEquals($expectedHttpStatusCode,
            $actual->getStatusCode());
    }

    /**
     * @return void
     */
    public function testEditCategory(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value],
            'category_edit_user1@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategory');
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();
        $expectedNewCategoryName = 'TestCategoryEdit';

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' .
            $testCategoryId . '/edit');

        $this->httpClient->submitForm(
            'Edytuj',
            ['category' => ['title' => $expectedNewCategoryName]]
        );

        $savedCategory = $categoryRepository->findOneById($testCategoryId);
        $this->assertEquals($expectedNewCategoryName, $savedCategory->getTitle());
    }

    /**
     * @return void
     */
    public function testNewRoutAdminUser(): void
    {
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'categoryCreate1@example.com');
        $this->httpClient->loginUser($adminUser);
        $this->httpClient->request('GET', self::TEST_ROUTE . '/');
        $this->assertEquals(301, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * @return void
     */
    public function testDeleteCategory(): void
    {
        $user = null;
        try {
            $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'category_deleted_user1@example.com');
        } catch (OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
        $this->httpClient->loginUser($user);

        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated');
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testCategoryId . '/delete');

        $this->httpClient->submitForm(
            'Usuń'
        );

        $this->assertNull($categoryRepository->findOneByTitle('TestCategoryCreated'));
    }

    /**
     * @return void
     */
    public function testCantDeleteCategory(): void
    {
        $user = null;
        try {
            $user = $this->createUser([UserRole::ROLE_ADMIN->value],
                'category_deleted_user2@example.com');
        } catch (OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
        $this->httpClient->loginUser($user);

        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated2');
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testCategoryId . '/delete');

        $this->assertEquals(200, $this->httpClient->getResponse()->getStatusCode());
        $this->assertNotNull($categoryRepository->findOneByTitle('TestCategoryCreated2'));
    }
}