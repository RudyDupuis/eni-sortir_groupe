<?php

namespace App\Data;

use App\Entity\Campus;

class SearchData
{
     /**
     * @var string
     */
    public $q = '';

    /**
     * @var Campus|null
     */
    public $campus;

    /**
     * @var \DateTime|null
     */
    public $dateDebut;

    /**
     * @var \DateTime|null
     */
    public $dateFin;

    /**
     * @var bool
     */
    public $utilite1 = false;

    /**
     * @var bool
     */
    public $utilite2 = false;

    /**
     * @var bool
     */
    public $utilite3 = false;

    /**
     * @var bool
     */
    public $utilite4 = false;

    public function __construct()
    {
        // Initialiser la date de début à aujourd'hui
        $this->dateDebut = new \DateTime();

        // Initialiser la date de fin à une semaine à partir de la date de début
        $this->dateFin = clone $this->dateDebut;
        $this->dateFin->modify('+1 week');
    }

}