<?php
/**
 *
 *
 * TODO Don't really like passing the page object by reference to this class and '$this->page->save()'. Not coupled well.
 *
 * User: lloop
 * Date: 7/12/14
 * Time: 11:44 AM
 */

class PGeneratorFile extends PGenerator implements GeneratorStyle {


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

    // Size units
    $units = $values['units'];

    // Generate random size
    $low_size = $values['lower_size'];
    $high_size = $values['upper_size'];

    // Calculate the places to the right of the decimal point
    $low_places = strlen(substr(strrchr($low_size, "."), 1));
    $high_places = strlen(substr(strrchr($high_size, "."), 1));

    // If there is no decimal point then the places is zero
    if($low_places || $high_places) {

      // todo Figure out what to do when one is decimal and one is not

      // Calculate the maximum decimal places of the two values
      $decimal_places = max($low_places, $high_places);

      $temp = 10 * $decimal_places;
      // Multiply both by ten times the places
      $pre_low  = $low_size * $temp;
      $pre_high = $high_size * $temp;
      // Get the random in between and divide back
      $size = mt_rand($pre_low, $pre_high) / $temp;

      $new_file_size = $this->sizeToBytes($size, $units);

    } else {

      $size = mt_rand($low_size, $high_size);
      $new_file_size = $this->sizeToBytes($size, $units);

    }

    // Randomly select a file extension
    $ext_values = preg_split('/\r\n|\r|\n/', $values['extension_types']);
    $ext_count = count($ext_values);
    $ext_value = $ext_values[mt_rand(0, $ext_count-1)];

    // Generate the file and return the path (and name)
    $file_path = $this->createFile($ext_value, $new_file_size, $batch_id );

    // Add the file to the page
    $this->page->save();

    return $file_path;

  }


  /**
   * Create dummy file
   *
   * @return boolean True if file created successfully
   */
  public function createFile($ext, $size, $batch_id) {

    $file_path = dirname(__FILE__) . "/../files/auto-" . $batch_id . "-" . time() . "." . $ext;

    // todo Uncomment when the bugs are fixed
    if ($fh = fopen($file_path, 'w')) {
      fwrite($fh, str_repeat('0', $size), $size);
      fclose($fh);
    }

    return $file_path;

  }


  /**
   * Convert size to bytes
   *
   * @param string $size File size
   * @param $units
   * @return integer Bytes
   */
  private function sizeToBytes($size, $units) {

    switch ($units) {
      case 'byte': return round($size);
      case 'kilo': return round($size * 1024);
      case 'meg':  return round($size * 1024 * 1024);
      default:     return 0;
    }

  }


}