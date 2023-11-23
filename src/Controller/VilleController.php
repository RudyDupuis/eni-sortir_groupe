<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

class VilleController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/ville/{id}/lieux')]
    public function recupererLieuxDuneVille(LieuRepository $lieuRepository, VilleRepository $villeRepository, int $id)
    {
        $ville = $villeRepository->find($id);
        $lieux = $lieuRepository->rechercheParVille($ville);

        $lieuxTableau = [];
        foreach ($lieux as $lieu) {
            $lieuxTableau[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
                'rue' => $lieu->getRue(),
                'codePostal' => $ville->getCodePostal(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude(),
            ];
        }
        return new JsonResponse($lieuxTableau);
    }

    #[Route('/admin/villes/ajouter', name: 'app_villes')]
    public function ajouterVille(Request $request, VilleRepository $villeRepository, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('searchTerm');

        $nouvelleVille = new Ville();

        // Utilisez le terme de recherche pour obtenir les villes filtrées
        $villes = $villeRepository->findFiltered($searchTerm);

        $villeForm = $this->createForm(VilleType::class, $nouvelleVille);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            // Vérifier si la ville existe déjà
            $existingVille = $villeRepository->findOneBy([
                'nom' => $nouvelleVille->getNom()
            ]);

            if ($existingVille) {
                // La ville existe déjà, affichez un message d'erreur
                $this->addFlash('error', 'Cette ville existe déjà.');
            } else {
                // La ville n'existe pas encore, persistez-la
                $entityManager->persist($nouvelleVille);
                $entityManager->flush();

                $this->addFlash('success', 'Nouvelle ville ajoutée avec succès.');

                return $this->redirectToRoute('app_villes');
            }
        }

        // Utilisez la variable $villes pour obtenir toutes les villes existantes
        return $this->render('pages/villes.html.twig', [
            'villeForm' => $villeForm->createView(),
            'villes' => $villes,
            'searchTerm' => $searchTerm,
        ]);
    }


    #[Route('/admin/villes/{id}/modifier', name: 'ville_modifier', methods: ['POST'])]
    public function modifierVille(Request $request, Ville $ville, VilleRepository $villeRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupère les nouvelles valeurs du formulaire
        $newName = $request->request->get('new_name');
        $newPostalCode = $request->request->get('new_postalCode');

        // Vérifie si les champs ne sont pas vides
        if (empty($newName) || empty($newPostalCode)) {
            // Affiche un message d'erreur
            $this->addFlash('error', 'Veuillez remplir tous les champs.');
            return $this->redirectToRoute('app_villes'); // Redirige vers la page souhaitée
        }

        // Vérifie si la ville existe déjà dans la base de données
        $existingVille = $villeRepository->findOneBy(['nom' => $newName]);

        if ($existingVille && $existingVille->getId() !== $ville->getId()) {
            // Affiche un message d'erreur si la ville existe déjà
            $this->addFlash('error', 'La ville existe déjà dans la base de données.');
            return $this->redirectToRoute('app_villes'); // Redirige vers la page souhaitée
        }

        // Applique les nouvelles valeurs à l'entité Ville
        $ville->setNom($newName);
        $ville->setCodePostal($newPostalCode);

        // Persiste les changements en base de données
        $entityManager->flush();

        // Affiche un message de succès
        $this->addFlash('success', 'La ville a été modifiée avec succès.');

        // Redirige vers la page souhaitée après la modification.
        return $this->redirectToRoute('app_villes');
    }


    #[Route('/admin/villes/{id}/supprimer', name: 'ville_supprimer', methods: ['GET'])]
    public function supprimerVille(Ville $ville): Response
    {
        // Vérifier si la ville est utilisée dans une sortie
        if ($this->isVilleUsedInSortie($ville)) {
            $this->addFlash('error', 'La ville est utilisée dans au moins une sortie et ne peut pas être supprimée.');
        } else {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($ville);
            $entityManager->flush();

            $this->addFlash('success', 'Ville supprimée avec succès.');
        }

        return $this->redirectToRoute('app_villes');
    }

    /**
     * Vérifie si la ville est utilisée dans au moins une sortie.
     *
     * @param Ville $ville
     * @return bool
     */
    private function isVilleUsedInSortie(Ville $ville): bool
    {
        // Récupérer les lieux associés à la ville
        $lieux = $ville->getLieux();

        // Parcourir les lieux et vérifier s'ils sont utilisés dans au moins une sortie
        foreach ($lieux as $lieu) {
            if (!$lieu->getSorties()->isEmpty()) {
                return true;
            }
        }

        return false;
    }
}
