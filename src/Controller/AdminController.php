<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\CreerParticiantType;
use App\Service\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/creer-un-participant', name: 'admin_creer_participant')]
    public function creerUnParticipant(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, ImageManager $imageManager): Response
    {
        $participant = new Participant();
        $participantForm = $this->createForm(CreerParticiantType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            $champPassword = $participantForm->get('motPasse')->getData();

            $hashedPassword = $passwordHasher->hashPassword(user: $participant, plainPassword: $champPassword);
            $participant->setMotPasse($hashedPassword);

            $participant->setAdministrateur('false');
            $participant->setActif('true');

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('pages/admin/creerParticipant.html.twig', [
            'participantForm' => $participantForm->createView()
        ]);
    }
}
