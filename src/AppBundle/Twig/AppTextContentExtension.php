<?php
namespace AppBundle\Twig;

class AppTextContentExtension extends \Twig_Extension
{
    const advertising_text_position = 3;
    const first_elements_text_position = 1;
    const summaries_text_position = 1;

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('formatedTextContent', array($this, 'formatContentText'))
        );
    }

    public function formatContentText($content, $advertising = null)
    {
        $elements = array();
        $html = "";
        $textArray = $this->crumbleText($content->getText());
        $elementsCounter = 0;

        foreach ($textArray as $key => $paragraph) {
            if ($key == self::advertising_text_position && $advertising != null && $advertising->count() > 0) {
                $html .= '<div class="robapaginas en-texto">'.$advertising->__toString().'</div>';
            }

            foreach ($content->getMultimedias() as $multimedia) {
                if ($multimedia->getPosition() != null && $multimedia->getPosition() == $key && $multimedia->getPosition() > 0) {
                    if ($multimedia->getType() == "video") {
                        $html .= $multimedia->getVideoHtml();
                    } else {
                        $html .= '<figure class="foto" itemprop="image" itemscope itemtype="http://schema.org/ImageObject"><img src="'.$this->getOptimizedImage($multimedia->getImageWebPath(), 'article_intext').'" alt="'.$multimedia->getAlt().'" itemprop="url">';
                        if (($multimedia->getTitle() != null && strlen($multimedia->getTitle()) > 0) || ($multimedia->getAuthor() != null && strlen($multimedia->getAuthor()) > 0)) {
                            $html .= '<figcaption itemprop="name">';
                            if ($multimedia->getTitle() != null && strlen($multimedia->getTitle()) > 0) {
                                $html .= '<strong>'.$multimedia->getTitle().'</strong>&nbsp;';
                            }
                            if ($multimedia->getDescription() != null && strlen($multimedia->getDescription()) > 0) {
                                $html .= $multimedia->getDescription().'&nbsp;';
                            }
                            if ($multimedia->getAuthor() != null && strlen($multimedia->getAuthor()) > 0) {
                                $html .= '<span class="firma">'.$multimedia->getAuthor().'</span>';
                            }
                            $html .= '</figcaption>';
                        }
                        $html .= '</figure>';
                    }
                }
            }

            if ($key == self::summaries_text_position && isset($content->getSummaries()[0]) && strlen($content->getSummaries()[0]) > 0) {
                $html .= '<ul class="sumarios">';
                foreach ($content->getSummaries() as $summary) {
                    $html .= '<li>'.$summary.'</li>';
                }
                $html .= '</ul>';
            }

            $html .= $paragraph."</p>";
        }

        return $html;
    }

    private function crumbleText($text)
    {
        $stringArray = explode("</p>", $text);
        if (end($stringArray) == "") {
            array_pop($stringArray);
        }

        return $stringArray;
    }

    private function getIntervalForElements($textArray, $content, &$elements)
    {
        $totalElements = 0;
        
        foreach ($content->getMultimedias() as $multimedia) {
            if ($multimedia->getPosition() != 'primary') {
                if ($multimedia->getType() == "video" || $multimedia->getType() == "audio") {
                    array_unshift($elements, $multimedia);
                } else {
                    $elements[] = $multimedia;
                }
                $totalElements++;
            }
        }
        
        if ($totalElements > 0) {
            $interval = (int) floor(count($textArray)/$totalElements);
        } else {
            $interval = null;
        }

        return $interval;
    }


    private function getOptimizedImage($path, $filter)
    {
        $info = pathinfo($path);

        return $info['dirname'].'/'.$info['filename'].'_'.$filter.'.'.$info['extension'];
    }

    private function str_replace_last($search, $replace, $str)
    {
        if(($pos = strrpos($str, $search)) !== false) {
            $search_length  = strlen($search);
            $str = substr_replace($str, $replace, $pos, $search_length);
        }

        return $str;
    }

    public function getName()
    {
        return 'app_text_content_extension';
    }
}
