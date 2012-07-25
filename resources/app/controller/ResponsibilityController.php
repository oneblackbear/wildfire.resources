<?
class ResponsibilityController extends BaseController{
  public $model_class = "Responsibility";
  public $form_name = "responsibility_form";
  public $per_page = 100;
  public $filter_fields=array(
                          'staff' => array('columns'=>array('staff'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'client' => array('columns'=>array('client'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                        );
  public $permissions = array(
                          'create'=>array('owner'),
                          'edit'=>array('owner', 'admin'),
                          'delete'=>array('owner'),
                          'index'=>array('owner', 'admin', 'staff')
                        );


}
?>