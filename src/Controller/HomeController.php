<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('public/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/charte-restaurateur', name: 'app_charte_restaurateur')]
    public function charteRestaurateur(): Response
    {
        return $this->render('public/charte_restaurateur.html.twig');
    }
}
