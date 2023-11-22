<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Data\SearchDataRechercher;
use App\Form\SearchFormRechercher;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;

class VillesController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/villes/ajouter', name: 'app_villes')]
    public function ajouterVille(Request $request, VilleRepository $villeRepository, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('searchTerm');

        // Utilisez une variable différente pour la nouvelle ville
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


    #[Route('/villes/{id}/modifier', name: 'ville_modifier', methods: ['POST'])]
    public function modifierVille(Request $request, Ville $ville): Response
    {
        // Vérifie si la requête est de type POST
        if ($request->isMethod('POST')) {
            // Récupère les nouvelles valeurs du formulaire
            $newName = $request->request->get('new_name');
            $newPostalCode = $request->request->get('new_postalCode');

            // Vérifie si les champs ne sont pas vides
            if (empty($newName) || empty($newPostalCode)) {
                // Affiche un message d'erreur
                $this->addFlash('error', 'Veuillez remplir tous les champs.');
                return $this->redirectToRoute('app_villes'); // Redirige vers la page souhaitée
            }

            // Obtient le gestionnaire d'entité
            $entityManager = $this->doctrine->getManager();

            // Vérifie si la ville existe déjà dans la base de données
            $existingVille = $entityManager->getRepository(Ville::class)->findOneBy(['nom' => $newName]);

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

        // Si la requête n'est pas de type POST, rend la vue avec les données actuelles de la ville
        return $this->render('pages/villes.html.twig', [
            'ville' => $ville,
        ]);
    }


    #[Route('/villes/{id}/supprimer', name: 'ville_supprimer', methods: ['GET'])]
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
