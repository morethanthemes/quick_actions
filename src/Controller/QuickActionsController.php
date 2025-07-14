<?php

namespace Drupal\quick_actions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;

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

}
