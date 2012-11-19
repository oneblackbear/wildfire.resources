<?
class ReportController extends BaseController{
  public $per_page = false;
  public $model_class = "Work";
  public $form_name = "report_form";
  public $name = "Report";
  public $filter_fields=array(
                          'date_start' => array('columns'=>array('date_end'), 'partial'=>'_filters_date', 'fuzzy'=>true)
                        );
  public $navigation_links = array('index');
  public $permissions = array(
                          'index'=>array('owner', 'admin'),
                          'by_job'=>array('owner', 'admin')
                        );

  public function by_job(){
    WaxEvent::run("model.setup", $this);
  }
}
?>