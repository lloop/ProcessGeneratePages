<?php
/**
 * Generate Pages
 *
 * Interfaces used Geneerate Pages module.
 *
 *
 */

/**
 * For classes that generate random content when a page is created
 *
 */
interface GeneratorStyle  {


  /**
   * @param $values
   * @param $batch_id
   * @return mixed
   */
  public function genFieldContent($values, $batch_id);

}