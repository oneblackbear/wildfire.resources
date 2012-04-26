<?
class WorkController extends BaseController{
  public $model_class = "Work";
  public $form_name = "work_form";
  public $name = "Work";
  public $filter_fields=array(
                          'text' => array('columns'=>array('title'), 'partial'=>'_filters_text', 'fuzzy'=>true),
                          'staff' => array('columns'=>array('staff'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'departments' => array('columns'=>array('departments'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'clients' => array('columns'=>array('clients'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'jobs' => array('columns'=>array('job'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work')
                        );
  public $navigation_links = array('index', 'create');
  public $permissions = array(
                          'create'=>array('owner', 'admin'),
                          'edit'=>array('owner', 'admin'),
                          'delete'=>array('owner', 'admin'),
                          'listing'=>array('owner')
                        );



  public function listing(){
    parent::index();
  }
}
?>