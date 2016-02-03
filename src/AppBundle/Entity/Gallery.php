<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Bloq\Common\EditorBundle\Entity\EditorialContentInterface;
use Bloq\Common\FrontBundle\Entity\Gallery as BloqFrontGallery;

/**
 * @ORM\Entity
 */
class Gallery extends BloqFrontGallery implements EditorialContentInterface
{
}
