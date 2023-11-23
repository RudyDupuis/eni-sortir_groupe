<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class LieuController extends AbstractController
{
    #[Route('/lieu/creer', name: 'lieu_creer')]
    public function creerLieu(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lieu = new Lieu;
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_creer');
        }

        return $this->render('pages/creerLieu.html.twig', [
            'lieuForm' => $lieuForm->createView(),
        ]);
    }
}
