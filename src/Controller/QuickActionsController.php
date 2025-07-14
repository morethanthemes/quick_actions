<?php

namespace Drupal\quick_actions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\Entity\Node;

class QuickActionsController extends ControllerBase {

  public function setHomepage($node) {
    $node = Node::load($node);
    if ($node) {
      // Placeholder logic, not actually setting front page yet.
      $this->messenger()->addStatus($this->t('Pretending to set %title as homepage.', ['%title' => $node->label()]));
    }
    return new RedirectResponse('/node/' . $node->id() . '/edit');
  }

}
