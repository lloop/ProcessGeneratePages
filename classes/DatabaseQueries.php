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
   * Check for config field rows for template in database.
   * Param $template can be a template object or the name(string).
   *
   * @param $template
   * @return int
   */
  public function checkTemplateConfigs($template) {

    $name = gettype($template) === 'object' ? $template->name : $template;

    $sql = "SELECT * FROM `$this->table_name` WHERE template_name=:name";
    $query = $this->database->prepare($sql);
    $query->bindValue(":name", $name);
    $result = $query->execute();
    if (!$result) {
      wire('pages')->error("Generate Pages - Error in query for checking if field configs exist in db");
      $count =  0;
    } else {
      $count = $query->rowCount();
    }

    return (int) $count;
  }


  /**
   * @param $name
   * @return bool
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
   * @param $field
   * @param $template
   * @return bool
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
          $this->error(sprintf($this->_('Generate Pages - Could not find table row for template: "%1$s", field: "%2$s", config: "%3$s"'), $template_name, $subject_name, $config_name));
          $q_result = false;
        }

      }

    }

    if($q_result) {
      $this->message(sprintf($this->_('Generate Pages - Config fields for: "%1$s": "%1$s" created in DB'), $template_name, $subject_name));
      return true;
    } else {
      $this->error(sprintf($this->_('Generate Pages - Config fields for: "%1$s": "%1$s" not created in DB'), $template_name, $subject_name));
      return false;
    }


  }

  /**
   * @param $field
   * @param $template
   * @return bool
   */
  public function deleteFieldConfigs($field, $template) {

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

      if(!$result) {
        $this->error(sprintf($this->_('Generate Pages - Could not find table row for template: "%1$s", field: "%2$s", config: "%3$s"'), $template_name, $subject_name, $config_name));
        $q_result = false;
      }

    }

    if($q_result) {
      $this->message(sprintf($this->_('Generate Pages - Config fields for: "%1$s": "%1$s" deleted in DB'), $template_name, $subject_name));
      return true;
    } else {
      $this->error(sprintf($this->_('Generate Pages - Config fields for: "%1$s": "%1$s" not deleted in DB'), $template_name, $subject_name));
      return false;
    }

  }


  /**
   * @param $old_name
   * @param $name
   * @return bool
   */
  public function templateRename($old_name, $name) {

    $rows = $this->checkTemplateConfigs($old_name);

    if ($rows) {

      $sql = "UPDATE `$this->table_name` " . "SET template_name='$name' " . "WHERE template_name='$old_name'";

      $query  = $this->database->prepare($sql);
      $result = $query->execute();

      if ($result) {
        $this->message(sprintf($this->_('Generate Pages - Template name in configs renamed from "%1$s" to "%2$s"'), $old_name, $name));
        return true;
      } else {
        $this->error(sprintf($this->_('Generate Pages - Template name in configs not renamed from "%1$s" to "%2$s"'), $old_name, $name));

        return false;
      }

    }

    $this->error(sprintf($this->_('Generate Pages - Config fields not found in database for template: "%s"'), $old_name));
    return false;

  }


  /**
   * @param $template_name
   * @return bool
   */
  public function templateClone($name, $new_name) {

    $sql = "CREATE TEMPORARY TABLE `gp_clone` SELECT * from `$this->table_name` WHERE template_name=:name; " .
        "ALTER TABLE `gp_clone` drop 'id'; " .
        "UPDATE `gp_clone` SET template_name=:new_name; " .
        "INSERT INTO `$this->table_name` SELECT * FROM `gp_clone`; " .
        "DROP TABLE `gp_clone`;";

    $query  = $this->database->prepare($sql);
    $query->bindValue(":name", $name);
    $query->bindValue(":new_name", $new_name);
    $result = $query->execute();

    if($result) {
      $this->message(sprintf($this->_('Generate Pages - Template: "%1$s" cloned in module configs to %2$s'), $name, $new_name));
      return true;
    } else {
      $this->error(sprintf($this->_('Generate Pages - Template: "%s" not cloned in module configs'), $name));
      return false;
    }

  }


  /**
   * @param $template_name
   * @return bool
   */
  public function templateDelete($template_name) {

    $sql = "DELETE FROM `$this->table_name` " .
          "WHERE template_name='$template_name'";

    $query  = $this->database->prepare($sql);
    $result = $query->execute();

    if($result) {
      $this->message(sprintf($this->_('Generate Pages - Template: "%s" deleted in configs'), $template_name));
      return true;
    } else {
      $this->error(sprintf($this->_('Generate Pages - Template: "%s" not deleted in configs'), $template_name));
      return false;
    }

  }


}