<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Bloq\Common\EditorBundle\Entity\EditorialContentInterface;
use Bloq\Common\FrontBundle\Entity\Article as BloqFrontArticle;

/**
 * @ORM\Entity
 */
class Article extends BloqFrontArticle implements EditorialContentInterface
{
}
