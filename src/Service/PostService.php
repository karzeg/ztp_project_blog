<?php
/**
 * Post service.
 */

namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class PostService.
 */
class PostService implements PostServiceInterface
{
    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Tag service.
     */
    private TagServiceInterface $tagService;

    /**
     * Post repository.
     */
    private PostRepository $postRepository;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     * @param PaginatorInterface       $paginator       Paginator
     * @param TagServiceInterface      $tagService      Tag service
     * @param PostRepository           $postRepository  Post repository
     */
    public function __construct(CategoryServiceInterface $categoryService, PaginatorInterface $paginator, TagServiceInterface $tagService, PostRepository $postRepository)
    {
        $this->categoryService = $categoryService;
        $this->paginator = $paginator;
        $this->tagService = $tagService;
        $this->postRepository = $postRepository;
    }

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<string, mixed> Paginated list
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPaginatedList(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->postRepository->queryAll(),
            $page,
            PostRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Post $post Post entity
     */
    public function save(Post $post): void
    {
        $this->postRepository->save($post);
    }

    /**
     * Delete entity.
     *
     * @param Post $post Post entity
     */
    public function delete(Post $post): void
    {
        $this->postRepository->delete($post);
    }

    /**
     * Delete post with comments.
     *
     * @param Post $post
     *
     * @return void
     */
    public function deletePostWithComments(Post $post): void
    {
        $this->postRepository->deleteCommentsForPost($post);
    }

    /**
     * Prepare filters for the posts list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if (null !== $category) {
                $resultFilters['category'] = $category;
            }
        }

        if (!empty($filters['tag_id'])) {
            $tag = $this->tagService->findOneById($filters['tag_id']);
            if (null !== $tag) {
                $resultFilters['tag'] = $tag;
            }
        }

        return $resultFilters;
    }
}
