<?php

namespace Drupal\quick_actions\Routing;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\Core\Config\ConfigFactoryInterface;

class DynamicRouteSubscriber implements EventSubscriberInterface {

  protected ConfigFactoryInterface $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  public static function getSubscribedEvents(): array {
    return [
      RoutingEvents::DYNAMIC => 'onDynamicRoutes',
    ];
  }

  public function onDynamicRoutes(RouteBuildEvent $event): void {
    $collection = $event->getRouteCollection();
    $paths = $this->configFactory->get('quick_actions.settings')->get('default_paths') ?? [];

    foreach ($paths as $path) {
      if (preg_match('#^/?([^/]+)/#', $path, $matches)) {
        $scope = $matches[1];

        // Skip if already added
        if ($collection->get("quick_actions.dynamic.$scope")) {
          continue;
        }

        $route = new Route(
          "/$scope",
          [
            '_controller' => '\Drupal\quick_actions\Controller\DynamicViewController::renderForScope',
            'scope' => $scope,
            '_title' => ucfirst($scope),
          ],
          [
            '_permission' => 'access content',
          ]
        );

        $collection->add("quick_actions.dynamic.$scope", $route);
      }
    }
  }
}
