<?php
/**
 * Category service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Service\CategoryService;
use App\Service\CategoryServiceInterface;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryServiceTest.
 */
class CategoryServiceTest extends KernelTestCase
{
    /**
     * Category repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Category service.
     */
    private ?CategoryServiceInterface $categoryService;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->categoryService = $container->get(CategoryService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        $expectedCategory = new Category();
        $expectedCategory->setTitle('TestCategory');

        $this->categoryService->save($expectedCategory);

        $expectedCategoryId = $expectedCategory->getId();
        $resultCategory = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $expectedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedCategory, $resultCategory);
    }

    /**
     * Test delete.
     *
     * @throws ORMException
     */
    public function testDelete(): void
    {
        $categoryToDelete = new Category();
        $categoryToDelete->setTitle('TestCategory');
        $this->entityManager->persist($categoryToDelete);
        $this->entityManager->flush();
        $deletedCategoryId = $categoryToDelete->getId();

        $this->categoryService->delete($categoryToDelete);

        $resultCategory = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $deletedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultCategory);
    }

    /**
     * Test find by id.
     *
     */
    public function testFindById(): void
    {
        // given
        $expectedCategory = new Category();
        $expectedCategory->setTitle('TestCategory');
        $this->entityManager->persist($expectedCategory);
        $this->entityManager->flush();
        $expectedCategoryId = $expectedCategory->getId();

        $resultCategory = $this->categoryService->findOneById($expectedCategoryId);

        $this->assertEquals($expectedCategory, $resultCategory);
    }

    /**
     * Test pagination empty list.
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $dataSetSize = 15;
        $expectedResultSize = 10;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $category = new Category();
            $category->setTitle('TestCategory #' . $counter);
            $this->categoryService->save($category);

            ++$counter;
        }

        $result = $this->categoryService->getPaginatedList($page);

        $this->assertEquals($expectedResultSize, $result->count());
    }

    /**
     * Test pagination empty list.
     */
    public function testGetPaginatedSearchList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 15;
        $expectedResultSize = 10;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $category = new Category();
            $category->setTitle('TestCategory name#' . $counter);
            $this->categoryService->save($category);

            ++$counter;
        }

        $result = $this->categoryService->getPaginatedList($page, 'name');

        $this->assertEquals($expectedResultSize, $result->count());
    }
}