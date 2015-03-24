<?php
/**
 *
 *
 * User: lloop
 * Date: 7/9/14
 * Time: 1:50 PM
 */

class PGeneratorText extends PGenerator implements GeneratorStyle {

  /**
   *
   *
   */
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
      case "name": // Generic name. Page with no title field
        $content = $this->genName($batch_id);
        break;
      case "FieldtypePageTitle":
      case "FieldtypePageTitleLanguage":
        $content = $this->genFieldtypeTitleText($values, $batch_id);
        break;
      case "FieldtypeText" :
      case "FieldtypeTextLanguage":
        $content = $this->genFieldtypeText($values, $batch_id);
        break;
      case "FieldtypeTextarea":
      case "FieldtypeTextareaLanguage":
        $content = $this->genFieldtypeText($values, $batch_id);
        break;
      case "FieldtypeComments":
        $content = $this->genFieldtypeComments($values, $batch_id);
        break;
      case "FieldtypeEmail":
        $content = $this->genFieldtypeEmail();
        break;
      case "FieldtypeURL":
        $content = $this->genFieldtypeURL();
        break;
      default:
        throw new WireException("Invalid field type: " . $this->subject_field->type . " in PGeneratorText class");
    }

    return $content;
  }


  /**
   * Returns a generated title that is also sanitized and used for the name field
   *
   * @param $values
   * @param $batch_id
   * @return string
   */
  private function genFieldtypeTitleText($values, $batch_id) {

    $prepend = "";

    // Prepend
    if ($values['prepend_confirm'] === '1') {
      if ($values['custom_confirm'] === '1') {
        $prepend = $values['custom_string'];
      } else {
        $prepend = $this->prependGen($batch_id);
      }
    }

    // String values
    if ($values['prepared_confirm'] == 'yes') {
      $prepared_values = preg_split('/\r\n|\r|\n/', $values['prepared_values']);
      $prep_count = count($prepared_values);
      $string = $prepared_values[mt_rand(0, $prep_count)];
    } else {
      $string = $this->stringGen($values);
    }

    $content = $prepend . $string;

    return $content;

  }


  /**
   * @param $values
   * @param $batch_id
   * @return string
   */
  private function genFieldtypeText($values, $batch_id) {

    // Declare the prepend
    $prepend = "";
    $content = "";

    // Probability
    if(array_key_exists('probability', $values)) {
      $probability = mt_rand(1, 100);
      if ($probability > $values['probability']) return $content;
    }

    // Prepend
    if ($values['prepend_confirm'] === 'chkd') {
      if ($values['custom_confirm'] === 'chkd') {
        $prepend = $values['custom_string'];
      } else {
        $prepend = $this->prependGen($batch_id);
      }
    }

    // Prepared values
    if ($values['prepared_confirm'] == 'chkd') {
      $prepared_values = json_decode($values['prepared_values']);
      $prep_count = count($prepared_values);
      $string = $prep_count > 0 ? $prepared_values[mt_rand(0, $prep_count-1)] : "No prepared values available";
    } else {
      $string = $this->stringGen($values);
    }

    $content = $prepend . $string;

    return $content;
  }


  /**
   * Generate a string according to the values
   * @param $values
   * @return string
   * @throws WireException
   */
  public function stringGen($values) {

    // String length
    $min = $values['lower_limit'];
    // Set max to max or to min if fixed_confirm is checked
    $max = $values['upper_limit'];
    // Subtract the prepend from the length(if it applies)
    // todo flesh it out
    if ($values['prepend_confirm'] === 1) {
      $max -= $values['custom_confirm'] === 1 ? strlen($values['custom_string']) : 20;
    }

    // String type
    switch ($values['string_type']) {
      case 'lorem_ipsum':
        $str = ContentLipsum::generate($min, $max);
        break;
      case 'lorem_ipsum_para':
        $str = ContentLipsum::generate($min, $max, true);
        break;
      case 'numbers_letters':
        $str = $this->generateString($min, $max);
        break;
      case 'numbers_letters_words':
        $str = $this->generateString($min, $max, true, true, true);
        break;
      case 'letters':
        $str = $this->generateString($min, $max, true, false, false);
        break;
      case 'letters_words':
        $str = $this->generateString($min, $max, true, false, true);
        break;
      case 'numbers':
        $str = $this->generateString($min, $max, false, true, false);
        break;
      case 'numbers_words':
        $str = $this->generateString($min, $max, false, true, true);
        break;
      case 'email_generator':
        $str = $this->generateEmail();
        break;
      case 'url_generator';
        $str = $this->generateUrl();
        break;
      default:
        throw new WireException("Invalid string type: " . $values['string_type'] . " in " . __CLASS__ . " class");
    }

    // Capitalization
    switch ($values['capitalization']) {
      case "no_caps":
        $string = $str;
        break;
      case "uppercase_words":
        $string = ucwords(strtolower($str));
        break;
      case "uppercase_first":
        $string = ucfirst(strtolower($str));
        break;
      case "uppercase_all":
        $string = mb_strtoupper($str);
        break;
      case "lowercase_all":
        $string = strtolower($str);
        break;
      default:
        throw new WireException("Invalid capitalization type: " . $values['capitalization'] . " in " . __CLASS__ . " class");
    }

    return $string;

  }


  /**
   * Generates an array of prepends for title and name
   *
   * @param $batch_id
   * @return mixed
   */
  private function prependGen($batch_id) {

    // Generate a random letter for alphabetical lists of titles
    $letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    $letter = $letters[mt_rand(0, 25)];

    // The prepend string components
    $date = date("Y-m-d H:i:s");
    $prepend = $letter .  "_Auto_" . $batch_id . ": " . $date . " - ";

    return $prepend;

  }


  /**
   * @param $values
   * @param $batch_id
   * @return string
   */
  private function genFieldtypeComments($values, $batch_id) {

    // Declare the prepend array
    $prepend = "";
    $content = "";

    // Probability
    $probability = mt_rand(1, 100);
    if ($probability > $values['probability']) return $content;

    // Prepend
    if ($values['prepend_confirm'] === 1) {
      if ($values['custom_confirm'] === 1) {
        $prepend = $values['custom_string'];
      } else {
        $prepend = $this->prependGen($batch_id);
      }
    }

    // Prepared values
    if ($values['prepared_confirm'] == 'yes') {
      $prepared_values = preg_split('/\r\n|\r|\n/', $values['prepared_values']);
      $prep_count = count($prepared_values);
      $string = $prepared_values[mt_rand(0, $prep_count)];
    } else {
      $string = $this->stringGen($values);
    }

    $content = $prepend . $string;

    return $content;

  }


  /**
   * @param $values
   * @param $batch_id
   */
  private function genFieldtypeEmail() {

    $lorem_ipsum = new ContentLipsum();

    // Generate name
    $length = mt_rand($min, $max);
    $numb = '0123456789';
    $lett = 'abcdefghijklmnopqrstuvwxyz';
    $characters = $numb . str_repeat($lett, 6);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
      $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    // Generate site
    $site = $lorem_ipsum->generate(1, 1);

    // Domain
    $domains = array('.com', '.org', '.edu', '.gov', '.net', 'info');
    $top_level = $domains[mt_rand(1, count($domains))];

    // Concat
    $email =  $name . '@' . $site . "." . $top_level;

    return $email;

  }


  /**
   * @param $values
   * @param $batch_id
   */
  private function genFieldtypeUrl() {
    $lorem_ipsum = new ContentLipsum();

    // Generate site
    // todo add min and max to the field config
    $min = 10;
    $max = 50;
    $length = mt_rand($min, $max);
    $numb = '0123456789';
    $lett = 'abcdefghijklmnopqrstuvwxyz';
    $characters = $numb . str_repeat($lett, 8);
    $site = '';
    for ($i = 0; $i < $length; $i++) {
      $site .= $characters[rand(0, strlen($characters) - 1)];
    }

    // Domain
    $domains = array('.com', '.org', '.edu', '.gov', '.net', 'info');
    $top_level = $domains[mt_rand(0, count($domains) - 1)];

    // Folders
    $folder_depth = mt_rand(0, 5);
    $f_string = "";
    for($i = 0; $i <= $folder_depth; $i++) {
      $fold = $lorem_ipsum->generate(1, 1);
      $f_string .= "/" . $fold;
    }

    // Concat
    $url =  "http://" . $site . "." . $top_level . $f_string;

    return $url;

  }

}