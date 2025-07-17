<?php

namespace Drupal\quick_actions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\Core\Config\ConfigFactoryInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for Quick Actions.
 */
class QuickActionsController extends ControllerBase {



  /**
   * Sets the given node as the front page.
   *
   * @param int $node
   *   The node ID.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function setHomepage($node) {
    $node = Node::load($node);

    if ($node) {
      // Actually set the front page path.
      $path = '/node/' . $node->id();
      \Drupal::configFactory()->getEditable('system.site')
        ->set('page.front', $path)
        ->save();

      $this->messenger()->addStatus($this->t('The front page has been set to %title.', ['%title' => $node->label()]));
    }
    else {
      $this->messenger()->addError($this->t('The requested node could not be found.'));
    }

    return $this->redirect('entity.node.edit_form', ['node' => $node->id()]);
  }


  public function setDefaultPath(Request $request) {
  $path = $request->request->get('path');

  if (!$path || !str_starts_with($path, '/')) {
    return new JsonResponse(['status' => 'error', 'message' => 'Invalid or missing path.'], 400);
  }

  // Determine the scope — it's the second segment in the path (e.g. 'events' from '/events/index').
  $segments = explode('/', trim($path, '/'));
  $scope = $segments[0] ?? null;

  if (!$scope) {
    return new JsonResponse(['status' => 'error', 'message' => 'Could not determine scope from path.'], 400);
  }

  $config = \Drupal::configFactory()->getEditable('quick_actions.settings');
  $paths = $config->get('default_paths') ?? [];

  // Remove all existing paths that belong to the same scope.
  $filteredPaths = array_filter($paths, function ($existingPath) use ($scope) {
    return !str_starts_with(trim($existingPath), "/$scope/");
  });

  
  // Add the new path for this scope.
  $filteredPaths[] = $path;

  $config->set('default_paths', array_values($filteredPaths))->save();
  // Rebuild routes so the change takes effect without needing drush cr
  \Drupal::service('router.builder')->rebuild();
  
  return new JsonResponse(['status' => 'success', 'message' => "✅ Path '$path' set as default."]);
}


}
