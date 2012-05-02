<?
class BriefController extends BaseController{
  public $model_class = "Brief";
  public $form_name = "brief_form";
  public $name = "Briefs";
  public $filter_fields=array();
  public $navigation_links = array('index', 'create');
  public $permissions = array(
                          'create'=>array('owner', 'admin', 'privileged'),
                          'edit'=>array('owner', 'admin', 'privileged'),
                          'index'=>array('owner', 'admin', 'privileged'),
                          'delete'=>array('owner')
                        );



}
?>