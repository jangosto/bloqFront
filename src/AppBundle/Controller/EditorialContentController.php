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
     * @Route("/{section}/{title}-{date}.html", requirements={"title" = ".+", "date" = "[1-3][0-9][0-9][0-9][0-1][0-9][0-3][0-9][0-2][0-9][0-5][0-9][0-5][0-9]"})
     * @Route("/{section}/{subsection}/{title}-{date}.html", requirements={"title" = ".+", "date" = "[1-3][0-9][0-9][0-9][0-1][0-9][0-3][0-9][0-2][0-9][0-5][0-9][0-5][0-9]"})
     * @Route("/{section}/{subsection}/{subsubsection}/{title}-{date}.html", requirements={"title" = ".+", "date" = "[1-3][0-9][0-9][0-9][0-1][0-9][0-3][0-9][0-2][0-9][0-5][0-9][0-5][0-9]"})
     */
    public function editorialContentShow(Request $request, $section, $subsection = null, $subsubsection = null, $title, $date)
    {
        $currentPath = $this->container->get('request')->getPathInfo();
        $urlManager = $this->container->get('editor.url.manager');
        $url = $urlManager->getByUrl($currentPath);

        if ($url === null) {
            throw $this->createNotFoundException('Content Not Found');
        }

        $editorialContentClass = $this->container->getParameter("editorial_contents.".$url->getContentType().".model_class");
        $editorialContentManager = $this->container->get('editor.'.$url->getContentType().'.manager');
        $editorialContent = $editorialContentManager->getById($url->getContentId(), true);

        if ($url->getCanonical() === true) {
            $canonicalUrl = $url;
        } else {
            foreach ($editorialContent->getUrls() as $ecUrl) {
                if ($ecUrl->getCanonical() === true) {
                    $canonicalUrl = $ecUrl;
                }
            }
        }

        if ($url->getEnabled() !== true) {
            return $this->redirect($this->container->get('request')->getBaseUrl().$canonicalUrl->getUrl(), 301);
        }

        if (count($editorialContent->getTags()) > 0) {
            $interestingContents = $editorialContentManager->getBySameTags($editorialContent, false, 3);
            foreach ($interestingContents as $interestingContent) {
                $interestingContent->setUrls($urlManager->getByContentId($interestingContent->getId()));
            }
        } else {
            $interestingContent = array();
        }

        $userManager = $this->container->get('fos_user.user_manager');
        $authors = $userManager->getByIds($editorialContent->getAuthors());
        
        return $this->render('editorial_content/'.$editorialContent->getType().'.html.twig', array(
            'user' => $this->getUser(),
            'authors' => $authors,
            'content' => $editorialContent,
            'interestingContents' => $interestingContents,
            'coverContents' => array()
        ));
    }
}
