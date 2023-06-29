<?php
/**
 * Registration controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\RegistrationType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RegistrationController.
 */
class RegistrationController extends AbstractController
{
    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Registration.
     *
     * @param Request                     $request
     * @param UserRepository              $userRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @param UserAuthenticatorInterface  $userAuthenticator
     * @param LoginFormAuthenticator      $formAuthenticator
     *
     * @return Response
     */
    #[Route(
        '/registration',
        name: 'app_register',
        methods: 'GET|POST'
    )]
    public function register(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $formAuthenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $user->setRoles([User::ROLE_USER]);
            $userRepository->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $userAuthenticator->authenticateUser($user, $formAuthenticator, $request);
        }

        return $this->render(
            'registration/register.html.twig',
            ['form' => $form->createView()]
        );
    }
}
