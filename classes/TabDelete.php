<?php
/**
 *
 *
 * User: lloop
 * Date: 7/8/14
 * Time: 12:04 PM
 */

class TabDelete extends Tab {

  private $actions = array(
    'delete_autos'      => 'Delete Auto Generated',
    'delete_all'        => 'Delete All'
  );


  function __construct() {

    parent::__construct();

  }


  /**
   * Returns actions after checking permissions for the current user
   *
   * @access protected
   * @return $actions Array of Actions
   */
  private function getActions() {

    $actions = $this->actions;
    if (!wire('user')->hasPermission('page-delete')) unset($actions['delete']);
    if (!wire('user')->hasPermission('page-lock')) {
      unset($actions['lock']);
      unset($actions['unlock']);
    }
    return $actions;

  }


  /**
   * Build the delete tab
   *
   * @param InputfieldWrapper $wrapper
   */
  public function buildDeleteTab(InputfieldForm $form) {

    $form->attr('name', 'delete');

    // buildEdit() is in Tab class
    $fieldset = $this->buildEdit('delete', 'del');

    $form->append($fieldset);

    // Pulldown for actions
    $action = wire('modules')->get('InputfieldSelect');
    $action->label = 'Action';
    $action->attr('name+id', 'action');
    $action->required = true;
    $action->addOptions($this->getActions());
    $form->append($action);

    // Set up the submit button
    $button = wire('modules')->get("InputfieldSubmit");
    $button->attr('name', 'submit_delete');
    $button->attr("value", "Delete");
    $button->attr("id", "submit_delete");
    $form->append($button);

    return $form;

  }


  /**
   *
   *
   * @return bool
   */
  public function execute() {
    $post = wire('input')->post;
    $par_id = $post->del_parent_id;
    $temp_id = $post->del_template_id;
    $action = $post->action;

    if( $par_id === '0' && $temp_id === '') {
      wire('session')->error('No template or page chosen');  // wire('pages')?????
      wire('session')->redirect("./");  // wire('pages')?????
      return false;
    }

    $valid_pages = $this->validPages($par_id, $temp_id);

    $amount = $this->deleteSubmit($valid_pages, $action);

    $this->outcomeMessages($amount, $action);

    return true;

  }


  /**
   * Switch statement for different actions
   *
   * @param $valid_pages
   * @param $action
   * @throws WireException
   * @return int
   */
  private function deleteSubmit($valid_pages, $action) {

    // Action
    switch($action) {

      case 'delete_autos':
        $module_user_id = wire('users')->get(ProcessGeneratePages::USER_NAME)->id;
        $filtered_pages = $valid_pages->find('createdUser=' . $module_user_id);
        $amount = $this->deletePages($filtered_pages);
        break;

      case 'delete_all':
        $amount = $this->deletePages($valid_pages);
        break;

      default:
        throw new WireException("Invalid action description in delete submit");
    }

    return $amount;

  }


  /**
   *
   *
   * @param $outcome
   * @param $action
   */
  private function outcomeMessages($amount=0, $action) {

    $desc = $action === 'delete_autos' ? " auto generated " : "";

    $p = $amount > 1 || $amount === 0 ? "pages" : "page";
    wire('pages')->message($amount . $desc . $p . ' successfully deleted.');

  }


  /**
   *
   *
   * @param $par_id
   * @param $temp_id
   * @return PageArray
   */
  private function validPages($par_id, $temp_id) {

    $parent_selector = ($par_id === '0') ? '' : 'parent=' . $par_id;
    $template_selector = ($temp_id === '') ? '' : 'template=' . $temp_id;
    $joiner = ($par_id === '0' || $temp_id === '') ? '' : ', ';

    $selector = $parent_selector . $joiner . $template_selector;

    $results = wire('pages')->find($selector);

    return $results;

  }


  /**
   * Delete Pages
   *
   * @param $pges
   * @return int
   */
  private function deletePages($pges) {

    $result = 0;

    foreach($pges as $p) {
      // TODO What to do when page has children. delete() has a recursion argument.
      $deleted = wire('pages')->delete($p);
      if ($deleted) {
        $result++;
      } else {
        wire('pages')->error("A problem encountered deleting page: " . $p->id);
      }
    }

    return $result;

  }

}