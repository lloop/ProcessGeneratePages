<?php
/**
 * Tab for configuring the parameters used to semi-randomly generate
 * the subject field values
 *
 * TODO Using the same nested foreach to process $_POST multi-dimensional array in buildConfigTab and execute. DRY!
 *
 * User: lloop
 * Date: 7/8/14
 * Time: 9:46 AM
 */

class TabConfig extends Tab {


  function __construct() {

    $this->database = $this->wire('database');

    parent::__construct();

  }


  /**
   * Build the tab for field configurations
   *
   * @param $wrapper
   */
  public function buildConfigTab(InputfieldForm $form) {

    $form->attr('id', 'configs_form');
    $fieldset = wire('modules')->get('InputfieldFieldset');

    // Submit button
    $button = wire('modules')->get("InputfieldSubmit");
    $button->attr('name', 'submit_config');
    $button->attr("value", "Save Configs");
    $button->attr("id", "submit_config_top");
    $form->append($button);

    // The markup wrapping the table
    $results = wire('modules')->get('InputfieldMarkup');
    $results->label = 'Fields';

    // Create a markup table
    $table = wire('modules')->get('MarkupAdminDataTable');
    $table->setEncodeEntities(false);
    $table->setClass('gen-pages-fields');

    // Table Header
    $header = array(
      '<div></div>',
      'Label',
      'Template'
    );
    $table->headerRow($header);

    // Build the rows
    $nFields = 0;

    // Get all the fields in the table
    $conf_fields = $this->getTable(ProcessGeneratePages::TABLE_NAME);

    // Declare the initial array to organize the table fields into
    $table_values = array();

    foreach ($conf_fields as $conf_field) {

      // Get the four important columns of info from the table
      $template = $conf_field['template_name'];
      $subj_id = $conf_field['subject_id'];
      $conf_name = $conf_field['conf_name'];

      // Process prepared values out of json or else use the value
      if ($conf_name === 'prepared_values' && !empty($conf_field['conf_value'])) {
        $conf_val = $this->processPreparedValues($conf_field['conf_value']);
      } else {
        $conf_val = $conf_field['conf_value'];
      }

      // If there is no key for the template_name
      if (!array_key_exists($template, $table_values)) {
        $table_values[$template] = array();
      }

      // If there is no key for the subject_id within the template_name key
      if (!array_key_exists($subj_id, $table_values[$template])) {
        $table_values[$template][$subj_id] = array();
      }

      // If there is no key for the conf_name within the subject_id array
      if (!array_key_exists($conf_name, $table_values[$template][$subj_id])) {
        $table_values[$template][$subj_id][$conf_name] = $conf_val;
      }

    }

    // $table_values is an array $table_values[template_name][subject_id][conf_name]=>[conf_val]
    foreach($table_values as $template=>$subject_ids) {

      foreach($subject_ids as $subject_id=>$conf_pairs) {

        // Create a new InputfieldFieldConfig object
        $field_config = wire('modules')->get('InputfieldFieldConfig');

        // Get the subject field
        $fld = wire('fields')->get($subject_id);
        // Set the subject field
        $field_config->setField($fld);

        // Set the template name
        $field_config->setTemplateName($template);

        // Set the conf_val
        $field_config->setValues($conf_pairs);

        // Return an array of FieldConfig objects and render them
        $row_configs = $field_config->getConfigItems($conf_pairs);
        $row_markup = $row_configs->render();

        // Remove the 'Fieldtype' section of the $type string for the label
        $arr = explode("Fieldtype", $fld->type);
        $type_title_core = array_pop($arr);

        // Create a row in the foreach dealing with subject_ids
        $row = array(
          "<div class='ui-widget-gp  ui-icon-triangle-1-e'></div>",
          "<span class='field-head field-id-$fld->id' >$fld->label" .
          "<div class='head-right f-right' >" .
          "<span class='f-left' >" . $type_title_core . "</span>" .
          "</div>" .
          "<div class='field-content'>$row_markup</div>" .
          "</span>",
          "<span class='field-template'>$template</span>"
        );

        $table->row($row);
        $nFields++;

      }

    }

    if ($nFields == 0) {
      $this->error($this->_('No fields found that are valid'));
      return false;
    }

    // Set the value attr of the InputfieldMarkup to the table render
    $results->attr('value', $table->render());
    $form->append($results);

    // Submit button bottom
    $button = wire('modules')->get("InputfieldSubmit");
    $button->attr('name', 'submit_config');
    $button->attr("value", "Save Configs");
    $button->attr("id", "submit_config_bottom");
    $form->append($button);

    // Append the field config table and submit button
    $fieldset->append($form);

    // Build import/export of configs
    $db_form = $this->buildImpExp();

    // Append the import/export buttons
    $fieldset->append($db_form);

    return $fieldset;

  }


  /**
   * Configuration save
   *
   * @param InputfieldForm $form
   * @throws WireException
   */
  public function execute(InputfieldForm $form) {

    $database = wire('database');
    $table = ProcessGeneratePages::TABLE_NAME;
    $changes = false;

    // wire('input') removes multi-dimensional arrays. Must use $_POST
    // $post = wire('input')->post;
    $post = $_POST;

    // Process the input
    // $form->processInput($post);

    // todo add error checking
    // Can't use the $form validation. Find an alternative
    // if ($form->getErrors()) {}


    // Sanitize inputs
    $this->sanitize($post);

    // What I have now is an array with [template][subject_id][conf_name=>conf_value]
    foreach ($post as $template=>$subject_ids) {

      // Filter out the non-array keys (where the subject_field_ids are)
      // created by the POST (throw errors on foreach)
      if (gettype($subject_ids) === 'array') {

        foreach ($subject_ids as $subject_id => $conf_pairs) {

          // Create a new InputfieldFieldConfig object
          $field_config = wire('modules')->get('InputfieldFieldConfig');

          // Get the subject field
          $fld = wire('fields')->get($subject_id);
          // Set the subject field
          $field_config->setField($fld);

          // DB values. Returns an empty array if no db data
          $dbs = $this->getFieldDbValue($subject_id, $template);

          // Process the dbs, defaults and submits together
          $save_values = $this->processValues($dbs, $conf_pairs);

          // Iterate through the $save_values array
          if (!empty($save_values)) {

            $changes = true;

            foreach ($save_values as $name => $val) {
              $query  = $database->prepare(" UPDATE `$table` SET conf_value='$val' WHERE `subject_id`=$subject_id AND `conf_name`='$name' AND `template_name`='$template' ");
              $result = $query->execute();

              // Get the name of the subject field
              $s_name = wire('fields')->get($subject_id)->name;
              // Compose message
              $common_string = " the template: " . $template . ", subject field: " . $s_name . ", config field: " . $name . ", with the value of: " . $val;
              if ($result) {
                wire('page')->message("Successfully updated" . $common_string);
              } else {
                wire('page')->error("Failed to update" . $common_string);
              }

            }

          }

        }

      }

    }

    if (!$changes) { wire('page')->error("No config data was altered. Nothing saved."); };

  }


  /**
   * Preserves UTF-8 characters in a JSON encode
   *
   * @param $arr
   * @return string
   */
  function my_json_encode($arr) {
    //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
    array_walk_recursive($arr, function (&$item, $key) {
      $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });

    return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
  }


  /**
   * Removes the subject field id number from the input name and reorders the POST
   * array into subarrays with the subject field id as the key
   *
   * @param $submit_values
   * @return mixed
   */
//  private function renameReorderPost($posts) {
//
//    $submit_values = array();
//
//    // Get the post array into a 2D array under the 'subject field id'  groups
//    foreach ($posts as $post => $value) {
//      $name_arr = explode('-_-', $post);
//
//      // Process the 'prepared values' input and return a json encoded array
//      if ($name_arr[0] === 'prepared_values' && !empty($value)) {
//        $value = $this->processPreparedValues($value, true);
//      }
//
//      // Only inputs with the dash-underscore-dash in the name
//      if (count($name_arr) > 1) {
//        $conf_name          = $name_arr[0];
//        $subj_id_template   = $name_arr[1];
//        if (array_key_exists($subj_id_template, $submit_values)) {
//          $submit_values[$subj_id_template][$conf_name] = $value;
//        } else {
//          $submit_values[$subj_id_template] = array($conf_name => $value);
//        }
//      }
//    }
//
//    return $submit_values;
//  }




  /**
   * Sanitize an entire submit
   *
   * @param $posts
   * @return mixed
   * @throws WireException
   */
  private function sanitize($posts) {

    $sanitizer = wire('sanitizer');

    foreach ($posts as $post => $value) {
      // Split the name to remove the subject field id
      $name_arr = explode('-', $post);

      // Filter out all the items that don't have a dash in there name (like the submit button)
      if (count($name_arr) > 1) {

        // Get sanitizer from the default values array
        $d_values = ProcessGeneratePages::$default_values;
        $san = $d_values[$name_arr[0]]['sanitizer'];

        switch ($san) {
          case 'text' :
            $res = $sanitizer->text($value);
            break;
          case 'text_small':
            $res = $sanitizer->text($value, array('maxLength' => 50));
            break;
          case 'text_area':
            $res = $sanitizer->textarea($value);
            break;
          case 'int':
            $res = (int) $value;
            break;
          case 'float':
            $res = (float) $value;
            break;
          default:
            throw new WireException("Invalid sanitizer type: " . $san . " in TabField class");
        }

        $posts[$post] = $res;

      } else {
        unset($posts[$post]);
      }

    }

    return $posts;

  }


  /**
   * Keeping the import and export fields as separate forms for cleanliness
   *
   * @return mixed
   */
  private function buildImpExp() {

    // Import and Export
    $fieldset = wire('modules')->get('InputfieldFieldset');
    $fieldset->label = $this->_('Import and Export');

    // Import form
    $import_form = wire('modules')->get('InputfieldForm');
    // Field configs import field
    $import_file = wire('modules')->get('InputfieldFile');
    $import_file->name = 'config_import';
    $import_file->label = $this->_('Configs File Import');
    $import_file->extensions = 'csv';
    $import_file->noAjax = 1;
    $import_file->maxFiles = 1;
    $import_file->descriptionRows = 1;
    $import_file->overwrite = 1;
    $import_file->destinationPath = "/tmp/";
    // Add the file upload field to the import form
    $import_form->add($import_file);
    // Import form submit
    $import_submit = wire('modules')->get('InputfieldSubmit');
    $import_submit->name = 'submit_import';
    $import_submit->value = "Import Configs";
    $import_submit->id = "submit_import";
    $import_form->add($import_submit);
    // Append the import form to the fieldset
    $fieldset->append($import_form);

    // Export form
    $export_form = wire('modules')->get('InputfieldForm');
    // Import form submit
    $export_submit = wire('modules')->get('InputfieldSubmit');
    $export_submit->name = 'submit_export';
    $export_submit->value = "Export Configs";
    $export_submit->id = "submit_export";
    $export_form->add($export_submit);
    // Append the import form to the fieldset
    $fieldset->append($export_form);

    return $fieldset;

  }


  /**
   *
   */
  public function export() {

    $table = self::TABLE_NAME;
    $time = time();
    $filename = $table . $time . ".csv";
    $filepath = "/tmp/" . $filename;

    // Need to check if the table exists
    $this->backup = $this->wire('database')->backups();
    $allTables = $this->backup->getAllTables();

    if($allTables[$table]) {

      $sql = "SELECT subject_id, subject_name, template_name, conf_name, conf_value " .
          "INTO OUTFILE '" . $filepath . "' " .
          "FIELDS TERMINATED BY ',' " .
          "OPTIONALLY ENCLOSED BY '`' " .
          "ESCAPED BY '\\'' " .
          "LINES TERMINATED BY '\n' " .
          "FROM " . $table;

      $query = $this->database->prepare($sql);
      $query->execute();

      $options = array(
          'exit'             => true, // boolean: halt program execution after file send
          'forceDownload'    => true, // boolean|null: whether file should force download (null=let content-type header decide)
          'downloadFilename' => ''); // string: filename you want the download to show on the user's computer, or blank to use existing.

      wireSendFile($filepath, $options);

    } else {
      throw new WireException("Generate Pages database table does not exist");
    }

  }


  /**
   * @param $filename
   */
  public function import($filename) {

    // TODO !!!!! Security !!! Sanitize???

    $filepath = "/tmp/" . $filename;
    $table = ProcessGeneratePages::TABLE_NAME;
    $temp_table = $table . "_temp_import";

    // First create a temporary new table to hold the import
    // Table has identical primary key as the original
    $sql_temp = 	"CREATE TABLE `" . $temp_table . "` (" .
        " `subject_id` int(10) unsigned NOT NULL," .
        " `subject_name` varchar(255) CHARACTER SET ascii NOT NULL," .
        " `template_name` varchar(255) CHARACTER SET ascii NOT NULL," .
        " `conf_name` varchar(255) CHARACTER SET ascii NOT NULL," .
        " `conf_value` text DEFAULT NULL " .
        ") ENGINE=MyISAM DEFAULT CHARSET=utf8";

    $sql_truncate = "TRUNCATE " . $temp_table;

    // Load the file into the temp table
    $sql_load = "LOAD DATA INFILE '" . $filepath . "'" .
      " INTO TABLE " . $temp_table .
      " FIELDS TERMINATED BY ',' " .
      " OPTIONALLY ENCLOSED BY '`' " .
      " LINES TERMINATED BY '\n' ";

    // Update the original table (column = conf_value)
    // where the primary keys match
    $sql_update = "UPDATE " . $table . ", `" . $temp_table . "`" .
        " SET " . $table . ".conf_value = " . $temp_table . ".conf_value" .
        " WHERE " . $table . ".subject_name = " . $temp_table . ".subject_name" .
        " AND " . $table . ".template_name = " . $temp_table . ".template_name" .
        " AND " . $table . ".conf_name = " . $temp_table . ".conf_name";

    // Drop the temporary table
    $sql_drop = "DROP TABLE " . $temp_table;

    // If the temp table exists truncate it else create new
    if($this->tableExists($this->database, $temp_table)) {
      $query_temp = $this->database->prepare($sql_truncate);
    } else {
      $query_temp = $this->database->prepare($sql_temp);
    }
    $query_temp->execute();

    // Load the file into the temp table
    $query_load = $this->database->prepare($sql_load);
    $query_load->execute();

    $sql_update = $this->database->prepare($sql_update);
    $sql_update->execute();

    $sql_drop = $this->database->prepare($sql_drop);
    $sql_drop->execute();

  }

  /**
   * Check if a table exists in the current database.
   *
   * @param PDO $pdo PDO instance connected to a database.
   * @param string $table Table to search for.
   * @return bool TRUE if table exists, FALSE if no table found.
   */
  private function tableExists($pdo, $table) {

    // Try a select statement against the table
    // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
    try {
      $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
    } catch (Exception $e) {
      // We got an exception == table not found
      return FALSE;
    }

    // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
    return $result !== FALSE;
  }

}