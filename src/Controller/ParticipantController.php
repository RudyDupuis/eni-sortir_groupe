<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use Symfony\Component\HttpFoundation\Request;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route(path: '/profil', name: 'app_profil')]
    public function profil(Request $request): Response
    {
        $participant = new Participant();
        $participantForm = $this->createForm(ProfilType::class, $participant);

        //todo traiter le formulaire
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $formData = $participantForm->getData();

            if ($formData->getPassword() !== $formData->getConfirmationPassword()) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            }
        }
        return $this->render('pages/profil.html.twig', [
            'participantForm' => $participantForm->createView()
        ]);
    }
}
