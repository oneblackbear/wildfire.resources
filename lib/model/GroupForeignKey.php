<?php

class GroupForeignKey extends ForeignKey{

  public $default_scope = "live";
  public function get() {
    $class = $this->target_model($this->default_scope);
    if($cache = WaxModel::get_cache($class, $this->field, $this->model->primval)) return $cache;
    $model = new $this->target_model($this->model->{$this->col_name});
    if($model->primval) {
      WaxModel::set_cache($class, $this->field, $this->model->primval, $model);
      return $model;
    } else return false;
  }

  public function get_choices() {
    if($this->choices && $this->choices instanceof WaxRecordset) {
      foreach($this->choices as $row) $choices[$row->{$row->primary_key}]=$row->{$row->identifier};
      $this->choices = $choices;
      return true;
    }
    $this->link = new $this->target_model($this->default_scope);
    WaxEvent::run("wax.choices.filter",$this); //filter choices hook
    $this->choices[""]="Select";
    foreach($this->link->filter("group_token", Session::get("GROUP"))->all() as $row) $this->choices[$row->{$row->primary_key}]=$row->{$row->identifier};
    return $this->choices;
  }

}
