<?
class DashController extends BaseController{
  public $name = "Home";
  public $navigation_links = array('index');
  public $filter_fields=array();
  public $permissions = array(
                          'index'=>array('standard'=>'standard', 'staff'=>'staff', 'admin'=>'admin', 'owner'=>'owner')
                        );

  public function index(){}

}
?>