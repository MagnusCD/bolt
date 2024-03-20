<?php

namespace App\Controller;

use Bolt\Common\Str;
use Bolt\Configuration\Config;
use Bolt\Controller\CsrfTrait;
use Bolt\Controller\Frontend\FrontendZoneInterface;
use Bolt\Controller\TwigAwareController;
use Bolt\Entity\User;
use Bolt\Enum\UserStatus;
use Bolt\Event\UserEvent;
use Bolt\Form\UserType;
use Bolt\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class FrontendRegisterController extends TwigAwareController implements FrontendZoneInterface
{

    /**
     * @ORM\Entity
     * @UniqueEntity(fields={"email"}, message="This email is already used.")
     * @UniqueEntity(fields={"username"}, message="This username is already taken.")
     */

    use CsrfTrait;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserPasswordHasherInterface */
    private $passwordHasher;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var string */
    protected $defaultLocale;

    /** @var array */
    private $assignableRoles;

    public function __construct(
        UrlGeneratorInterface       $urlGenerator,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface   $csrfTokenManager,
        EventDispatcherInterface    $dispatcher,
        Config                      $config,
        string                      $defaultLocale
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->dispatcher = $dispatcher;
        $this->defaultLocale = $defaultLocale;
        $this->assignableRoles = $config->get('permissions/assignable_roles')->all();
    }

    #[Route('/register', name: 'register_success', methods: ['GET', 'POST'], priority: 400)]
    public function register(Request $request, UserRepository $userRepository): Response
    {
        $user = UserRepository::factory();

        /** @var array $submitted_data */
        $submitted_data = $request->request->get('user');

        $event = new UserEvent($user);
        $this->dispatcher->dispatch($event, UserEvent::ON_ADD);
        $roles = $this->getPossibleRolesForForm();

        // These are the variables we have to pass into our FormType so we can build the fields correctly
        $form_data = [
            'suggested_password' => Str::generatePassword(),
            'roles' => $roles,
            'require_username' => true,
            'require_password' => true,
            'default_locale' => $this->defaultLocale,
            'is_profile_edit' => false,
        ];
        $form = $this->createForm(UserType::class, $user, $form_data);


        if ($request->getMethod() === Request::METHOD_POST) {
            // We need to pre-check that we are not re-posting a username or an email, which is already in the database of users...

            $postedUserData = $request->request->all()['user'];
            $postedUserName = $postedUserData['username'];
            $postedEmail = $postedUserData['email'];

            $userNameExists = $userRepository->findOneByUsername($postedUserName);
            $emailExists = $userRepository->findOneByEmail($postedEmail);

            if(!$userNameExists && !$emailExists) {
                $form->handleRequest($request);
                $this->handleValidFormSubmit($form);
            } else {
                $request->getSession()->getFlashBag()->clear();
                $errorMessage = '';

                if ($userNameExists) {
                    $errorMessage .= 'A user with the given username already exists. ';
                }

                if ($emailExists) {
                    $errorMessage .= 'A user with the given email already exists. ';
                }

                $this->addFlash('notice', $errorMessage);
            }
        }


        return $this->render('register.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }



    /**
     * This function is called by add and edit function if given form was submitted and validated correctly.
     * Here the User Object will be persisted to the DB. A security exception will be raised if the roles
     * for the user being saved are not allowed for the current logged user.
     */
    private function handleValidFormSubmit(FormInterface $form): void
    {
        // Get the adjusted User Entity from the form
        /** @var User $user */
        $user = $form->getData();

        // Once validated, encode the password
        if ($user->getPlainPassword()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
            $user->eraseCredentials();
        }

        $event = new UserEvent($user);
        $this->dispatcher->dispatch($event, UserEvent::ON_PRE_SAVE);

        // Save the new user data into the DB
        $this->em->persist($user);
        $this->em->flush();

        $event = new UserEvent($user);
        $this->dispatcher->dispatch($event, UserEvent::ON_POST_SAVE);

        $this->addFlash('success', 'user.updated_profile');
    }

    private function getPossibleRolesForForm(): array
    {
        $result = [];
        $assignableRoles = $this->assignableRoles;

        // convert into array for form
        foreach ($assignableRoles as $assignableRole) {
            $result[$assignableRole] = $assignableRole;
        }

        return $result;
    }
}

