<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SortieController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, Security $security): Response
    {
        // Récupérer la date actuelle
        $currentDate = new \DateTime();

        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        // Rendre la vue en passant la date et l'utilisateur
        return $this->render('pages/accueil.html.twig', [
            'currentDate' => $currentDate,
            'user' => $user,
        ]);
    }
}
