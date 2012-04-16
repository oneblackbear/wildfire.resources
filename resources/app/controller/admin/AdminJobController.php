<?
class AdminJobController extends AdminComponent{

  public $module_name = "job";
  public $model_class = 'Job';
  public $display_name = "Jobs";
  public $dashboard = false;
  public $filter_fields=array(
                          'text' => array('columns'=>array('title', 'id'), 'partial'=>'_filters_text', 'fuzzy'=>true),
                          'date_start' => array('columns'=>array('date_internal_testing', 'date_client_testing', 'date_go_live'), 'partial'=>"_filters_date", 'fuzzy_right'=>true)
                        );
}
?>