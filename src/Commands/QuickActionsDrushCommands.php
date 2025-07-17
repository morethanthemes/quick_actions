<?php

namespace Drupal\quick_actions\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Drupal\Core\Config\ConfigFactoryInterface;

final class QuickActionsDrushCommands extends DrushCommands {

  public function __construct(protected ConfigFactoryInterface $configFactory) {
    parent::__construct();
  }

  #[CLI\Command(name: 'quick-actions:add-default-path', description: 'Add a default view path (e.g. /events/card)')]
  #[CLI\Argument(name: 'path', description: 'The view display path to render at /{scope}')]
  public function addDefaultPath(string $path): void {
    $config = $this->configFactory->getEditable('quick_actions.settings');
    $paths = $config->get('default_paths') ?? [];

    if (!in_array($path, $paths)) {
      $paths[] = $path;
      $config->set('default_paths', $paths)->save();
      $this->io()->success("Added $path to default_paths.");
    }
    else {
      $this->io()->warning("$path already in default_paths.");
    }
  }
}
