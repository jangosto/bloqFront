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
     * @Route("/", name="front_home")
     */
    public function homeAction()
    {
        $categoryManager = $this->container->get('editor.category.manager');

        $outstandingCategories = $categoryManager->getOutstandings();

        $counters = new \Bloq\Common\ModulesBundle\Monitors\Counters($outstandingCategories);
        $counters->getOutstandingSections()->first();

        return $this->render('cover/home.html.twig', array(
            'user' => $this->getUser(),
            'counters' => $counters
        ));
    }

    /**
     * @Route("/{section}/{title}-{date}.html", name="front_editorial_content_show_section", requirements={"title" = ".+", "date" = "[1-3][0-9][0-9][0-9][0-1][0-9][0-3][0-9][0-2][0-9][0-5][0-9][0-5][0-9]"})
     * @Route("/{section}/{subsection}/{title}-{date}.html", name="front_editorial_content_show_subsection", requirements={"title" = ".+", "date" = "[1-3][0-9][0-9][0-9][0-1][0-9][0-3][0-9][0-2][0-9][0-5][0-9][0-5][0-9]"})
     * @Route("/{section}/{subsection}/{subsubsection}/{title}-{date}.html", name="front_editorial_content_show_subsubsection", requirements={"title" = ".+", "date" = "[1-3][0-9][0-9][0-9][0-1][0-9][0-3][0-9][0-2][0-9][0-5][0-9][0-5][0-9]"})
     */
    public function editorialContentShowAction(Request $request, $section, $subsection = null, $subsubsection = null, $title, $date)
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

        if ($editorialContent->getStatus() !== "published") {
            throw $this->createNotFoundException('Content Not Found');
        }

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

        $userManager = $this->container->get('fos_user.user_manager');
        $authors = $userManager->getByIds($editorialContent->getAuthors());

        $counters = new \Bloq\Common\ModulesBundle\Monitors\Counters();
        $counters->getUsedContents()->add($editorialContent->getId());
        
        return $this->render('editorial_content/'.$editorialContent->getType().'.html.twig', array(
            'user' => $this->getUser(),
            'authors' => $authors,
            'content' => $editorialContent,
            'counters' => $counters
        ));
    }

    /**
     * @Route("/{section}/", name="front_cover_by_section")
     * @Route("/{section}/{subsection}/", name="front_cover_by_subsection")
     * @Route("/{section}/{subsection}/{subsubsection}/", name="front_cover_by_subsubsection")
     */
    public function coverAction($section, $subsection = null, $subsubsection = null)
    {
        $categoryManager = $this->container->get('editor.category.manager');

        $outstandingCategories = $categoryManager->getOutstandings();

        $slug = "";
        if ($subsubsection != null && strlen($subsubsection) > 0) {
            $slug[0] = $subsubsection;
            $slug[1] = $subsection;
            $slug[2] = $section;
        } elseif ($subsection != null && strlen($subsection) > 0) {
            $slug[0] = $subsection;
            $slug[1] = $section;
        } elseif ($section != null && strlen($section) > 0) {
            $slug[0] = $section;
        }

        $section = $categoryManager->getBySlug($slug[0]);
        $i = 0;
        $tempSection = $section;
        while ($tempSection != null && $categoryManager->getById($tempSection->getId())->getSlug() == $slug[$i]) {
            $tempSection = $categoryManager->getById($tempSection->getParentId());
            $i++;
        }
        if ($i < count($slug)) {
            $tagManager = $this->container->get('editor.tag.manager');
            $section = $tagManager->getBySlug($slug[0]);
            $i = 0;
            $tempSection = $section;
            while ($tempSection != null && $tagManager->getById($tempSection->getId())->getSlug() == $slug[$i]) {
                $tempSection = $tagManager->getById($tempSection->getParentId());
                $i++;
            }
            if ($i < count($slug)) {
                throw $this->createNotFoundException('Content Not Found');
            } else {
                $templatePrefix = "tag_";
            }
        } else {
            $templatePrefix = "category_";
        }

        $counters = new \Bloq\Common\ModulesBundle\Monitors\Counters($outstandingCategories);

        return $this->render('cover/'.$templatePrefix.'cover.html.twig', array(
            'user' => $this->getUser(),
            'counters' => $counters,
            'section' => $section
        ));
    }
}
