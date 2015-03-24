<?php
/**
 *
 *
 * User: lloop
 * Date: 7/12/14
 * Time: 12:01 PM
 */

class PGeneratorRepeater extends PGenerator implements GeneratorStyle {


  public function __construct($field) {

    parent::__construct($field);

  }


  /**
   * @param $values
   */
  public function genFieldContent($values, $batch_id) {

    // Generate amount of repeats
    $lower_limit = settype($values['lower_limit'], 'integer');
    $upper_limit = settype($values['upper_limit'], 'integer');
    $amount = mt_rand($lower_limit, $upper_limit);
    $repeated_fields = $this->field->repeaterFields; // array

    if(array_key_exists('probability', $values)) {
      // Probability
      $probability = mt_rand(1, 100);
      if ($probability > $values['probability']) {
        $pge_fields[$this->field->name] = "";

        return;
      }
    }

    // For each generated repeat
    for ($i = 1; $i <= $amount; $i++) {

      $field_name = $this->field->name;

      // Create a new repeat item
      $repeat_item = $this->page->$field_name->getNewItem();

      // For each of the repeated fields in the repeat item
      foreach ($repeated_fields as $field_id) {

        // Get the repeated field
        $f = wire('fields')->get($field_id);
        $name = $f->name;

        // Get the fieldConfig
        $field_config = wire('modules')->get('InputfieldFieldConfig');
        // Set the field
        $field_config->setField($f);
        // Get the default values for configfield
        $defaults = $field_config->getDefaults();
        // DB values. Returns an empty array if no db data
        $values = $this->getFieldDbValue($field_id, $this->template);

        // Instantiate a generator for the repeated field
        $repeated_gen = GeneratorFactory::create($f);
        $repeated_gen->setPage($this->page);
        $repeated_content = $repeated_gen->genFieldContent($values, $batch_id);

        // Gets the combined values(db,default)
//        $values = $this->getValues($f->id, $this->template);

        // Assign the content to the repeated field in the repeat item
        // $repeat_item->$name is assigning the field with a variable name
        $repeat_item->$name = $repeated_content;
      }

      $this->page->$field_name->add($repeat_item);

    }

  }

}