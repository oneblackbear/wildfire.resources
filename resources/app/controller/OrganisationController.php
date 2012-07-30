<?
class OrganisationController extends BaseController{
  public $model_class = "Organisation";
  public $form_name = "organisation_form";
  public $permissions = array(
                          'create'=>array('owner', 'admin', 'staff'),
                          'edit'=>array('owner', 'admin', 'staff'),
                          'delete'=>array('owner'),
                          'index'=>array('owner', 'admin', 'staff')
                        );

  /**
   * setup actions are used by the join controller pages
   */
  public function setup(){
    if(!$this->active_staff) $this->redirect_to("/?no-user");
    //after save of the form, join to the user and redirect to department
    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to("/department/setup/");
    });
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }
}
?>