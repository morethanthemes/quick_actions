<?php

namespace Drupal\quick_actions\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class DynamicRouteProvider implements RouteProviderInterface {

  protected ConfigFactoryInterface $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteByName($name) {
    return $this->getRoutes()->get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getRoutesByNames($names) {
    $collection = new RouteCollection();
    $all = $this->getRoutes();
    foreach ($names as $name) {
      if ($route = $all->get($name)) {
        $collection->add($name, $route);
      }
    }
    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoutes($route_name = null) {
    $routes = new RouteCollection();
    $paths = $this->configFactory->get('quick_actions.settings')->get('default_paths') ?? [];

    \Drupal::logger('quick_actions')->notice('DynamicRouteProvider loaded @count routes', ['@count' => count($paths)]);


    foreach ($paths as $path) {
        \Drupal::logger('quick_actions')->notice('Registering dynamic route: @scope', ['@scope' => $scope]);

      if (preg_match('#^/([^/]+)/#', $path, $matches)) {
        $scope = $matches[1];
        $route_name = "quick_actions.dynamic.$scope";
        if (!$routes->get($route_name)) {
          $route = new Route(
            "/$scope",
            [
              '_controller' => '\Drupal\quick_actions\Controller\DynamicViewController::renderForScope',
              '_title' => ucfirst($scope),
              'scope' => $scope,
            ],
            [
              '_permission' => 'access content',
            ]
          );
          $routes->add($route_name, $route);
        }
      }
    }

    return $routes;
  }
}
