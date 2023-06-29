<?php
/**
 * Comment controller.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\Type\CommentType;
use App\Repository\CommentRepository;
use App\Service\CommentServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CommentController.
 */
#[Route('/comment')]
class CommentController extends AbstractController
{
    /**
     * Comment service.
     */
    private CommentServiceInterface $commentService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param CommentServiceInterface $commentService
     * @param TranslatorInterface     $translator
     */
    public function __construct(CommentServiceInterface $commentService, TranslatorInterface $translator)
    {
        $this->commentService = $commentService;
        $this->translator = $translator;
    }

    /**
     * Create action.
     *
     * @param Request           $request           HTTP request
     * @param Post              $post              Post entity
     * @param CommentRepository $commentRepository Comment Repository
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create/post/{id}',
        name: 'comment_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request, Post $post, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();
        $id = $post->getId();
        $author = $this->getUser();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $comment->setAuthor($author);
            $this->commentService->save($comment);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('post_show', ['id' => $id]);
        }

        return $this->render(
            'comment/create.html.twig',
            [
                'form' => $form->createView(),
                'comment' => $comment,
                'post' => $post->getId(),
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Comment $comment Comment entity
     *
     * @return Response HTTP response
     */
    #[IsGranted('EDIT', subject: 'comment')]
    #[Route(
        '/{id}/edit',
        name: 'comment_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    public function edit(Request $request, Comment $comment): Response
    {
        $form = $this->createForm(CommentType::class, $comment, [
            'method' => 'PUT',
            'action' => $this->generateUrl('comment_edit', ['id' => $comment->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentService->save($comment);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'comment/edit.html.twig',
            [
                'form' => $form->createView(),
                'comment' => $comment,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Comment $comment Comment entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'comment_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    public function delete(Request $request, Comment $comment): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('warning', $this->translator->trans('message_action_impossible'));

            return $this->redirectToRoute('post_index');
        }

        $form = $this->createForm(FormType::class, $comment, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('comment_delete', ['id' => $comment->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentService->delete($comment);
            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'comment/delete.html.twig',
            [
                'form' => $form->createView(),
                'comment' => $comment,
            ]
        );
    }
}
