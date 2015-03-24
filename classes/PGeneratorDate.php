<?php
/**
 *
 *
 * User: lloop
 * Date: 7/9/14
 * Time: 1:50 PM
 */

class PGeneratorDate extends PGenerator implements GeneratorStyle {

  /**
   *
   *
   */
  public function __construct($field) {

    parent::__construct($field);

  }


  /**
   * A fieldgroup that designates whether the date picker is dependant on another
   * date is set up. But that is for another time to develop.
   *
   *
   * @param $type
   * @param $values
   * @return mixed
   * @throws WireException
   */
  public function genFieldContent($values, $batch_id) {

//    var_dump($values);

    $earliest = strtotime($values['earliest_date']);
    $latest = strtotime($values['latest_date']);

    $highest = max($earliest, $latest);
    $lowest = min($earliest, $latest);

    $timestamp = rand($lowest, $highest);

//    $type = $this->field->type->className;

//    $content = date("Y-m-d H:i:s");


    $content = date("Y-m-d H:i:s", $timestamp);

    return $content;
  }


  /**
   * This was copied from the PGeneratorNumber file that originally handled
   * date content generation
   *
   * @param $values
   * @param $batch_id
   * @return bool
   */
  private function genFieldtypeDatetime($values) {

    $earliest = empty($values['earliest_date']) ? time() : (int) $values['earliest_date'];
    $latest = empty($values['latest_date']) ? time() : (int) $values['latest_date'];

    // Date - format: 2014-06-25 20:13:48
    $int = mt_rand($earliest, $latest);
    $date = date("Y-m-d H:i:s", $int);

    return $date;

  }


}