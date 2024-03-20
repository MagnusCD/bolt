<?php

namespace App\Controller;

use Bolt\Common\Str;
use Bolt\Configuration\Content\ContentType;
use Bolt\Controller\Frontend\FrontendZoneInterface;
use Bolt\Controller\TwigAwareController;
use Bolt\Entity\Content;
use Bolt\Repository\ContentRepository;
use Bolt\Storage\Query;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class FrontendLoginController extends TwigAwareController implements FrontendZoneInterface
{

    #[Route(
        '/login',
        name: 'login',
        methods: ['GET', 'POST'],
        priority: 250
    )]

    #[Route(
        '/login-check',
        name: 'login_check',
        methods: ['GET', 'POST'],
        priority: 300
    )]

    #[Route(
        '/logout',
        name: 'logout',
        methods: ['GET', 'POST'],
        priority: 350
    )]

    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    public function check(Request $request)
    {
        // This code is never executed; Symfony's security system intercepts this route
    }

    public function logout()
    {
        // This code is never executed; Symfony's security system handles the logout
    }
}


