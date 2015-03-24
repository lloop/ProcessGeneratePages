<?php

/**
 * An individual field configuration item to be part
 * of a FieldConfigArray for a Page
 *
 * Many configuration fields are applied to one subject field
 * The subject field will receive the content that is described by the
 * configuration field
 *
 * This will be an individual input that can be used in for
 * different fields like 'upper limit' 'lower limit'
 *
 * A FieldConfig object doesn't need to know about
 * the field it is supplying data about.
 * It only needs it's name
 * No field object , no field_id
 *
 */
class FieldConfig extends WireData {

	/**
	 * We keep a copy of the $page that owns this field configuration so that we can follow
	 * its outputFormatting state and change our output per that state
	 */
	protected $page;
  protected $field_id;
  protected $config_name;
  protected $template_name;
  protected $value;
  protected $label;



	/**
	 * Construct a new FieldConfig
	 *
	 */
	public function __construct() {

    // set default values
    $this->set('field_id', 0);
    $this->set('config_name', '');
    $this->set('template_name', '');
    $this->set('value', NULL);

  }

	/**
	 * Set a value to the field configuration
	 *
	 */
	public function set($key, $value) {

		if($key == 'page') {
			$this->page = $value;
			return $this;
		}

    if($key == 'field_id') {
      $this->field_id = $value;
      return $this;
    }

    if($key == 'config_name') {
      $this->config_name = $value;
      return $this;
    }

    if($key == 'template_name') {
      $this->template_name = $value;
      return $this;
    }

    if($key == 'value') {
      $this->value = $value;
      return $this;
    }

    if($key == 'label') {
      $this->label = $value;
      return $this;
    }

    $value = $this->sanitizer->text($value);

		return parent::set($key, $value); 
	}

	/**
	 * Retrieve a value from the field configuration
	 *
	 */
	public function get($key) {
		$value = parent::get($key); 

		// if the page's output formatting is on, then we'll return formatted values
//		if($this->page && $this->page->of()) {
//
//			if($key == 'date') {
//				// format a unix timestamp to a date string
//				$value = date(self::dateFormat, $value);
//
//			} else if($key == 'location' || $key == 'notes') {
//				// return entity encoded versions of strings
//				$value = $this->sanitizer->entities($value);
//			}
//		}

		return $value; 
	}


	/**
	 * Provide the default render item for a field in a configuration fieldgroup
	 *
	 */
	public function getItem() {

    // POST should be template[subjectFieldID][configFieldName][configFieldValue]
    // <input type='text' name="someName[0][value]">

    $checkbox = false;

    $item_id = $this->config_name . "--" . $this->field_id . "--" . $this->template_name;
    $item_name = $this->template_name . "[" . $this->field_id . "][" . $this->config_name . "]";

//    var_dump($item_name);

    switch($this->config_name) {
      case "probability" :
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $this->_('Entry Probability');
        $res->description = $this->_('What percentage is it likely for a value to be entered');
        $res->set('columnWidth', '35');
        break;

      case "conf_notes" :
        $res = wire('modules')->get("InputfieldTextarea");
        $res->label = $this->_('Notes');
        break;

      case "lower_limit" :
        $label = empty($this->label) ? 'Lower limit' : $this->label;
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $label;
        $res->set('columnWidth', '25');
        break;

      case "upper_limit" :
        $label = empty($this->label) ? 'Upper limit' : $this->label;
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $label;
        $res->set('columnWidth', '25');
        break;

      case "lower_size" :
        $label = empty($this->label) ? 'Lower size limit' : $this->label;
        $res = wire('modules')->get("InputfieldFloat");
        $res->label = $label;
        $res->set('columnWidth', '25');
        break;

      case "upper_size" :
        $label = empty($this->label) ? 'Upper size limit' : $this->label;
        $res = wire('modules')->get("InputfieldFloat");
        $res->label = $label;
        $res->set('columnWidth', '25');
        break;

      case "lower_width" :
        $label = empty($this->label) ? 'Lowest width' : $this->label;
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $this->_($label);
        $res->set('columnWidth', '15');
        break;

      case "lower_height" :
        $label = empty($this->label) ? 'Lowest height' : $this->label;
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $this->_($label);
        $res->set('columnWidth', '15');
        break;

      case "upper_width" :
        $label = empty($this->label) ? 'Highest width' : $this->label;
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $this->_($label);
        $res->set('columnWidth', '15');
        break;

      case "upper_height" :
        $label = empty($this->label) ? 'Highest height' : $this->label;
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $this->_($label);
        $res->set('columnWidth', '15');
        break;

      case "earliest_date" :
        $label = empty($this->label) ? 'Earliest date' : $this->label;
        $ramge = empty($this->range) ? '' : $this->range;
        $res = wire('modules')->get("InputfieldDatetime");
        $res->label = $this->_($label);
        $res->set('columnWidth', '25');
        $res->set('placeholder', '---');
        $this->set('yearRange', $ramge);
        $res->set('datepicker',1);
        break;

      case "latest_date" :
        $label = empty($this->label) ? 'Latest Date' : $this->label;
        $ramge = empty($this->range) ? '' : $this->range;
        $res = wire('modules')->get("InputfieldDatetime");
        $res->label = $this->_($label);
        $res->set('columnWidth', '25');
        $res->set('placeholder', '---');
        $this->set('yearRange', $ramge);
        $res->set('datepicker',1);
        break;

      case "date_dep_confirm" :
        $res = wire('modules')->get("InputfieldCheckbox");
        $res->label = $this->_('Dependant');
        $res->set('label2', "Is this date selector dependant on another date?");
        $res->set('columnWidth', '35');
        $res->attr('checkedValue', 'chkd');
        $res->attr('uncheckedValue', '!chkd');
        $checkbox = true;
        break;

      case "date_dep_length" :
        $label = empty($this->label) ? 'Dependancy length (in days)' : $this->label;
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $label;
        $res->set('columnWidth', '25');
        break;

      case "string_type" :
        $res = wire('modules')->get("InputfieldSelect");
        $res->label = $this->_('Random string type');
        $res->required = true;
        // TODO Also include paragraph option
        $types = array(
          "lorem_ipsum"           => "Lorem Ipsum",
          "lorem_ipsum_para"      => "Lorem Ipsum with paragraphs",
          "numbers_letters"       => "Numbers and letters",
          "numbers_letters_words" => "Numbers and letters with word spacing",
          "letters"               => "Letters",
          "letters_words"         => "Letters with word spacing",
          "numbers"               => "Numbers",
          "numbers_words"         => "Numbers with word spacing",
          "email_generator"       => "Email",
          "url_generator"         => "URL"
        );
        $res->addOptions($types);
        break;

      case "capitalization" :
        $res = wire('modules')->get("InputfieldSelect");
        $res->label = $this->_('Capitalization type');
        $res->required = true;
        $types = array(
          "no_caps"               => "None",
          "uppercase_words"       => "Uppercase words",
          "uppercase_first"       => "First word uppercase",
          "uppercase_all"         => "All Uppercase",
          "lowercase_all"         => "All lowercase",
        );
        $res->addOptions($types);
        break;

      case "prepend_confirm" :
        $res = wire('modules')->get("InputfieldCheckbox");
        $res->label = $this->_('Prepend');
        $res->set('label2', "Prepend an 'auto' string? (good for searches)");
        $res->set('columnWidth', '35');
        $res->attr('checkedValue', 'chkd');
        $res->attr('uncheckedValue', '!chkd');
        $checkbox = true;
        break;

      case "custom_confirm" :
        $res = wire('modules')->get("InputfieldCheckbox");
        // TODO set up showif - problem in js and hiding higher enclosing elements
        // $res->showIf = "prepend_confirm=1";
        $res->label = $this->_('Custom');
        $res->set('label2', 'Use a custom string?');
        $res->set('columnWidth', '25');
        $res->attr('checkedValue', 'chkd');
        $res->attr('uncheckedValue', '!chkd');
        $checkbox = true;
        break;

      case "custom_string" :
        $res = wire('modules')->get("InputfieldText");
        // TODO set up showif
        // $res->showIf = "custom_confirm=1";
        $res->label = $this->_('Custom prepend string');
        break;

      case "prepared_confirm" :
        $res = wire('modules')->get("InputfieldCheckbox");
        $res->label = $this->_('Prepared values');
        $res->set('label2', 'Use prepared values?');
        $res->set('columnWidth', '25');
        $res->attr('checkedValue', 'chkd');
        $res->attr('uncheckedValue', '!chkd');
        $checkbox = true;
        break;

      case "prepared_values" :
        $res = wire('modules')->get("InputfieldTextarea");
        // TODO set up showif
        // $custom_string->showIf = "prepared_confirm=1";
        $res->label = $this->_('Prepared values');
        $res->description = $this->_('A list of values to randomly select from. Each value to a line. Quotes and commas not necessary');
        break;

      case "normal_dev_confirm":
        $res = wire('modules')->get("InputfieldCheckbox");
        $res->label = $this->_('Normal deviation');
        $res->set('columnWidth', '25');
        $res->set('label2', 'Bell curve distribution');
        $res->attr('checkedValue', 'chkd');
        $res->attr('uncheckedValue', '!chkd');
        $checkbox = true;
        break;

      case "deviation_type" :
        $res = wire('modules')->get("InputfieldSelect");
        $res->label = $this->_('Deviation type');
        // TODO set up showif
        // $res->showIf = "normal_distro=1";
        $res->set('columnWidth', '25');
        $res->required = true;
        $types = array(
          "average"  => "Weighted average",
          "lowest"   => "Weighted lowest",
          "highest"  => "Weighted highest"
        );
        $res->addOptions($types);
        break;

      case "standard_deviation" :
        $res = wire('modules')->get("InputfieldInteger");
        $res->label = $this->_('Standard deviation');
        // TODO set up showif
        // $res->showIf = "normal_distro=1";
        $res->set('columnWidth', '25');
        break;

      case "extension_types" :
        $res = wire('modules')->get("InputfieldTextarea");
        $res->label = $this->_('Extension types');
        $res->description = $this->_('A list of extensions to randomly select from. Each value to a line. Quotes and commas not necessary');
        break;

      case "units" :
        $res = wire('modules')->get("InputfieldSelect");
        $res->label = $this->_('Units');
        $res->set('columnWidth', '25');
        $res->required = true;
        $types = array(
          "byte"      => "Bytes",
          "kilo"      => "Kilobytes",
          "meg"       => "Megabytess"
        );
        $res->addOptions($types);
        break;

      default :
        throw new WireException("Missing fieldconfig type: ". $this->config_name . " in class: " . __CLASS__);

    }

    // Assign the value (checkboxes are treated differently)
    if($checkbox) {
      $checked = $this->value === 'chkd' ? 'checked' : '';
      $res->attr('checked', $checked);
    } else {
      $res->attr("value", $this->value);
    }

    // Assigns that are on all types
    $res->set('name', $item_name);
    $res->set('id', $item_id);


    return $res;

//		// remember page's output formatting state
//		$of = $this->page->of();
//		// turn on output formatting for our rendering (if it's not already on)
//		if(!$of) $this->page->of(true);
//		$out = "<p><strong>$this->date</strong><br /><em>$this->location</em><br />$this->notes</p>";
//		if(!$of) $this->page->of(false);
//		return $out;
	}

	/**
	 * Return a string representing this field configuration
	 *
	 */
	public function __toString() {
		return $this->renderFieldConfig();
	}

}

