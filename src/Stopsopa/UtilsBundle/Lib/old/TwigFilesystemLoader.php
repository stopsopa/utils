<?php

namespace Stopsopa\UtilsBundle\Lib;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Twig_Error_Loader;

/**
 * Ta klasa to zwykła kopia z vendorsów z podmienioną klasą macierzystą/rozszerzaną
 * ... jakby się coś w vendorsowej klasie zmieniło z nowymi wersjami to trzeba ją na nowo skopiować
 * Stopsopa\UtilsBundle\Lib\TwigFilesystemLoader.
 */
class TwigFilesystemLoader extends TwigLoaderFilesystemExtend
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

    protected function findTemplate($name)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $this->validateName($name);

        list($namespace, $shortname) = $this->parseName($name);

        if (!isset($this->paths[$namespace])) {
            throw new Twig_Error_Loader(sprintf('There are no registered paths for namespace "%s".', $namespace));
        }

        foreach ($this->paths[$namespace] as $path) {
            if (is_file($path.'/'.$shortname)) {
                if (false !== $realpath = realpath($path.'/'.$shortname)) {
                    return $this->cache[$name] = $realpath;
                }

                return $this->cache[$name] = $path.'/'.$shortname;
            }
        }

        throw new Twig_Error_Loader(sprintf('Unable to find template "%s" (looked into: %s).', $name, implode(', ', $this->paths[$namespace])));
    }

    protected function parseName($name, $default = self::MAIN_NAMESPACE)
    {
        if (isset($name[0]) && '@' == $name[0]) {
            if (false === $pos = strpos($name, '/')) {
                throw new Twig_Error_Loader(sprintf('Malformed namespaced template name "%s" (expecting "@namespace/template_name").', $name));
            }

            $namespace = substr($name, 1, $pos - 1);
            $shortname = substr($name, $pos + 1);

            return array($namespace, $shortname);
        }

        return array($default, $name);
    }

    protected function normalizeName($name)
    {
        return preg_replace('#/{2,}#', '/', strtr((string) $name, '\\', '/'));
    }

    protected function validateName($name)
    {
        if (false !== strpos($name, "\0")) {
            throw new Twig_Error_Loader('A template name cannot contain NUL bytes.');
        }

        $name = ltrim($name, '/');
        $parts = explode('/', $name);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new Twig_Error_Loader(sprintf('Looks like you try to load a template outside configured directories (%s).', $name));
            }
        }
    }
}
