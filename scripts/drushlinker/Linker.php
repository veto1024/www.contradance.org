<?php

/**
 * @file
 * Contains \DrushLinker\Linker.
 */

namespace DrushLinker;


use Composer\Script\Event;

class Linker {

  /*
   * linkDrush function
   */

  public static function linkDrush(Event $event) {
    $target_pointer = $drupalRoot . "/vendor/bin/drush";
    $link_name = "/usr/bin/drush";
    if (!is_link($link_name)) {
      $result = symlink($target_pointer, $link_name);
      if ($result) {
        $event->getIO()->write("Symlink to Drush created!");
      }
      else {
        $event->getIO()->write("Symlink to Drush NOT created!");
      }
    }
    else {
      $event->getIO()->write("Symlink to Drush already exists");
    }
  }

}
