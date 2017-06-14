<?php

namespace CONSERTO\KiosqueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CONSERTOKiosqueBundle:Default:index.html.twig');
    }
}
