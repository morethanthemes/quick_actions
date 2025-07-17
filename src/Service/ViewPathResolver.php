<?php

namespace Drupal\quick_actions\Service;

use Drupal\views\Views;

/**
 * Service to resolve a view and display from a given Drupal path.
 * 
 * Example:
 *   /events/index → ['view_id' => 'events', 'display_id' => 'page_index']
 *
 * Example usage:
 *
 * @code
 * // Get the resolver service.
 * $resolver = \Drupal::service('quick_actions.view_path_resolver');
 *
 * // Resolve view from a known path.
 * $result = $resolver->resolve('/events/cards');
 *
 * if ($result) {
 *   $scope = $result['scope'];         // e.g. 'events'
 *   $view_id = $result['view_id'];     // e.g. 'events'
 *   $display_id = $result['display_id']; // e.g. 'page_cards'
 * }
 * @endcode
 */
class ViewPathResolver {

  /**
   * Resolves a Drupal view + display from a given URL path.
   *
   * @param string $path
   *   The public path, e.g., '/events/cards'.
   *
   * @return array|null
   *   Associative array with 'scope', 'view_id', and 'display_id', or NULL if not found.
   */
  public function resolve(string $path): ?array {
    $path = ltrim($path, '/');

    foreach (Views::getAllViews() as $view) {
      foreach ($view->get('display') as $display_id => $display) {
        $display_path = $display['display_options']['path'] ?? NULL;

        if ($display_path && trim($display_path, '/') === $path) {
          $segments = explode('/', $path);
          return [
            'scope' => $segments[0] ?? $view->id(), // first segment is 'events'
            'view_id' => $view->id(),
            'display_id' => $display_id,
          ];
        }
      }
    }

    return NULL;
  }

}


