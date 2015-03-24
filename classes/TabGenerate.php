<?php
/**
 *
 *
 * User: lloop
 * Date: 7/8/14
 * Time: 12:23 PM
 */

class TabGenerate extends Tab {

  /**
   *
   *
   */
  public function __construct() {

    parent::__construct();

  }


  /**
   * Build the generate tab
   *
   * @access private
   * @param InputfieldWrapper $wrapper
   */
  public function buildGenerateTab(InputfieldForm $form) {

    $form->attr('name', 'generate');

    // buildEdit() is in Tab class
    $fieldset = $this->buildEdit('generate', 'gen');

    $form->append($fieldset);

    // Integer input for amount of paged to generate
    $field = wire('modules')->get('InputfieldInteger');
    $field->label = 'Amount new';
    $field->attr('name', 'amount');
    $field->attr('value', 1);
    $form->append($field);

    // Submit button
    $button = wire('modules')->get("InputfieldSubmit");
    $button->attr('name', 'submit_generate');
    $button->attr("value", "Generate");
    $button->attr("id","submit_generate");
    $form->append($button);

    return $form;

  }


  /**
   * @return bool
   */
  public function execute() {

    $post = wire('input')->post;
    $amount = $post->amount;
    $par_id = $post->gen_parent_id;
    $temp_id = $post->gen_template_id;

    // (eventually the selector and php inputs)

    if( $par_id === '0' && $temp_id === '') {
      wire('session')->error('No template or parent chosen');
      wire('session')->redirect("./");
      return false;
    }

    return $this->generatePages($par_id, $temp_id, $amount );

  }



  /**
   *
   * @param int $amount
   * @param $template_name
   * @return bool
   */
    private function generatePages($par_id, $temp_id, $amount) {

      $result = true;

      // Always need to come up with arrays of page and template objects
      // should start these out as a PageArray and a WireArray
      $parents = new PageArray();
      $templates = new TemplatesArray();

      // if parent page is set
      if ($par_id !== '0') {

        // Use find to maintain a PageArray instead of a single page
        $pge = wire('pages')->get($par_id);
        $parents->add($pge);

        // Get a WireArray of child templates of the parent page
        // Selector is changed if template is set also
        $child_templates = $parents[0]->template->childTemplates;
        $selector = ($temp_id === '') ? implode('|', $child_templates) : $temp_id;
        $ts = wire('templates')->find('id=' . $selector);
        $templates->add($ts);

        // Check to see if the template chosen is a child template of the page chosen
        // TODO write an automatic traversal to find descendants with the template
        // so that a page can be used to isolate several descendant parent pages
        if ($temp_id !== '' && !in_array($temp_id, $child_templates)) {
          wire('session')->error('Chosen template is not a child of the chosen page');
          wire('session')->redirect("./");
          return false;
        }

      } else {

        // Else no parent and only a template chosen
        // TODO access the db directly through a mysql statement to retrieve the templates

        $pars = new PageArray();
        $p_templates = new WireArray();

        // Find the parent templates that have this template as a child
        foreach (wire('templates') as $t){
          if(in_array((int)$temp_id, $t->childTemplates)) $p_templates->add($t);
        }

        // Then find all the pages that use those templates
        foreach ($p_templates as $p_t) {
          $ps = wire('pages')->find('template=' . $p_t);
          $pars->add($ps);
        }
        $parents = $pars->unique();

        $ts = wire('templates')->find('id=' . $temp_id);
        $templates->add($ts);

      }

      // Check for empty $parents or $templates
      if (count($parents) === 0 || count($templates) === 0) {
        wire('session')->error('Parent page has no child templates or template has no parent pages');
        wire('session')->redirect("./");
        return false;
      }

      // For each page to generate
      for ($i = 1; $i <= $amount; $i++) {

        // Get randomly selected template and parent
        $template = $templates->getRandom();
        $parent = $parents->getRandom();

        $generated = $this->generatePage($parent, $template, $i);

        // Message of success or fail
        if($generated) {
          wire('pages')->message("Page generated for template:" . $template->name . " --- path: " . $generated['path'] );
        } else {
          wire('pages')->error("Page not generated for template:" . $template->name . " --- path: " . $generated['path'] );
          $result = false;
        }

      }

      return $result;

  }


  /**
   * Generate a single page
   *
   * Need to iterate once through the fields of the page and remove all the fields that
   * are in (only in?) a repeat field.
   * These should be calculated when the repeats are calculated.
   *
   * @param $parents
   * @param $batch_id
   * @param $template
   * @return bool
   */
  private function generatePage(Page $parent, Template $template, $batch_id) {

    $fieldgroup = $template->fields;
    $subj_fields = $this->validateFields($fieldgroup);
    $pge_fields = array();
    $image_fields = array();
    $repeater_fields = array();
    $success = true;

    // Iterate through the fields once.
    // Copy the image (and file?) fields into an array to be used later
    foreach ($subj_fields as $sf) {
      $type = $sf->type->className;

      // Image field
      if ($type === 'FieldtypeImage') {
        array_push($image_fields, $sf);
        $key = array_search($sf, $subj_fields);
        unset($subj_fields[$key]);
        continue;
      }

      // Repeater field
      if ($type === 'FieldtypeRepeater') {
        array_push($repeater_fields, $sf);
        $key = array_search($sf, $subj_fields);
        unset($subj_fields[$key]);
        continue;
      }

    }

    // NICE LITTLE WINDOW INTO THE FIELDS THAT HAVE BEEN REMOVED
    //$arr = array();
    //foreach($subj_fields as $v) {
    //  $arr[] = $v->name;
    //}

    // For each subject field in the template. Fill out the pge_fields array
    foreach ($subj_fields as $f) {

      $values = $this->getFieldDbValue($f->id, $template->name);

      // Instantiate the generator for this type
      $field_gen = GeneratorFactory::create($f);

      // Generate the content according to the field type config data
      $field_content = $field_gen->genFieldContent($values, $batch_id);

      // Getting the array filled out to assign to the new page before a save
      $pge_fields[$f->name] = $field_content;

    }

    // New page
    $p = new Page();

    // Name
    $title_name = $pge_fields['title'];

    // Make sure the name isn't already used
    $p->name = $this->uniquePageName($title_name);

    // Set the template
    $p->template = $template->id;

    // Set the parent
    $p->parent = $parent;

    // Assign the field values
    foreach($pge_fields as $key=>$field) {
      $p->$key = $pge_fields[$key];
    }

    if(wire("modules")->isInstalled("LanguageSupportPageNames")) {
      // TODO alter the field inputs( add a word after first word in order to preserve alphabetical lists )
      // TODO add an option for languages in config. Also check field for type/language compatability
      $p->status1082 = 1;
    }

    // Save the page before the repeater in case a repeater has a fieldtypeFile in it
    $p->save();

    // Generate the repeater fields
    // TODO uncouple the setPage($p). perform the page->save here
    foreach ($repeater_fields as $field) {

      $values = $this->getFieldDbValue($field->id, $template->name);

      // Returns an instance of PGeneratorRepeater
      $repeater_gen = GeneratorFactory::create($field);
      $repeater_gen->setPage($p);
      $repeater_gen->setTemplate($template->name);

      $repeater_gen->genFieldContent($values, $batch_id);

    }

    // Save the page
    $p->save();

    // Image fields
    // TODO uncouple the setPage($p). perform the page->save here
    foreach ($image_fields as $image_field) {

      $values = $this->getFieldDbValue($image_field->id, $template->name);

      $image_gen = GeneratorFactory::create($image_field);
      $image_gen->setPage($p);
      $image_gen->setTemplate($template->name);
      $image_gen->genFieldContent($values, $batch_id);

    }

    // Set the user created
    $module_id = wire('users')->get(ProcessGeneratePages::USER_NAME)->id;
    $page_id = $p->id;
    $sql = "UPDATE `pages` SET `created_users_id` = $module_id WHERE `id` = $page_id ";
    $update = wire('db')->query($sql);

    return array(
      'success'    => $success,
      'path'   => $p->path
    );

  }

  /**
   * @param $name
   * @return mixed
   */
  private function uniquePageName($name) {

    // Todo the name is run through the sanitizer a second time when it finds a double and reciprocates
    $name = wire('sanitizer')->pageName($name, true);

    $exists = wire('pages')->find('name='.$name);

    if (count($exists) > 0) {
      $name = $this->uniquePageName($name);
    }

    return $name;
  }

}

