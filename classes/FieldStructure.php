<?php
/**
 * Constructs the markup structure of the config fields
 *
 * User: lloop
 * Date: 7/13/14
 * Time: 6:57 PM
 */

class FieldStructure {

  // Fieldsets
  // todo Put default values in this??? Would reduce the complexity of the processValues function in the Tab class
  private $field_sets =  array(
      // Probability
     'header' => array(
         'header_fieldset'                  => array(
             'type'                           => 'fieldset_open',
             'label'                          => 'Probability'
         ),
         'probability'                      => array('type' => 'input'),
         'header_fieldset_close'            => array('type' => 'fieldset_close')
     ),

      // Required
     'required' => array(
         'required_fieldset'                => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'Required',
           'description'                      => 'This field requires a value. No probability input'
         ),
         'required_fieldset_close'          => array('type' => 'fieldset_close')
     ),

      // Numerical Upper and Lower
     'size' => array(
         'size_fieldset'                    => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'Number limits',
           'description'                      => 'Upper and lower boundaries'
         ),
         'lower_limit'                      => array('type' => 'input'),
         'upper_limit'                      => array('type' => 'input'),
         'size_fieldset_close'              => array('type' => 'fieldset_close')
     ),

      // File size and units
     'file_size' => array(
       'size_fieldset'                    => array(
         'type'                             => 'fieldset_open',
         'label'                            => 'File size',
         'description'                      => 'Upper and lower size'
       ),
       'lower_size'                       => array('type' => 'input'),
       'upper_size'                       => array('type' => 'input'),
       'units'                            => array('type' => 'input'),
       'file_size_fieldset_close'         => array('type' => 'fieldset_close')
     ),

      // String size and type
     'size_and_type' => array(
         'size_and_type_fieldset'           => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'String size and type',
           'description'                      => 'If string type is with word spaces then size limits are in words. If not then in letters.'
         ),
         'lower_limit'                      => array('type' => 'input'),
         'upper_limit'                      => array('type' => 'input'),
         'string_type'                      => array('type' => 'input'),
         'capitalization'                   => array('type' => 'input'),
         'size_and_type_fieldset_close'     => array('type' => 'fieldset_close')
     ),

      // Amount plus width and height ranges
     'size_and_dims' => array(
         'size_and_dims_fieldset'           => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'Upper and lower amounts',
           'description'                      => 'Select how many different images'
         ),
         'lower_limit'                      => array('type'     => 'input',
                                                     'label'    => 'Amount created lowest'),
         'upper_limit'                      => array('type'     => 'input',
                                                     'label'    => 'Amount created most'),
         'lower_width'                      => array('type'     => 'input'),
         'lower_height'                     => array('type'     => 'input'),
         'upper_width'                      => array('type'     => 'input'),
         'upper_height'                     => array('type'     => 'input'),
         'size_and_dims_fieldset_close'     => array('type'     => 'fieldset_close')
     ),

      // String prepend
     'prepend_string' =>  array(
         'prepend_string_fieldset'          => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'Prepend a string',
           'description'                      => 'Prepending an auto string is required in the title if you want to delete auto generated pages later. Prepending a custom is good for searches.',
         ),
         'prepend_confirm'                  => array('type' => 'input'),
         'custom_confirm'                   => array('type' => 'input'),
         'custom_string'                    => array('type' => 'input'),
         'prepend_string_fieldset_close'    => array('type' => 'fieldset_close')
     ),

      // Prepared strings
     'prepared_strings' => array(
         'prepared_strings_fieldset'        => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'Use prepared strings'
         ),
         'prepared_confirm'                 => array('type' => 'input'),
         'prepared_values'                  => array('type' => 'input'),
         'prepared_strings_fieldset_close'  => array('type' => 'fieldset_close')
     ),

      // Deviation curves
     'deviations' => array(
         'deviations_fieldset'              => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'Deviations',
           'description'                      => 'This configures the amount of items generated. It can be weighted to more likely produce the least, the most, or average'
         ),
         'normal_dev_confirm'               => array('type'     => 'input'),
         'deviation_type'                   => array('type'     => 'input'),
         'standard_deviation'               => array('type'     => 'input'),
         'deviations_fieldset_close'        => array('type'     => 'fieldset_close')
     ),

      // Date Range
     'date_range' => array(
         'date_range_fieldset'             => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'Date range',
           'description'                      => 'The earliest and latest that a randomly selected date could be. Format: 2014-06-25 20:13:48'
         ),
         'earliest_date'                    => array('type' => 'input',
                                                     'range'    => '-100:+100'),
         'latest_date'                      => array('type' => 'input',
                                                     'range'    => '-100:+100'),
         'date_range_fieldset_close'       => array('type' => 'fieldset_close')
     ),

      // Date Dependency
     'date_dependency' => array(
         'date_dep_fieldset'              => array(
             'type'                             => 'fieldset_open',
             'label'                            => 'Date dependency',
             'description'                      => 'Not enabled yet - DO NOT DEPEND ON THIS (no pun intended)'
              // 'description'                  => 'If a date is dependant on another. Like an ending date that must be after an opening date'
         ),
         'date_dep_confirm'                   => array('type' => 'input'),
         'date_dep_length'                    => array('type' => 'input'),
         'date_dep_fieldset_close'        => array('type' => 'fieldset_close')
     ),

      // File extension types
     'file_ext_types' => array(
       'file_ext_types_fieldset'            => array(
         'type'                             => 'fieldset_open',
         'label'                            => 'File extensions to randomly select',
         'description'                      => "Valid types: pdf, txt, doc, docx, xls, xlsx, zip, rar"
       ),
       'extension_types'                  => array('type' => 'input'),
       'file_ext_types_fieldset_close'    => array('type' => 'fieldset_close')
     ),

      // Config notes
     'footer' => array(
         'footer_fieldset'                  => array(
           'type'                             => 'fieldset_open',
           'label'                            => 'Notes'
         ),
         'conf_notes'                       => array('type' => 'input'),
         'footer_fieldset_close'            => array('type' => 'fieldset_close')
     )
  );


  /**
   *
   *
   * @param $field
   * @return mixed
   */
  public function getItems($field) {

    // Probability and required
    $prob_req = $field->get("required") ? $this->field_sets['required'] : $this->field_sets['header'];

    // Categorize
    $field_tyoe = $field->type->className;
    $category = $this->categorizeFieldtype($field_tyoe);

    // todo implement a throw and catch here
    // Concat a function name
    $function_name = 'build' . $category;
    $select = $this->$function_name($field);

    // Intersect the array with the config item units and the main array
    $arrays = array_intersect_key($this->field_sets, $select);

    // Flatten the result
    $items = $this->flattenArr($arrays);

    // Merge with the probability/required result
    $merged = array_merge($prob_req, $items);

    return $merged;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildTitle($field) {

    $select = array('size_and_type'     => '',
                    'prepend_string'    => '',
                    'prepared_strings'  => '',
                    'footer'            => ''
    );

    return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildText($field) {

    $select = array('size_and_type'     => '',
                    'prepend_string'    => '',
                    'prepared_strings'  => '',
                    'footer'            => ''
    );

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildComments($field) {

    $select = array('size_and_type'     => '',
                    'prepend_string'    => '',
                    'footer'            => ''
    );

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildEmail($field) {

    $select = array('prepared_strings'  => '',
                    'footer'            => ''
    );

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildUrl($field) {

    $select = array('prepared_strings'  => '',
                    'footer'            => ''
    );

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildNumber($field) {

    $select = array('size'              => '',
                    'footer'            => ''
    );

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildDate($field) {

    $select = array('date_range'        => '',
                    'date_dependency'   =>'',
                    'footer'            => ''
    );

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildPage($field) {

    // if multi conditions exist then use the size element
    if ($field->derefAsPage == 0) {
      $select = array('size'              => '',
                      'footer'            => ''
      );
    } else {
      $select = array('footer'            => '');
    }

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildRepeater($field) {

    $select = array('size'              => '',
                    'deviations'        => '',
                    'footer'            => ''
    );

    return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildCheckbox($field) {

    $select = array('footer'            => ''
    );

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildFile($field) {

    $select = array('file_size'         => '',
                    'file_ext_types'    => '',
                    'footer'            => ''
    );

		return $select;

  }


  /**
   * Called with a name dynamically constructed from getItems()
   *
   * @param $field
   * @return array
   */
  private function buildImage($field) {

    $select = array('size_and_dims'     => '',
                    'deviations'        => '',
                    'footer'            => ''
    );

		return $select;

  }


  /**
   *
   *
   * @param $field_type
   * @return string
   */
  private function categorizeFieldtype($field_type) {

    $cat_types = array(
      'FieldtypePageTitle'            => 'Title',
      'FieldtypePageTitleLanguage'    => 'Title',
      'FieldtypeText'                 => 'Text',
      'FieldtypeTextLanguage'         => 'Text',
      'FieldtypeTextarea'             => 'Text',
      'FieldtypeTextareaLanguage'     => 'Text',
      "FieldtypeComments"             => 'Comments',
      "FieldtypeEmail"                => 'Email',
      "FieldtypeURL"                  => 'Url',
      "FieldtypeInteger"              => 'Number',
      "FieldtypeFloat"                => 'Number',
      "FieldtypeDatetime"             => 'Date',
      "FieldtypePage"                 => 'Page',
      "FieldtypeRepeater"             => 'Repeater',
      "FieldtypeCheckbox"             => 'Checkbox',
      "FieldtypeFile"                 => 'File',
      "FieldtypeImage"                => 'Image'
    );

    return $cat_types[$field_type];

  }


  /**
   *
   *
   * @param $arr
   * @return array
   */
  private function flattenArr($arr) {

    $new_array = array();

    // $k is 'size'
    // $v is the array ( I want to append the array items on to $new_array )

    foreach($arr as $k=>$v) {
      $new_array = array_merge($new_array, $v);
    }

    return $new_array;
  }

}