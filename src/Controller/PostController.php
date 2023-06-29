<?php
/**
 * Post controller.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\Type\CommentType;
use App\Form\Type\PostType;
use App\Repository\CommentRepository;
use App\Service\CommentService;
use App\Service\PostServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PostController.
 */
#[Route('/post')]
class PostController extends AbstractController
{
    /**
     * Post service.
     */
    private PostServiceInterface $postService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    private CommentService $commentService;

    /**
     * Constructor.
     *
     * @param PostServiceInterface $postService Post service
     * @param TranslatorInterface  $translator  Translator
     * @param CommentService $commentService Comment Service
     */
    public function __construct(PostServiceInterface $postService, TranslatorInterface $translator, CommentService $commentService)
    {
        $this->postService = $postService;
        $this->translator = $translator;
        $this->commentService = $commentService;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'post_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $filters = $this->getFilters($request);
        $pagination = $this->postService->getPaginatedList(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render(
            'post/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param Request $request request
     * @param Post $post Post entity
     * @param CommentRepository $commentRepository param
     * @param EntityManagerInterface $em param
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'post_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Request $request, Post $post, CommentRepository $commentRepository, EntityManagerInterface $em): Response
    {
        $postId = $post->getId();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $this->commentService->save($comment);
            $redirectUrl = $this->generateUrl('post_show', ['id' => $postId]);

            return $this->redirect($redirectUrl);
        }
        $filteredComment = $commentRepository->findBy(['post' => $postId]);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $filteredComment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     * @IsGranted("ROLE_ADMIN")
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/create',
        name: 'post_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Post    $post    Post entity
     *
     * @return Response HTTP response
     *
     * @IsGranted("ROLE_ADMIN")
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/{id}/edit',
        name: 'post_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post, [
            'method' => 'PUT',
            'action' => $this->generateUrl('post_edit', ['id' => $post->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/edit.html.twig',
            [
                'form' => $form->createView(),
                'post' => $post,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Post    $post    Post entity
     *
     * @return Response HTTP response
     *
     * @IsGranted("ROLE_ADMIN")
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/{id}/delete',
        name: 'post_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    public function delete(Request $request, Post $post): Response
    {
        $form = $this->createForm(FormType::class, $post, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('post_delete', ['id' => $post->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->delete($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/delete.html.twig',
            [
                'form' => $form->createView(),
                'post' => $post,
            ]
        );
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{category_id: int, tag_id: int, status_id: int}
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['category_id'] = $request->query->getInt('filters_category_id');
        $filters['tag_id'] = $request->query->getInt('filters_tags_id');

        return $filters;
    }
}
