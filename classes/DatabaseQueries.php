<?php
/**
 * Creates config fields in the database. This is used when the module is installed
 * to create the table. It is also used when a new fiels is added or removed
 * from a template.
 *
 * User: lloop
 * Date: 2/12/15
 * Time: 7:52 PM
 */

class DatabaseQueries extends WireData {

  /**
   *
   */
  function __construct() {

    $this->database = $this->wire('database');

    $this->table_name = ProcessGeneratePages::TABLE_NAME;

    parent::__construct();

  }

  /**
   * @param $name
   */
  public function createTable($name) {

    $sql = 	"CREATE TABLE `$name` (" .
        " `id` int(10) unsigned NOT NULL AUTO_INCREMENT," .
        " `subject_id` int(10) unsigned NOT NULL," .
        " `subject_name` varchar(255) CHARACTER SET ascii NOT NULL," .
        " `template_name` varchar(255) CHARACTER SET ascii NOT NULL," .
        " `conf_name` varchar(255) CHARACTER SET ascii NOT NULL," .
        " `conf_value` text DEFAULT NULL, " .
        " PRIMARY KEY (`id`) " .
        ") ENGINE=MyISAM DEFAULT CHARSET=utf8";

    $query = $this->database->prepare($sql);
    $result = $query->execute();

    if($result) {
      wire('pages')->message("Table for Generate Pages Module created in DB :: " . $name);
      return true;
    } else {
      wire('pages')->error("Table for Generate Pages Module not created in DB:: " . $name);
      return false;
    }

  }


  /**
   * Check if table exists n database
   *
   * @param $table
   * @return mixed
   */
  public function checkTable($table) {
    $tables = $this->database->getTables();
    $result = in_array($table, $tables);
    return $result;
  }


  /**
   * @param $name
   */
  public function dropTable($name) {

    $sql = 	"DROP TABLE `$name` ";
    $query = $this->database->prepare($sql);
    $result = $query->execute();

    if($result) {
      wire('pages')->message("Generate Pages - Table dropped in DB :: " . $name);
      return true;
    } else {
      wire('pages')->error("Generate Pages - Table not dropped in DB:: " . $name);
      return false;
    }

  }

  /**
   *
   */
  public function createConfigRows($field, $template) {

    $q_result = true;

    // Establish the subject field variables
    $subject_id = $field->get('id');
    $subject_name = $field->get('name');
    $subject_flags = $field->get('flags');
    $subject_type = $field->type->className;

    // Template name
    $template_name = $template->name;

    // If the subject field is a valid type
    if (array_key_exists($subject_type, ProcessGeneratePages::$valid_types) && $subject_flags <= 13) {

      // Get a InputfieldFieldConfig object
      $inputfield = $this->modules->get('InputfieldFieldConfig');
      $inputfield->setField($field);
      $config_names = $inputfield->getConfigNames();

      // Iterate over the config inputs for this subject field
      foreach ($config_names as $config_name) {
        $config_val = ProcessGeneratePages::$default_values[$config_name]['value'];
        $sql = 	" INSERT INTO `$this->table_name` (`subject_id`, `subject_name`, `template_name`, `conf_name`, `conf_value`)" .
                " VALUES('$subject_id', '$subject_name', '$template_name', '$config_name', '$config_val') ";
        $query = $this->database->prepare($sql);
        $result = $query->execute();
        if(!$result) {
          wire('pages')->error("Generate Pages - Config fields: " . $template_name . ": " . $subject_name . ": " . $config_name . " not created in DB");
          $q_result = false;
        }
      }

    }

    if($q_result) {
      wire('pages')->message("Generate Pages - Config fields: " . $template_name . ": " . $subject_name . " created in DB");
      return true;
    } else {
      wire('pages')->error("Generate Pages - Config fields: " . $template_name . ": " . $subject_name . " not created in DB");
      return false;
    }

  }

  /**
   *
   */
  function deleteConfigRows($field, $template) {

    $q_result = true;

    // Establish the subject field variables
    $subject_id = (int) $field->get('id');
    $subject_name = $field->get('name');

    $template_name = $template->name;

    // get a InputfieldFieldConfig object
    $inputfield = $this->modules->get('InputfieldFieldConfig');
    $inputfield->setField($field);
    $config_names = $inputfield->getConfigNames();

    // Iterate over the config inputs for this subject field
    foreach ($config_names as $config_name) {

      //
      $sql = "DELETE FROM `$this->table_name` " .
        "WHERE subject_id=$subject_id " .
          "AND subject_name='" . $subject_name . "' " .
          "AND template_name='" . $template_name . "' " .
          "AND conf_name='" . $config_name . "' ";

      $query  = $this->database->prepare($sql);
      $result = $query->execute();

      if($result === 0) {
        wire('pages')->error("Generate Pages - Could not find table row for: " . $template_name . " - " . $subject_name . " - " . $config_name);
        $q_result = false;
      }

    }

    if($q_result) {
      wire('pages')->message("Generate Pages - Config fields: " . $template_name . ": " . $subject_name . " deleted in DB");
      return true;
    } else {
      wire('pages')->error("Generate Pages - Config fields for " . $template_name . ": " . $subject_name . " not deleted in DB");
      return false;
    }

  }

}