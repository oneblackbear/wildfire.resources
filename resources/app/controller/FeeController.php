<?
class FeeController extends BaseController{
  public $name = "Fee";
  public $model_class = "Fee";
  public $filter_fields=array();
  public $permissions = array(
                            'index'=>array('owner', 'admin', 'privileged'),
                            'create'=>array('owner', 'admin', 'privileged'),
                            'edit'=>array('owner', 'admin', 'privileged'),
                            'details'=>array('owner', 'admin', 'privileged'),
                            'delete'=>array('owner', 'admin')
                          );


}
?>