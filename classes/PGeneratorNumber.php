<?php
/**
 *
 *
 * User: lloop
 * Date: 7/12/14
 * Time: 11:52 AM
 */

class PGeneratorNumber extends PGenerator implements GeneratorStyle {

  public function __construct($field) {

    parent::__construct($field);

  }

  /**
   * @param $type
   * @param $values
   * @return mixed
   * @throws WireException
   */
  public function genFieldContent($values, $batch_id) {

    $type = $this->field->type->className;

    switch($type) {
      case "FieldtypeInteger":
        $content = $this->genFieldtypeInteger($values);
        break;
      case "FieldtypeFloat":
        $content = $this->genFieldtypeFloat($values);
        break;
      default:
        throw new WireException("Invalid field type: " . $this->field->type . " in PGeneratorNumber class");
    }

    return $content;
  }


  /**
   * @param $values
   * @param $batch_id
   * @return bool
   */
  private function genFieldtypeInteger($values) {


    return true;
  }


  /**
   * @param $values
   * @param $batch_id
   * @return bool
   */
  private function genFieldtypeFloat($values) {
    return true;
  }


}