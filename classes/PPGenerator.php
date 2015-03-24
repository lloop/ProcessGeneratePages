<?php
/**
 *
 *
 * User: lloop
 * Date: 7/12/14
 * Time: 11:36 AM
 */

class PGenerator extends Tab {

  protected $field;
  protected $page;
  protected $template;

  /**
   *
   *
   */
  public function __construct($field) {

    $dir = dirname(__FILE__);
    require_once($dir . "/ContentLipsum.php");

    $this->field = $field;

  }

  /**
   * @param $page
   */
  public function setPage($page) {
    $this->page = $page;
  }

  /**
   * @param $page
   */
  public function setTemplate($template) {
    $this->template = $template;
  }


  /**
   * Random number functions are here because they are used by many generators
   * and not necessarily exclusive to the number generators
   */

  /**
   * Generate a random number with normal distribution
   *
   * @param $min
   * @param $max
   * @param $std_deviation
   * @param int $step
   * @return float
   */
  public function randNormDev($min, $max, $step=1, $std_dev=null) {

    $std_deviation = is_null($std_dev) ? ($max - $min)/6 : $std_dev;

    $rand1 = (float)mt_rand()/(float)mt_getrandmax();
    $rand2 = (float)mt_rand()/(float)mt_getrandmax();
    $gaussian_number = sqrt(-2 * log($rand1)) * cos(2 * M_PI * $rand2);
    $mean = ($max + $min) / 2;
    $random_number = ($gaussian_number * $std_deviation) + $mean;
    $random_number = round($random_number / $step) * $step;
    if($random_number < $min || $random_number > $max) {
      $random_number = $this->randNormDev($min, $max, $step, $std_deviation);
    }

    return $random_number;

  }

  /**
   * Exponentially weighted random number generator.
   * Generates random numbers throughout the base formula
   * f(x)=x^a where x is a random number and a is the weight
   * The function inputs are $min, $max, and $average.
   * Returns a random float between $min and $max
   * If run multiple times the average should be near $average.
   */
  public function randWeighted($min, $max, $average) {

    //the origional exprand didn't accept $min
    //the next 2 lines and the second $resault line add $min functionality
    $max -=$min;
    $average -=$min;

    //calculating exponent
    $exp = ($max/$average)-1;
    //calculating max value for mt_rand
    $maxrand = pow($max,(1/$exp));

    //mt_rand returns integers
    //rounding errors were causing major problems
    //next 3 lines of code generate a random number with accuracy to the millionth
    $maxrand *= 1000000;
    $rand = mt_rand(0,$maxrand);
    $rand /= 1000000;

    //applying curve to $rand
    $resault = pow($rand,$exp);

    //add $min back in
    $resault += $min;

    return $resault;

  }


  /**
   * Randomly generates a weighted integer between two other (specified) integers (which can include them).
   * Can be used for getting random indexes from an array, where the lower the index,
   * the higher the chances of it being selected. Passing a value of 1 will give all
   * numbers in the range an equal chance of being selected. A value of 2 should be
   * sufficient in most instances. If you want high numbers to occur less often, use higher values.
   */
  public function randWeightedBottom($min, $max, $factor){
    return round($min + (pow(rand(0, $max) / $max, $factor) * ($max - $min)));
  }


  /**
   * Generates a random number that is not alreadt in the array
   *
   * @param $min
   * @param $max
   * @param $arr
   * @return int
   */
  public function randUniqueNumber($min, $max, $arr) {

    $number = rand($min, $max);
    if(in_array($number, $arr)) {
      $this->randUniqueNumber($min, $max, $arr);
    }

    return $number;

  }


  /**
   * Generates a random string with the letters weighted 5:1 against numbers.
   * Used by various generators for titles so not in the text generators.
   *
   * @param int $length
   * @param bool $spaces
   * @return string
   */
  public function generateString($min, $max, $letters=true, $numbers=true, $spaces=false) {

    $length = mt_rand($min, $max);
    $numb = $numbers ? '0123456789' : '';
    $lett = $letters ? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' : '';
    $sp = $spaces ? "        " : "";
    $characters = $numb . str_repeat($lett, 5) . $sp;
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
      $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;

  }


}