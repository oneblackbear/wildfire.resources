<?
class ResponsibilityController extends BaseController{
  public $model_class = "Responsibility";
  public $form_name = "responsibility_form";
  public $permissions = array(
                          'create'=>array('owner'),
                          'edit'=>array('owner', 'admin'),
                          'delete'=>array('owner'),
                          'index'=>array('owner', 'admin', 'staff')
                        );


}
?>