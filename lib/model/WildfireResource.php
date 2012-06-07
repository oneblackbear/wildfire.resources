<?
class WildfireResource extends WaxModel{
  public static $salt = "0bb";
  public function setup(){
    $this->define("title", "CharField", array('required'=>true, 'maxlength'=>255, 'label'=>'Name <span class="required">*</span>','scaffold'=>true, 'info_preview'=>1) );
    $this->define("content", "TextField", array('widget'=>"TinymceTextareaInput", 'label'=>'Brief <span class="required">*</span>'));
    //$this->define("media", "ManyToManyField", array('editable'=>false,'target_model'=>"WildfireMedia", "eager_loading"=>true, "join_model_class"=>"WildfireOrderedTagJoin", "join_order"=>"join_order", 'group'=>'media', 'module'=>'media'));
    $this->define("date_modified", "DateTimeField", array('export'=>true, 'scaffold'=>true, "editable"=>false));
    $this->define("date_created", "DateTimeField", array('export'=>true, "editable"=>false));
    $this->define("created_by", "IntegerField", array('widget'=>'HiddenInput'));
    $this->define("group_token", "CharField", array('widget'=>'HiddenInput', 'info_preview'=>1));
    parent::setup();
    $this->define("saved_colour", "CharField", array('editable'=>false));
    $this->define("send_notification", "BooleanField", array('editable'=>false, 'default'=>1));
  }

  public function scope_live(){
    return $this->order("title ASC");
  }

  public function notifications(){}
  public function contact_emails(){
    return array();
  }
  public function before_insert(){
    parent::before_insert();
    if(!$this->group_token) $this->group_token = hash_hmac("sha1", time(), self::$salt);
  }
  public function is_editable(){
    if(Session::get("LOGGED_IN_ROLE") == "owner") return true;
    else return false;
  }

  //this will need updating when the framework can handle manipulating join columns
  public function file_meta_set($fileid, $tag, $order=0, $title=''){
    $model = new WaxModel;
    if($this->table < "wildfire_media") $model->table = $this->table."_wildfire_media";
    else $model->table = "wildfire_media_".$this->table;

    $col = $this->table."_".$this->primary_key;
    if(!$order) $order = 0;
    if(($found = $model->filter($col, $this->primval)->filter("wildfire_media_id", $fileid)->all()) && $found->count()){
      foreach($found as $r){
        $sql = "UPDATE `".$model->table."` SET `join_order`=$order, `tag`='$tag', `title`='$title' WHERE `id`=$r->primval";
        $model->query($sql);
      }
    }else{
      $sql = "INSERT INTO `".$model->table."` (`wildfire_media_id`, `$col`, `join_order`, `tag`, `title`) VALUES ('$fileid', '$this->primval', '$order', '$tag', '$title')";
      $model->query($sql);
    }
  }
  public function file_meta_get($fileid=false, $tag=false){
    $model = new WaxModel;
    if($this->table < "wildfire_media") $model->table = $this->table."_wildfire_media";
    else $model->table = "wildfire_media_".$this->table;
    $col = $this->table."_".$this->primary_key;
    if($fileid) return $model->filter($col, $this->primval)->filter("wildfire_media_id", $fileid)->order('join_order ASC')->first();
    elseif($tag=="all") return $model->filter($col, $this->primval)->order('join_order ASC')->all();
    elseif($tag) return $model->filter($col, $this->primval)->filter("tag", $tag)->order('join_order ASC')->all();
    else return false;
  }

  public function format_content() {
    return CmsTextFilter::filter("before_output", $this->content);
  }

  public function before_save(){
    parent::before_save();
    if($this->columns['date_created'] && !$this->date_created) $this->date_created = date("Y-m-d H:i:s");
    if($this->columns['date_modified']) $this->date_modified = date("Y-m-d H:i:s");
    if($this->columns['content']) $this->content =  CmsTextFilter::filter("before_save", $this->content);
    if(!$this->saved_colour){
      $colours = new CSSColour;
      $this->saved_colour = $colours->RGB2Hex($this->get_rgb());
    }
  }

  public function get_rgb(){
    return array(rand(125,255), rand(125,255), rand(125,255));
  }
  public function css_selector(){
    return Inflections::underscore($this->title);
  }

  public function colour($join=false, $weight, $func="lighten"){
    if($join && ($joins = $this->$join) && ($item = $joins->first()) ) return $item->colour(false, $weight, $func);
    if(!$weight) return "#".$this->saved_colour;
    $colours = new CSSColour;
    return "#".$colours->$func($this->saved_colour, $weight);
  }

  public function start_end_times($sd = false, $ed = false){
    if($sd) $start = $sd;
    else $start = strtotime($this->date_start);
    if($ed) $end = $ed;
    else $end = strtotime($this->date_end);

    $sysm = date("F Y", $start);
    $smsd = date("md", $start);
    $emed = date("md", $end);
    $end_short = $start_short = "";
    $start_txt = date("jS F", $start);
    $start_short = date("D j M", $start);

    if(date("Y") != date("Y", $start)){
      $start_txt .= " " .date("Y", $start);
      $start_short .= " " .date("Y", $start);
    }
    //if not on the same day
    if($smsd != $emed){
      $end_text = " - " .date("jS F", $end);
      $end_short = " - ". date("D j M", $end);
    }
    if(date("Y", $start) != date("Y", $end)){
      $end_text .= " " .date("Y", $end);
      $end_short .= " " .date("Y", $end);
    }
    return array('string'=>$start_txt . $end_text, 'sysm'=>$sysm, 'start'=>$start, 'end'=>$end, 'short_string'=>$start_short. $end_short);
  }

  public function date_string(){
    $times = $this->start_end_times();
    return $times['string'];
  }

  public function between($start, $end){
    return $this->filter("((`date_start` <= '".$end."') AND (`date_end` >= '".$start."'))");
  }

  public function for_department($department_id, $join="jobs"){
    if($join == false) return $this->filter("id", $department_id);
    else{
      $dept = new Department;
      //this will allow for multiple department ids
      $departments = $dept->filter("id", $department_id)->all();
      $ids = array(0);
      if(!$dept->columns[$join]) $join = strtolower(get_class($this))."s";
      foreach($departments as $dept) foreach($dept->$join as $j) $ids[] = $j->primval;
      echo "<!-- FD: ";
      print_r($ids);
      echo "-->"
      return $this->filter("id", $ids);
    }
  }

  public function search_details(){
    return $this->title;
  }

}
?>