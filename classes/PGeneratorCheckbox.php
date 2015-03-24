<?php
/**
 *
 *
 * User: lloop
 * Date: 7/12/14
 * Time: 12:08 PM
 */

class PGeneratorCheckbox extends PGenerator implements GeneratorStyle {

  public function __construct($field) {

    parent::__construct($field);

  }

  /**
   * @param $values
   * @param $batch_id
   */
  public function genFieldContent($values, $batch_id) {

    $content = '';

    // Probability
    $probability = mt_rand(1, 100);
    if ($probability > $values['probability']) return $content;

    $content = 1;

    return $content;

  }

}