<?php

namespace App\Controller;

use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ParticipantController extends AbstractController
{


    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_accueil');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('pages/connexion.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/mon-profil', name: 'app_profil')]
    public function profil(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, ImageManager $imageManager): Response
    {
        /** @var \App\Entity\Participant $participant */
        $participant = $this->getUser();
        $participantForm = $this->createForm(ProfilType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            $champPassword = $participantForm->get('motPasse')->getData();

            if ($champPassword) {
                $hashedPassword = $passwordHasher->hashPassword(user: $participant, plainPassword: $champPassword);
                $participant->setMotPasse($hashedPassword);
            }

            $newFileName = $imageManager->saveImage($participantForm->get('photoDeProfil')->getData(), $participant->getPhotoDeProfil(), 'profile_pictures_directory');

            if ($newFileName) {
                $participant->setPhotoDeProfil($newFileName);
            }


            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('pages/monProfil.html.twig', [
            'participantForm' => $participantForm->createView()
        ]);
    }

    #[Route(path: '/participant/{id}', name: 'app_participant')]
    public function profilParticipant(ParticipantRepository $participantRepository, int $id): Response
    {
        $participant = $participantRepository->find($id);

        return $this->render('pages/informationsParticipant.html.twig', [
            'participant' => $participant
        ]);
    }
}
