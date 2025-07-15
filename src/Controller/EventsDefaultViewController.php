<?php

namespace Drupal\quick_actions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Views;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventsDefaultViewController extends ControllerBase {

  public function render() {

    \Drupal::logger('quick_actions')->notice('Events route called');



    $config = $this->config('quick_actions.settings');
    $view_id = $config->get('events_default.view');
    $display_id = $config->get('events_default.display');

    \Drupal::logger('quick_actions')->notice('Loaded config: view = @view, display = @display', [
      '@view' => $view_id,
      '@display' => $display_id,
    ]);

    if (!$view_id || !$display_id) {
      throw new NotFoundHttpException('Default Events view not configured.');
    }

    $view = Views::getView($view_id);

    if (!$view) {
      \Drupal::logger('quick_actions')->error('View @view not found', ['@view' => $view_id]);
      throw new NotFoundHttpException();
    }

    if (!$view->access($display_id)) {
      \Drupal::logger('quick_actions')->error('No access to display @display in view @view', [
        '@display' => $display_id,
        '@view' => $view_id,
      ]);
      throw new NotFoundHttpException();
    }

    $view->setDisplay($display_id);
    return $view->executeDisplay($display_id);
  }
}