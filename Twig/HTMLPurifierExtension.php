<?php

namespace Exercise\HTMLPurifierBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class HTMLPurifierExtension extends \Twig_Extension
{
    private $container;

    private $purifiers = array();

    /**
     * Constructor.
     *
     * @param \HTMLPurifier $purifier
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('purify', array($this, 'purify'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Filter the input through an HTMLPurifier service.
     *
     * @param string $string
     * @param string $profile
     * @return string
     */
    public function purify($string, $profile = 'default')
    {
        return $this->getHTMLPurifierForProfile($profile)->purify($string);
    }

    /**
     * Get the HTMLPurifier service corresponding to the given profile.
     *
     * @param string $profile
     * @return \HTMLPurifier
     * @throws \RuntimeException
     */
    private function getHTMLPurifierForProfile($profile)
    {
        if (!isset($this->purifiers[$profile])) {
            $purifier = $this->container->get('exercise_html_purifier.' . $profile);

            if (!$purifier instanceof \HTMLPurifier) {
                throw new \RuntimeException(sprintf('Service "exercise_html_purifier.%s" is not an HTMLPurifier instance.', $profile));
            }

            $this->purifiers[$profile] = $purifier;
        }

        return $this->purifiers[$profile];
    }

    /**
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'html_purifier';
    }
}
