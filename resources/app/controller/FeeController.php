<?
class FeeController extends BaseController{
  public $name = "Fee";
  public $model_class = "Fee";
  public $filter_fields=array();
  public $permissions = array(
                            'index'=>array('owner', 'admin', 'staff'),
                            'create'=>array('owner', 'admin', 'staff'),
                            'edit'=>array('owner', 'admin', 'staff'),
                            'details'=>array('owner', 'admin', 'staff'),
                            'delete'=>array('owner', 'admin')
                          );


}
?>