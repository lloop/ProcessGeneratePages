<?php
/**
 *
 *
 * User: lloop
 * Date: 7/12/14
 * Time: 11:59 AM
 */

class PGeneratorPage extends PGenerator implements GeneratorStyle {

  public function __construct($field) {

    parent::__construct($field);

  }

  /**
   * @param $values
   */
  public function genFieldContent($values, $batch_id) {

    $selected = '';
    $field = $this->field;

    // If the random number is not within the probability return empty string
    $probability = mt_rand(1, 100);
    if ($probability > $values['probability']) return $selected;

    // TODO Must develop this out
    // Can get selectable pages from four different ways
    // (parent_id - choose parent , template_id - choose template,
    //  findPagesSelector - PW selector, findPagesCode - PHP code w/ PW variables)
    // For now, I will only use the template_id and parent_id.
    $temp_id = $field->template_id;
    $parent_id = $field->parent_id;

    // If no selectable pages exist return empty string
    $selectables = wire('pages')->find('template='.$temp_id.', has_parent='.$parent_id);
    if (count($selectables) === 0) return $selected;

    // If page is not 'multi-page' (single is derefAsPage 1 and 2)
    If ($this->field->derefAsPage > 0) {

      $selected = $this->selectPage($field, $selectables);
      $result = $selected->id;

    } else {

      // Page is multi
      $amount = mt_rand($values['lower_limit'], $values['upper_limit']);

      for ($i = 0; $i < $amount; $i++) {

        $next = $this->selectPage($field, $selectables);
        $selected .= "," . $next->id;
        $result = trim($selected, ",");

      }

    }

    return $result;

    // Artist field
//    array
//      'derefAsPage' => int 1                                        ---- single or multi
//      'parent_id' => int 1020                                       ---- selectable pages by parent (this one is '/ Artists')
//      'labelFieldName' => string 'title' (length=5)                 ++++ which field becomes the label
//      'inputfield' => string 'InputfieldSelect' (length=16)         ++++ input fields type on edit window
//      'template_id' => int 45                                       ---- selectable pages by template (artist-info)
//      'tags' => string 'Item' (length=4)                            ++++
//      'columnWidth' => int 17                                       ++++ column width
//      'label1082' => string 'Künstlername' (length=13)              ++++ translation
//      'addable' => int 1                                            ++++ allow new pages to be creted from the select input
//      'findPagesSelector' => string 'parent=/Artists' (length=15)   ---- selectable pages by selector(if the entry is empty then this doesn't show up in field object)
//      'findPagesCode' => string '$page->parent' (length=13)         ---- slectable pages by PHP code (same as above)

  }


  private function selectPage($field, $selectables) {

    $upper_index = count($selectables) - 1;

    $selected = $selectables[mt_rand(0, $upper_index)];

    return $selected;
  }

}