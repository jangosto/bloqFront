<?php

namespace AppBundle\Twig;

class AppEditorialContentExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('canonicalUrl', array($this, 'canonicalUrlFilter')),
            new \Twig_SimpleFilter('analyticsSectionString', array($this, 'analyticsSectionStringFilter')),
        );
    }

    public function analyticsSectionStringFilter($section)
    {
        $tempSection = null;

        if (($tempSection = $section->getParent()) != null) {
            $result = $this->analyticsSectionStringFilter($tempSection)." - ".$section->getName();
        } else {
            $result = $section->getName();
        }

        return $result;
    }

    public function canonicalUrlFilter($editorialContent)
    {
        foreach ($editorialContent->getUrls() as $url) {
            if ($url->getCanonical() === true) {
                return $url->getUrl();
            }
        }

        return null;
    }

    public function getName()
    {
        return 'app_editorial_content_extension';
    }
}
