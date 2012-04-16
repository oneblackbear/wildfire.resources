<?
class AdminStaffController extends AdminComponent{

  public $module_name = "staff";
  public $model_class = 'Staff';
  public $display_name = "Staff";
  public $dashboard = false;
  public $filter_fields=array(
                          'text' => array('columns'=>array('id', 'title', 'content'), 'partial'=>'_filters_text', 'fuzzy'=>true),
                          'departments' => array('columns'=>array('departments'), 'partial'=>'_filters_select', 'opposite_join_column'=>'staff'),
                          'organisations' => array('columns'=>array('organisations'), 'partial'=>'_filters_select', 'opposite_join_column'=>'staff')
                        );
}
?>