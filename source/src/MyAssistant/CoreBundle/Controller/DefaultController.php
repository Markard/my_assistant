<?php

namespace MyAssistant\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MyAssistantCoreBundle:Default:index.html.twig');
    }
}
