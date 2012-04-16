<?
class Comment extends WildfireResource{

  public function setup(){
    parent::setup();
    unset($this->columns['media']);
  }

}
?>