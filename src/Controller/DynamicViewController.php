<?php

namespace Drupal\quick_actions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\quick_actions\Service\ViewPathResolver;

class DynamicViewController extends ControllerBase {

  protected ViewPathResolver $resolver;

  public function __construct(ViewPathResolver $resolver) {
    $this->resolver = $resolver;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('quick_actions.view_path_resolver')
    );
  }

  public function renderForScope(string $scope) {
    $paths = $this->config('quick_actions.settings')->get('default_paths') ?? [];
    \Drupal::logger('quick_actions')->notice('Dynamic route called for scope: @scope', ['@scope' => $scope]);

    // return [
    //     '#markup' => "<h1>Dynamic route: $scope</h1>",
    // ];

    foreach ($paths as $path) {
        if (str_starts_with($path, "/$scope/")) {
        $result = $this->resolver->resolve($path);
        if ($result) {
            $view = \Drupal\views\Views::getView($result['view_id']);
            if ($view && $view->access($result['display_id'])) {
            $view->setDisplay($result['display_id']);
            return $view->executeDisplay($result['display_id']);
            }
        }
        }
    }

    throw new NotFoundHttpException("No view configured for /$scope.");
  }
}
