<?php

class GroupHasManyField extends HasManyField {

  public $default_scope = "live";
  public $editable = false;

  public function get($filters = false) {
    $target = new $this->target_model($this->default_scope);
    if($filters) $target->filter($filters);
    if($this->join_order) $target->order($this->join_order);
    if($this->eager_loading) return $this->eager_load($target);
    return $this->lazy_load($target);
  }

  public function eager_load($target) {
    $cache = WaxModel::get_cache($this->target_model.":".md5(serialize($target->filters)), $this->field, $this->model->primval, false);
    if(is_array($cache)) return new WaxModelAssociation($this->model, $target, $cache, $this->field);
    $vals = $target->scope($this->default_scope)->filter("group_token", Session::get("GROUP"))->filter(array($this->join_field=>$this->model->primval))->all();
    WaxModel::set_cache($this->target_model.":".md5(serialize($target->filters)), $this->field, $this->model->primval, $vals->rowset);
    return new WaxModelAssociation($this->model, $target, $vals->rowset, $this->field);
  }

  public function lazy_load($target) {
    $target->scope($this->default_scope)->filter("group_token", Session::get("GROUP"))->filter(array($this->join_field=>$this->model->primval));
    foreach($target->rows() as $row) {
      $ids[]=$row[$target->primary_key];
    }
    return new WaxModelAssociation($this->model, $target, $ids, $this->field);
  }

  public function get_choices() {
    $j = new $this->target_model($this->default_scope);
    if($this->identifier) $j->identifier = $this->identifier;
    foreach($j->filter("group_token", Session::get("GROUP"))->all() as $row) $this->choices[$row->{$row->primary_key}]=$row->{$row->identifier};
    return $this->choices;
  }


}
