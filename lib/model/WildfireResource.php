<?
class WildfireResource extends WaxModel{

  public function setup(){
    $this->define("title", "CharField", array('export'=>true, 'maxlength'=>255, 'scaffold'=>true, 'default'=>"enter title here", 'info_preview'=>1) );
    $this->define("content", "TextField", array('widget'=>"TinymceTextareaInput", 'label'=>'Description'));
    $this->define("media", "ManyToManyField", array('target_model'=>"WildfireMedia", "eager_loading"=>true, "join_model_class"=>"WildfireOrderedTagJoin", "join_order"=>"join_order", 'group'=>'media', 'module'=>'media'));
    $this->define("date_modified", "DateTimeField", array('export'=>true, 'scaffold'=>true, "editable"=>false));
    $this->define("date_created", "DateTimeField", array('export'=>true, "editable"=>false));
    $this->define("author", "ForeignKey", array('export'=>true, 'target_model'=>"WildfireUser", 'scaffold'=>true, 'widget'=>'HiddenInput', 'info_preview'=>1));
    parent::setup();
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
  }
}
?>