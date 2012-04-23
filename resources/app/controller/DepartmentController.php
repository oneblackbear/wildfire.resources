<?
class DepartmentController extends BaseController{
  public $model_class = "Department";
  public $form_name = "deparment_form";
  public $permissions = array(
                          'create'=>array('owner'),
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
      $controller->active = false;
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to("/staff/setup/");
    });
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }
}
?>