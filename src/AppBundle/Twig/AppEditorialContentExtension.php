<?php

namespace AppBundle\Twig;

class AppEditorialContentExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('canonicalUrl', array($this, 'canonicalUrlFilter')),
        );
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
