<?
class AdminDepartmentController extends AdminComponent{

  public $module_name = "department";
  public $model_class = 'Department';
  public $display_name = "Departments";
  public $dashboard = false;
  public $filter_fields=array(
                          'text' => array('columns'=>array('id', 'title', 'content'), 'partial'=>'_filters_text', 'fuzzy'=>true),
                          'organisations' => array('columns'=>array('organisations'), 'partial'=>'_filters_select', 'opposite_join_column'=>'departments')
                        );
}
?>