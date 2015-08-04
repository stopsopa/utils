<?php

namespace Stopsopa\UtilsBundle\Lib;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Twig_Error_Loader;
use Symfony\Component\Templating\TemplateReference;

/**
 * Ta klasa to zwykła kopia z vendorsów z podmienioną klasą macierzystą/rozszerzaną
 * ... jakby się coś w vendorsowej klasie zmieniło z nowymi wersjami to trzeba ją na nowo skopiować
 * Stopsopa\UtilsBundle\Lib\TwigFilesystemLoader.
 */
class TwigFilesystemLoader_1 extends TwigLoaderFilesystemExtend
{
    protected $locator;
    protected $parser;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface        $locator A FileLocatorInterface instance
     * @param TemplateNameParserInterface $parser  A TemplateNameParserInterface instance
     */
    public function __construct(FileLocatorInterface $locator, TemplateNameParserInterface $parser)
    {
        parent::__construct(array());
        $this->locator = $locator;
        $this->parser = $parser;
        $this->cache = array();
        $container = AbstractApp::getCont(); // no niestety pojawiły się problemy z nadpisywaniem tego serwisu, później może zrobię to tak jak Bóg nakazał...
        // ale to nie jest pilne bo logicznie wszystko tutaj jest poprawne ale nie wedłóg konwencji symfonowej
        $this->container = $container;
        $this->dev = $container->getParameter('kernel.environment') == 'dev';

        $this->symlinkloader = AbstractApp::getStpaConfig('yui.symlinkloader', false);
        $this->loader = is_string($this->symlinkloader) ? trim($this->symlinkloader) : '';
    }

    /**
     * Returns the path to the template file.
     *
     * The file locator is used to locate the template when the naming convention
     * is the symfony one (i.e. the name can be parsed).
     * Otherwise the template is located using the locator from the twig library.
     *
     * @param string|TemplateReferenceInterface $template The template
     *
     * @return string The path to the template file
     *
     * @throws \Twig_Error_Loader if the template could not be found
     */
    protected function findTemplate($template)
    {
        $logicalName = (string) $template;

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        }

        $file = null;
        $previous = null;
        try {
            $template = $this->parser->parse($template);
            try {
                $file = $this->locator->locate($template);
            } catch (InvalidArgumentException $e) {
                $previous = $e;
            }
        } catch (Exception $e) {
            try {
                $file = parent::findTemplate($template);
            } catch (Twig_Error_Loader $e) {
                $previous = $e;
            }
        }

        // simon dodałem to:  vvv
        if ((false === $file || null === $file) && $template) {
            /* @var $template Symfony\Component\Templating\TemplateReference */
//            niechginie($template->getPath());
            if ($template instanceof TemplateReference) {
                $file = $this->exists($template->getPath());
            }
        }
        // simon dodałem to:  ^^^


        if (false === $file || null === $file) {
            throw new Twig_Error_Loader(sprintf('Unable to find template "%s".', $logicalName), -1, null, $previous);
        }

        return $this->cache[$logicalName] = $file;
    }
}
