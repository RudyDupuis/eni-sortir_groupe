<?php

namespace App\Controller;

use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
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
}
