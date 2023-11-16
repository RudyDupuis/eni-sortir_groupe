<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, SortieRepository $repository): Response
    {
        // Récupérer la date actuelle
        $currentDate = new \DateTime();

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer les sorties
        $sorties = $repository->findSearch();

        // Rendre la vue en passant la date et l'utilisateur
        return $this->render('pages/accueil.html.twig', [
            'currentDate' => $currentDate,
            'user' => $user,
            'sorties' => $sorties,
        ]);
    }
}
