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

class PGeneratorImage extends PGenerator implements GeneratorStyle {


  public function __construct($field) {

    parent::__construct($field);

  }


  /**
   * @param $type
   * @param $values
   * @return mixed
   * @throws WireException
   */
  public function genFieldContent($values, $file_batch_id) {

    $images_arr = array();

    // Probability
    $probability = mt_rand(1, 100);
    if ($probability > $values['probability']) return false;

    $dims = array(
      'min_w' => $values['lower_width'],
      'min_h' => $values['lower_height'],
      'max_w' => $values['upper_width'],
      'max_h' => $values['upper_height']
    );

    $images_amount = mt_rand($values['lower_limit'], $values['upper_limit']);

    for($i = 1; $i <= $images_amount; $i++) {
      $image_path = $this->generateImage($dims, $file_batch_id, $i);

      // Create the array in order to delete the original images after the upload
      array_push($images_arr, $image_path);

      // Add the image to the page
      $this->page->images->add($image_path);

      // Generate a description
      $descrip = ContentLipsum::generate(5, 20);

      // Add a description to the last image
      $this->page->images->last()->description = $descrip;

    }

    $this->page->save();


    // Delete the original images
    $success = true;
    foreach($images_arr as $image) {
      $deleted = unlink($image);
      if ($deleted === false) $success = false;
    }
    if ($success === true) {
      wire('pages')->message("Temporary images successfully deleted.");
    } else {
      wire('pages')->error("Temporary images not deleted.");
    }

    return true;

  }


  /**
   * Generates a jpeg of random size
   *
   */
  protected function generateImage($dims, $file_batch_id, $batch_id) {

    $min_w = $dims['min_w'];
    $max_w = $dims['max_w'];
    $min_h = $dims['min_h'];
    $max_h = $dims['max_h'];

    $width = $this->randNormDev($min_w, $max_w, 100);
    $height = $this->randNormDev($min_h, $max_h, 100);
    $ratio = round($width/$height, 2);

    $title_dimens = "-" . $height . "X" . $width;
    $temp_path = wire("config")->paths->assets . "gen_pages_temp";
    $title_path = $temp_path . "/auto-" . $file_batch_id . "_" . $batch_id . "-" . time() . $title_dimens . ".jpg";
    $my_img = imagecreatetruecolor($width, $height);
    $background = imagecolorallocate( $my_img, 0, 0, 0 );
    $shape_1_color = imagecolorallocate($my_img, rand(0, 255), rand(0, 255), rand(0, 255));
    $shape_2_color = imagecolorallocate($my_img, rand(0, 255), rand(0, 255), rand(0, 255));
    $shape_3_color = imagecolorallocate($my_img, rand(0, 255), rand(0, 255), rand(0, 255));
    $shape_4_color = imagecolorallocate($my_img, rand(0, 255), rand(0, 255), rand(0, 255));
    $circle_color = imagecolorallocate($my_img, rand(0, 255), rand(0, 255), rand(0, 255));

    $text_color = imagecolorallocate($my_img, 255, 255, 255);
    $text = $height . " X " . $width;
    $font_path = wire("config")->paths->siteModules . "ProcessGeneratePages/images/Cent_Got.ttf";
    $font_size = $ratio < 1 ? $width/20 : $height/20;

    $line_thickness = round(($width + $height) / 4);
    imagesetthickness($my_img, $line_thickness);
    // imagefilledrectangle ( resource $image , int $x1 , int $y1 , int $x2 , int $y2 , int $color )
    // 0, 0 is the top left corner of the image.
    imagefilledrectangle($my_img, 0, 0, $width/2, $height/2, $shape_1_color); // upper left
    imagefilledrectangle($my_img, $width/2, 0, $width, $height/2, $shape_2_color); // upper right
    imagefilledrectangle($my_img, 0, $height/2, $width/2, $height, $shape_3_color); // lower left
    imagefilledrectangle($my_img, $width/2, $height/2, $width, $height, $shape_4_color); // lower right
    $diam = $width < $height ? $width - 10 : $height -10;
    imageellipse($my_img, $width/2, $height/2, $diam, $diam, $circle_color);
    imagettftext($my_img, $font_size, 0, $font_size + 100, 150, $text_color, $font_path, $text);

    imagejpeg($my_img, $title_path);
    imagecolordeallocate($my_img, $background);
    imagecolordeallocate($my_img, $shape_1_color);
    imagecolordeallocate($my_img, $shape_2_color);
    imagecolordeallocate($my_img, $shape_3_color);
    imagecolordeallocate($my_img, $shape_4_color);
    imagecolordeallocate($my_img, $circle_color);
    imagedestroy($my_img);

    return $title_path;

  }


}