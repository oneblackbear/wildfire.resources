<?php

class GroupForeignKey extends ForeignKey{

  public $default_scope = "";
  public function get(){
    if($cache = WaxModel::get_cache($this->target_model, $this->field, $this->model->primval)) return $cache;
    $class = new $this->target_model($this->default_scope);
    $model = $class->filter($class->primary_key, $this->model->{$this->col_name})->first();
    if($model && $model->primval){
      WaxModel::set_cache($this->target_model, $this->field, $this->model->primval, $model);
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
