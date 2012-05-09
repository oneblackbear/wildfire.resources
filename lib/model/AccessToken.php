<?
class AccessToken extends WildfireResource{
  public static $salt = "0neb2012";
  public function setup(){
    parent::setup();
    $this->define("send_notification", "BooleanField", array('editable'=>false, 'default'=>0));
    $this->define("staff", "ForeignKey", array('target_model'=>'Staff'));
  }

  public static function generate($obj){
    $model = new AccessToken;
    $model->title = hash_hmac("sha1", $obj->primval.rand(0,9999).time(), AccessToken::$salt.date("zy"));
    $model->group_token = $obj->group_token;
    return $model->save();
  }
}
?>