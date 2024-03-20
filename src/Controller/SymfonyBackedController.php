<?php

namespace App\Controller;

use Bolt\BoltForms\Form\ContenttypeType;
use Bolt\Configuration\Content\ContentType;
use Bolt\Controller\TwigAwareController;
use Bolt\Entity\Content;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SymfonyBackedController extends TwigAwareController
{
    #[Route('/symfony/plain', name: 'plainly_symfony')]
    public function plainEndpoint()
    {
        return $this->render('@symfony/plain.html.twig');
    }

    #[Route('/symfony/post', name: 'symfony_post_formpage')]
    public function postForm()
    {

        $postEntityContentType = ContentType::factory('Post', $this->config->get('contenttypes'));
        $form = $this->createForm(ContenttypeType::class, $postEntityContentType, ['params' => ['contenttype' => 'Post']]);
        /**
        $postEntity = ContentType::factory('Post', $this->config->get('contenttypes'));
        $form = $this->createForm(ContenttypeType::class, $postEntity);
         **/

        return $this->render(
            '@symfony/post_form.html.twig',
            [
                'test' => 'Magnus!',
                'post_form' => $form->createView(),
            ]
        );
    }

}
