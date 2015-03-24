<?php
/**
 *
 *
 * User: lloop
 * Date: 7/12/14
 * Time: 1:23 PM
 */

class GeneratorFactory {

  public function __construct() {}

  public static function create($field) {

    $cat = ProcessGeneratePages::$valid_types[$field->type->className];

    switch($cat) {
      case 'text':
        return new PGeneratorText($field);
        break;
      case 'number':
        return new PGeneratorNumber($field);
        break;
      case 'date':
        return new PGeneratorDate($field);
        break;
      case 'image':
        return new PGeneratorImage($field);
        break;
      case 'file':
        return new PGeneratorFile($field);
        break;
      case 'page':
        return new PGeneratorPage($field);
        break;
      case 'repeater':
        return new PGeneratorRepeater($field);
        break;
      case 'checkbox';
        return new PGeneratorCheckbox($field);
        break;
      default:
        throw new WireException("Invalid type in class: " . __CLASSNAME__ );
    }
  }

}