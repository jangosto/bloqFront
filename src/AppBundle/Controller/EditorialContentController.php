<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Bloq\Common\EntitiesBundle\Entity\User as AdminUser;
use Bloq\Common\MultimediaBundle\Entity\Multimedia as MultimediaEntity;

use AppBundle\Form\Type\UserCreationFormType as AdminUserCreationFormType;


class EditorialContentController extends Controller
{
    /**
     * @Route("/{section}/{editorialContentType}/{id}.html")
     * @Route("/{section}/{subsection}/{editorialContentType}/{id}.html")
     */
    public function editorialContentShow(Request $request, $section, $subsection = null, $editorialContentType, $id)
    {
        $editorialContentClass = $this->container->getParameter("editorial_contents.".$editorialContentType.".model_class");
        $editorialContentManager = $this->container->get('editor.'.$editorialContentType.'.manager');
        $editorialContent = $editorialContentManager->getById($id, true);

        $interestingContents = $editorialContentManager->getBySameTags($editorialContent, false, 3);

        $userManager = $this->container->get('fos_user.user_manager');
        $authors = $userManager->getByIds($editorialContent->getAuthors());
        
        return $this->render('editorial_content/'.$editorialContentType.'.html.twig', array(
            'user' => $this->getUser(),
            'authors' => $authors,
            'content' => $editorialContent,
            'interestingContents' => $interestingContents,
            'coverContents' => array()
        ));
    }
}
