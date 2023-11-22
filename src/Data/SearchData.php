<?php

namespace App\Data;

use App\Entity\Campus;

class SearchData
{
     /**
     * @var string
     */
    public $rechercher = '';

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

}