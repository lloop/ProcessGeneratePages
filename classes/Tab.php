<?php
/**
 *
 *
 * User: lloop
 * Date: 7/11/14
 * Time: 8:54 PM
 */

class Tab extends ProcessGeneratePages {

  public function __construct() {

    parent::__construct();

    $dir = dirname(__FILE__);
    require_once($dir . "/GeneratorFactory.php");
    require_once($dir . "/PPGenerator.php");
    require_once($dir . "/PGeneratorText.php");
    require_once($dir . "/PGeneratorNumber.php");
    require_once($dir . "/PGeneratorDate.php");
    require_once($dir . "/PGeneratorImage.php");
    require_once($dir . "/PGeneratorFile.php");
    require_once($dir . "/PGeneratorPage.php");
    require_once($dir . "/PGeneratorRepeater.php");
    require_once($dir . "/PGeneratorCheckbox.php");

  }


  /**
   * Sometimes a Fieldgroup is sent in and sometimes a Fields object
   *
   * @return array
   */
  protected function validateFields($fieldgroup) {

    $valids = array();
    $valid_types = ProcessGeneratePages::$valid_types;

    foreach($fieldgroup as $f) {
      $type = $f->type->className;
      if(array_key_exists($type, $valid_types) && $f->flags !== 24) {
        array_push($valids, $f);
      }
    }

    return $valids;

  }


  /**
   * Query the db for all the rows that match the subject field id and template name
   * Use the field ID and template name because the field can be used in different templates
   * @param $id
   * @param $template
   * @return array
   */
  protected function getFieldDbValue($id, $template) {

    $database = wire('database');
    $sorted_values = array();
    $table_name = ProcessGeneratePages::TABLE_NAME;

//    var_dump('$id ----');
//    var_dump($id);
//    var_dump('$template');
//    var_dump($template);

    // Query the db for all the conf records for this subject field and template
    $query = $database->prepare("SELECT `conf_name`, `conf_value` FROM `$table_name` WHERE `subject_id`=$id AND `template_name`='$template' ");
    $query->execute();
    $results = $query->fetchAll();

    // If no results return an emoty array
    if (empty($results)) return array();

    // Get the query return into a digestible form
    foreach ($results as $value) {
      $conf_name = $value['conf_name'];
      $conf_value = $value['conf_value'];
      $sorted_values[$conf_name] = $conf_value;
    }

    return $sorted_values;

  }


  /**
   * Get all the fields with the template variations at once
   *
   * @param $table_name
   * @return array
   */
  protected function getTable($table_name) {

    $database = wire('database');

    // Query the db for all the conf records for this subject field that have a value in conf_value
    $query = $database->prepare("SELECT `subject_id`, `subject_name`, `template_name`, `conf_name`, `conf_value` FROM $table_name ");
    $query->execute();
    $results = $query->fetchAll();

    // If no results return an emoty array
    if (empty($results)) return array();

    return $results;

  }



  /**
   * All the values are for a single subject field - template
   * Compares the database values and the submit values and
   * returns savable values
   *
   * @todo Got to be a more graceful way to do this
   *
   * @return array
   */
  protected function processValues($dbs, $submits) {

    // all arguments are an array config_name-value pairs


//    var_dump('================= Submits ===');
//    var_dump($submits);
//    var_dump('================= DBs ===');
//    var_dump($dbs);

    $values = array();

    // If a item is missing from submit then it must be a checkbox in negative
    // If the submit item has a value of 'chked' or '!chkd', exclude it
    foreach ($dbs as $k => $v) {
      settype($v, "string");
      // Checkboxes suck!!
      if ($v === 'chkd' || $v === '!chkd') {
        // If the db value equals !chkd and the submit key doesn't exist, continue
        if ($v === '!chkd' && !array_key_exists($k, $submits)) continue;
        // If the db value equals chkd and the submit value exists, continue
        if ($v === 'chkd' && array_key_exists($k, $submits)) continue;
        // If the db value equals !chkd and the submit key exists, save a 'chkd'
        if ($v === '!chkd' && array_key_exists($k, $submits)) $values[$k] = 'chkd';
        // If the db value equals chkd and the submit key doesn't exist, save a '!chkd'
        if ($v === 'chkd' && !array_key_exists($k, $submits)) $values[$k] = '!chkd';
      } else {
        // Normal input items
        if (!empty($submits[$k]) && $v !== (string)$submits[$k]) $values[$k] = (string)$submits[$k];
      }
    }

//    var_dump($values);

    return $values;

  }


  /**
   * Processes the 'prepared values' textarea input. A list of values is submitted.
   * Spaces and carriage returns are trimmed and a json encoded array is returned.
   *
   * @param $v
   * @param $encode
   * @return string
   */
  protected function processPreparedValues($v, $encode=false) {

    if ($encode) {
      // Explode into an array on carriage returns/newlines
      $values = preg_split('/\r\n|\r|\n/', $v);
      // Iterate and trim
      foreach ($values as &$value) {
        // Whitespace, carriage returns, tabs, quotes and commas
        $value = trim($value, " \t\n\r\0\x0B',");
        $value = trim($value, '"');
      }
      unset($value);

      // PHP => 5.4
      // $encoded = json_encode($values, JSON_UNESCAPED_UNICODE);
      // PHP < 5.4
      $encoded = preg_replace_callback(
        '/\\\\u([0-9a-zA-Z]{4})/',
        function ($matches) {
          return mb_convert_encoding(pack('H*',$matches[1]),'UTF-8','UTF-16');
        },
        json_encode($values)
      );

      return $encoded;
    }

    $decoded = json_decode($v);

    $vals = implode("\n", $decoded);

    return $vals;

  }


  /**
   * Build the generate or delete edit tabs
   *
   * @param $tab_name
   * @param $short_name
   * @return mixed
   */
  protected function buildEdit($tab_name, $short_name) {

    $fieldset = wire('modules')->get('InputfieldFieldset');
    $fieldset->label = $this->_('Pages to ' . $tab_name);

    // The parent page selector
    $field = $this->modules->get('InputfieldPageListSelect');
    $field->setAttribute('name', $short_name.'_parent_id');
    $field->label = $this->_('Parent of pages to ' . $tab_name);
    $field->attr('value', (int) $this->parent_id);
    $field->description = $this->_('Select the parent of the pages to ' . $tab_name . '.');
    $field->required = false;
    $fieldset->append($field);

    // The template selector
    $field = $this->modules->get('InputfieldSelect');
    $field->setAttribute('name', $short_name.'_template_id');
    $field->label = $this->_('Template of page(s) to ' . $tab_name);
    $field->attr('value', (int) $this->template_id);
    $field->description = $this->_('Select the template of the pages to ' . $tab_name . '. May be used instead of, or in addition to, the parent above.'); // Description for Template of selectable pages
    $valid_templates = wire('templates')->find('noParents!=1, flags<8');
    foreach($valid_templates as $template) $field->addOption($template->id, $template->name);
    $field->collapsed = Inputfield::collapsedBlank;
    $fieldset->append($field);

    // TODO add the 'selector' and 'php code' pages finder inputs
    //$field = $this->modules->get('InputfieldText');
    //$field->attr('name', 'findPagesSelector');
    //$field->label = $this->_('Custom selector to find selectable pages');
    //$field->attr('value', $this->findPagesSelector);
    //$field->description = $this->_('If you want to find selectable pages using a ProcessWire selector rather than selecting a parent page or template (above) then enter the selector to find the selectable pages. This selector will be passed to a $pages->find("your selector"); statement. NOTE: Not currently compatible with PageListSelect input field types.'); // Description for Custom selector to find selectable pages
    //
    //$field->notes = $exampleLabel . $this->_('parent=/products/, template=product, sort=name'); // Example of Custom selector to find selectable pages
    //$field->collapsed = Inputfield::collapsedBlank;
    //$fieldset->append($field);
    //
    //$field = $this->modules->get('InputfieldTextarea');
    //$field->attr('name', 'findPagesCode');
    //$field->label = $this->_('Custom PHP code to find selectable pages');
    //$field->attr('value', $this->findPagesCode);
    //$field->attr('rows', 4);
    //$field->description = $this->_('If you want to find selectable pages using a PHP code snippet rather than selecting a parent page or template (above) then enter the code to find the selectable pages. This statement has access to the $page and $pages API variables, where $page refers to the page being edited. The snippet should return either a PageArray or NULL. Using this is optional, and if used, it overrides the parent/template/selector fields above. NOTE: Not compatible with PageListSelect or Autocomplete input field types.'); // Description for Custom PHP to find selectable pages
    //$field->notes = $exampleLabel . $this->_('return $page->parent->parent->children("name=locations")->first()->children();'); // Example of Custom PHP code to find selectable pages
    //$field->collapsed = Inputfield::collapsedBlank;
    //$fieldset->append($field);

    return $fieldset;

  }


}