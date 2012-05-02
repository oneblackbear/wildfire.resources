<?
class StaffController extends BaseController{
  public $model_class = "Staff";
  public $form_name = "staff_form";
  public $filter_fields=array(
                          'text' => array('columns'=>array('title', 'email'), 'partial'=>'_filters_text', 'fuzzy'=>true),
                          'organisations' => array('columns'=>array('organisations'), 'partial'=>'_filters_select', 'opposite_join_column'=>'staff'),
                          'departments' => array('columns'=>array('departments'), 'partial'=>'_filters_select', 'opposite_join_column'=>'staff')
                        );
  public $permissions = array(
                          'index' => array('owner', 'admin', 'staff'),
                          'create'=>array('owner', 'admin'),
                          'edit'=>array('owner', 'admin'),
                          'delete'=>array('owner')
                        );

  /**
   * setup actions are used by the join controller pages
   */
  public function setup(){
    if(!$this->active_staff) $this->redirect_to("/");
    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to("/dash/");
    });
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

}
?>