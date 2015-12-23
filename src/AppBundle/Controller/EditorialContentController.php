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
     * @Route("/", name="test_list_editorial_contents")
     */
    public function publishedContentsList()
    {
        $urlManager = $this->container->get('editor.url.manager');
        $urls = $urlManager->getAllEnabled();

        $contents = array();
        foreach ($urls as $url) {
            if ($url->getCanonical() === true) {
                $editorialContentManager = $this->container->get('editor.'.$url->getContentType().'.manager');
                $editorialContent = $editorialContentManager->getById($url->getContentId(), true);
                if ($editorialContent->getStatus() == 'published') {
                    $element['url'] = $url->getUrl();
                    $element['type'] = $editorialContent->getType();
                    $element['title'] = $editorialContent->getTitle();
                    $contents[$editorialContent->getType()][] = $element;
                }
            }
        }

        return $this->render('contentList.html.twig', array(
            'user' => $this->getUser(),
            'contents' => $contents
        ));
    }

    /**
     * @Route("/index.html", name="front_home")
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
}
