<?php

namespace PieCrust\Plugins\Twig;

use Twig\Extension\AbstractExtension;

class GeshiExtension extends AbstractExtension
{
    public function __construct()
    {
        class_exists('\\Geshi');
        //parent::__construct(); // it is an abstract class
    }

    public function getName()
    {
        return "geshi";
    }

    public function getTokenParsers()
    {
        return array(
            new GeshiTokenParser(),
        );
    }

    public function getFunctions()
    {
        return array(
            'geshi_css' => new \Twig_Function_Method($this, 'getGeshiCss')
        );
    }

    public function getGeshiCss($value)
    {
        $geshi = new \Geshi('', $value);
        return $geshi->get_stylesheet(false);
    }
}
