<?php

namespace Stopsopa\UtilsBundle\Lib\Depends;

use Closure;
use Symfony\Component\Finder\Adapter\AdapterInterface;
use Symfony\Component\Finder\Finder as Base;
use ArrayIterator;

/**
 * http://symfony.com/doc/current/components/finder.html
 * http://fabien.potencier.org/article/43/find-your-files.
 */
class Finder extends Base
{
    protected $max;
    protected $order = true; // kolejność sortowania // true - asc, false - desc
    /**
     * @return Finder
     */
    public function setMaxResults($max = null)
    {
        $this->max = $max;

        return $this;
    }
    /**
     * Metoda wymusza odwracanie kolejności.
     *
     * @param bool|string $order - true = 'asc', false = 'desc'
     *
     * @return Finder
     */
    public function setSortOrder($order = true)
    {
        $this->order = (bool) $order;

        // jeśli podano jako string
        $order = trim(strtolower($order));
        ($order === 'asc') and ($this->order = true);
        ($order === 'desc') and ($this->order = false);

        return $this;
    }

    public function getIterator()
    {
        $iterator = parent::getIterator();

        if ($iterator instanceof Base) {
            $iterator = $iterator->getIterator();
        }

        $list = $iterator->getArrayCopy();

        if ($this->max) {
            $list = array_slice($list, 0, $this->max);
        }

        if (!$this->order) {
            $list = array_reverse($list);
        }

//        $finder = new ArrayIterator(array_reverse($finder->getIterator()->getArrayCopy())); // dowracam kolejność
        return new ArrayIterator($list);
    }
    /**
     * @return Finder
     */
    public function date($date)
    {
        return parent::date($date);
    }

    /**
     * @return Finder
     */
    public function name($pattern)
    {
        return parent::name($pattern);
    }
    /**
     * @return Finder
     */
    public function size($size)
    {
        return parent::size($size);
    }
    /**
     * @return Finder
     */
    public function ignoreUnreadableDirs($ignore = true)
    {
        return parent::ignoreUnreadableDirs($ignore);
    }
    /**
     * @return Finder
     */
    public function exclude($dirs)
    {
        return parent::exclude($dirs);
    }
    /**
     * @return Finder
     */
    public function sort(Closure $closure)
    {
        return parent::sort($closure);
    }

    /**
     * @return Finder
     */
    public function sortByChangedTime()
    {
        return parent::sortByChangedTime();
    }
    /**
     * sortowanie od najstarszych do coraz nowszych.
     *
     * @return Finder
     */
    public function sortByModifiedTime()
    {
        return parent::sortByModifiedTime();
    }
    /**
     * @return Finder
     */
    public function sortByName()
    {
        return parent::sortByName();
    }
    /**
     * @return Finder
     */
    public function sortByType()
    {
        return parent::sortByType();
    }
    /**
     * @return Finder
     */
    public function sortByAccessedTime()
    {
        return parent::sortByAccessedTime();
    }
    /**
     * @return Finder
     */
    public function notName($pattern)
    {
        return parent::notName($pattern);
    }
    /**
     * @return Finder
     */
    public function notContains($pattern)
    {
        return parent::notContains($pattern);
    }
    /**
     * @return Finder
     */
    public function notPath($pattern)
    {
        return parent::notPath($pattern);
    }
    /**
     * @return Finder
     */
    public function depth($level)
    {
        return parent::depth($level);
    }
    /**
     * @return Finder
     */
    public function filter(Closure $closure)
    {
        return parent::filter($closure);
    }
    public static function create()
    {
        return parent::create();
    }

    /**
     * @return Finder
     */
    public function followLinks()
    {
        return parent::followLinks();
    }
    /**
     * @return Finder
     */
    public function ignoreDotFiles($ignoreDotFiles)
    {
        return parent::ignoreDotFiles($ignoreDotFiles);
    }
    /**
     * @return Finder
     */
    public function path($pattern)
    {
        return parent::path($pattern);
    }
    /**
     * @return Finder
     */
    public function contains($pattern)
    {
        return parent::contains($pattern);
    }
    /**
     * @return Finder
     */
    public function append($iterator)
    {
        return parent::append($iterator);
    }
    /**
     * @return Finder
     */
    public function addAdapter(AdapterInterface $adapter, $priority = 0)
    {
        return parent::addAdapter($adapter, $priority);
    }
    /**
     * @return Finder
     */
    public function removeAdapters()
    {
        return parent::removeAdapters();
    }
    /**
     * @return Finder
     */
    public function setAdapter($name)
    {
        return parent::setAdapter($name);
    }
    /**
     * @return Finder
     */
    public function useBestAdapter()
    {
        return parent::useBestAdapter();
    }
    /**
     * @return Finder
     */
    public function ignoreVCS($ignoreVCS)
    {
        return parent::ignoreVCS($ignoreVCS);
    }
    /**
     * @return Finder
     */
    public function directories()
    {
        return parent::directories();
    }
    /**
     * @return Finder
     */
    public function files()
    {
        return parent::files();
    }
    /**
     * @return Finder
     */
    public function in($dirs)
    {
        return parent::in($dirs);
    }
}
