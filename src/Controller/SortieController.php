<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        return $this->render('pages/accueil.html.twig');
    }
    #[Route('/sortie/creer', name: 'sortie_creer')]
    public function creer(Request $request): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        return $this->render('pages/creer.html.twig' , [
        'sortieForm' => $sortieForm->createView()
    ]);
    }

}
