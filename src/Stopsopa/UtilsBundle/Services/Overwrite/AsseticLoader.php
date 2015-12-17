<?php
/**
 * Klasa ta jest kopią 1:1 klasy z bundle AsseticBundle
 * https://github.com/symfony/assetic-bundle/blob/2.3/Routing/AsseticLoader.php#L74
 * zmiana dotyczy tylko
 *
 *         foreach ($this->am->getResources() as $resources) {
 *             if (!$resources instanceof \Traversable) {
 *                 $resources = array($resources);
 *             }
 *             foreach ($resources as $resource) {
 *                 $routes->addResource(new AsseticResource($resource));
 *             }
 *         }
 *
 *         // routes
 *         foreach ($this->am->getNames() as $name) {
 *             if (!$this->am->hasFormula($name)) {
 *                 continue;
 *             }
 *
 *             $asset = $this->am->get($name);
 *             $formula = $this->am->getFormula($name);
 *
 * // simon
 * $asset->setTargetPath($formula[0][1]);
 *
 *             $this->loadRouteForAsset($routes, $asset, $name);
 *
 *             $debug = isset($formula[2]['debug']) ? $formula[2]['debug'] : $this->am->isDebug();
 *             $combine = isset($formula[2]['combine']) ? $formula[2]['combine'] : !$debug;
 *
 *             // add a route for each "leaf" in debug mode
 *             if (!$combine) {
 *                 $i = 0;
 *                 foreach ($asset as $leaf) {
 * // simon
 * $leaf->setTargetPath($leaf->getSourcePath());
 *
 * Stopsopa\UtilsBundle\Services\Overwrite\AsseticLoader
 */








namespace Stopsopa\UtilsBundle\Services\Overwrite;
/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

//namespace Symfony\Bundle\AsseticBundle\Routing;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\LazyAssetManager;
use Symfony\Bundle\AsseticBundle\Config\AsseticResource;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Loads routes for all assets.
 *
 * Assets should only be served through the routing system for ease-of-use
 * during development.
 *
 * For example, add the following to your application's routing_dev.yml:
 *
 *     _assetic:
 *         resource: .
 *         type:     assetic
 *
 * In a production environment you should use the `assetic:dump` command to
 * create static asset files.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 * Symfony\Bundle\AsseticBundle\Routing\AsseticLoader
 */
class AsseticLoader extends Loader
{
    protected $am;
    private $varValues;

    public function __construct(LazyAssetManager $am, array $varValues = array())
    {
        $this->am = $am;
        $this->varValues = $varValues;
    }

    public function load($routingResource, $type = null)
    {
        $routes = new RouteCollection();

        // resources
        foreach ($this->am->getResources() as $resources) {
            if (!$resources instanceof \Traversable) {
                $resources = array($resources);
            }
            foreach ($resources as $resource) {
                $routes->addResource(new AsseticResource($resource));
            }
        }

        // routes
        foreach ($this->am->getNames() as $name) {
            if (!$this->am->hasFormula($name)) {
                continue;
            }

            $asset = $this->am->get($name);
            $formula = $this->am->getFormula($name);
// simon
$asset->setTargetPath($formula[0][1]);

            $this->loadRouteForAsset($routes, $asset, $name);

            $debug = isset($formula[2]['debug']) ? $formula[2]['debug'] : $this->am->isDebug();
            $combine = isset($formula[2]['combine']) ? $formula[2]['combine'] : !$debug;

            // add a route for each "leaf" in debug mode
            if (!$combine) {
                $i = 0;
                foreach ($asset as $leaf) {
// simon
$leaf->setTargetPath($leaf->getSourcePath());
                    $this->loadRouteForAsset($routes, $leaf, $name, $i++);
                }
            }
        }

        return $routes;
    }

    /**
     * Loads a route to serve an supplied asset.
     *
     * The fake front controller that {@link UseControllerWorker} adds to the
     * target URL will be removed before set as a route pattern.
     *
     * @param RouteCollection $routes The route collection
     * @param AssetInterface  $asset  The asset
     * @param string          $name   The name to use
     * @param integer         $pos    The leaf index
     */
    private function loadRouteForAsset(RouteCollection $routes, AssetInterface $asset, $name, $pos = null)
    {
        $defaults = array(
            '_controller' => 'assetic.controller:render',
            'name'        => $name,
            'pos'         => $pos,
        );
        $requirements = array();

        // remove the fake front controller
        $pattern = str_replace('_controller/', '', $asset->getTargetPath());

        if ($format = pathinfo($pattern, PATHINFO_EXTENSION)) {
            $defaults['_format'] = $format;
        }

        $route = '_assetic_'.$name;
        if (null !== $pos) {
            $route .= '_'.$pos;
        }

        foreach ($asset->getVars() as $var) {
            if (empty($this->varValues[$var])) {
                throw new \UnexpectedValueException(sprintf('The possible values for the asset variable "%s" are not known', $var));
            }

            $values = array();

            foreach ($this->varValues[$var] as $value) {
                $values[] = preg_quote($value, '#');
            }

            $requirements[$var] = implode('|', $values);
        }

        $routes->add($route, new Route($pattern, $defaults, $requirements));
    }

    public function supports($resource, $type = null)
    {
        return 'assetic' == $type;
    }
}
