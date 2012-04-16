<?
class AdminClientController extends AdminComponent{

  public $module_name = "client";
  public $model_class = 'Client';
  public $display_name = "Clients";
  public $dashboard = false;
  public $filter_fields=array(
                          'text' => array('columns'=>array('id', 'title', 'content'), 'partial'=>'_filters_text', 'fuzzy'=>true),
                          'account_handler' => array('columns'=>array('account_handler'), 'partial'=>'_filters_select', 'opposite_join_column'=>'clients')
                        );
}
?>