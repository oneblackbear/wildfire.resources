<?
class SearchController extends BaseController{
  public $name = "Search";
  public $model_class = "Staff";
  public $filter_fields=array();
  public $navigation_links = array();
  public $search_results = array();
  public $permissions = array(
                            'index'=>array('owner', 'admin', 'staff'),
                            'create'=>array('owner', 'admin', 'staff'),
                            'edit'=>array('owner', 'admin', 'staff'),
                            'details'=>array('owner', 'admin', 'staff'),
                            'delete'=>array()
                          );

  //do a fuzzy search on all the controllers that are allowed
  public function index(){
    $search_on = array();
    foreach($this->structure as $name=>$details){
      $class = $details['class'];
      if($class::$searchable){
        $controller = new $class(false, false);
        $search_on[$name] = array('model'=>$controller->model_class, 'cols'=>$class::$global_search_columns);
      }
    }
    if($q = Request::param('q')){
      foreach($search_on as $name=>$info){
        $class = $info['model'];
        $model = new $class;
        $res = array();
        foreach($info['cols'] as $col){
          foreach($model->clear()->scope("live")->filter("$col LIKE ?", "%".$q."%", "raw")->limit(3)->all() as $row) $res[$row->primval] = $row;
        }
        if(count($res)) $this->search_results[$name] = $res;
      }
    }

  }

}
?>