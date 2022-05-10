<?php

namespace Drupal\block_expose\Controller;

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Block Expose routes.
 */
class BlockExposeController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function getHTML(Request $request): JsonResponse {

    $id = $request->query->get('block_id');
    $query = \Drupal::entityQuery('block')
      ->condition('id', $id)
      ->execute();
    if (empty($query)){
      return new JsonResponse("Not found", 204);
    }
    $block = Block::load(reset($query));
    $block_content_id_string = $block->get('settings')['id'];
    $block_content_uuid = explode(':', $block_content_id_string)[1];
    $bcid_query = \Drupal::entityQuery('block_content')->condition('uuid', $block_content_uuid)->execute();
    $bcid = reset($bcid_query);
    $block_content = BlockContent::load($bcid);
    if ($block_content->get('body')->count()){
      $text = $block_content->get('body')[0]->get('value')->getString();
      return new JsonResponse($text, 200);
    }
    return new JsonResponse("No content", 204);

  }

}
