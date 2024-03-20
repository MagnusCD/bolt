<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/forumform", name="forumform")
     */
    public function forumForm(): Response
    {
        // Replace 'forumform.twig' with your actual Twig template
        return $this->render('forumform.twig');
    }
}
