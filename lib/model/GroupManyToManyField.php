<?php

class GroupManyToManyField extends ManyToManyField {

  public $default_scope = "live";


  protected function eager_load($target) {
		$this->join_model->left_join($target);
		$this->join_model->join_condition("$target->table.$target->primary_key = ".$this->join_model->table.".".$this->join_field($target)." AND $target->table.group_token='".Session::get("GROUP")."'");
		//select columns from the far side of the join, not the join table itself
		$this->join_model->select_columns = array($target->table.".*");
		foreach(array_diff_key($this->join_model->columns,array($this->join_model->primary_key=>false,$this->join_model->left_field=>false,$this->join_model->right_field=>false)) as $col => $col_options)
		  $this->join_model->select_columns[] = "{$this->join_model->table}.$col";

		$cache = WaxModel::get_cache(get_class($this->model), $this->field, $this->model->primval.":".md5(serialize($target->filters)),$vals->rowset, false);
		if($cache) return new WaxModelAssociation($this->model, $target, $cache, $this->field);
		$vals = $this->join_model->all();

		WaxModel::set_cache(get_class($this->model), $this->field, $this->model->primval.":".md5(serialize($target->filters)), $vals->rowset);

		return new WaxModelAssociation($this->model->scope($this->default_scope), $target, $vals->rowset, $this->field);
  }

  protected function lazy_load($target_model){
    $left_field = $this->model->table."_".$this->model->primary_key;
    $right_field = $target_model->table."_".$target_model->primary_key;
    if($this->join_model) $this->join_model->select_columns=$right_field;
    $ids = array();
    if($cache = WaxModel::get_cache(get_class($this->model), $this->field, $this->model->primval.":".md5(serialize($target_model->filters)), false )) return new WaxModelAssociation($this->model, $target_model, $cache, $this->field);
    if($this->join_model) foreach($this->join_model->rows() as $row) $ids[]=$row[$right_field];
    WaxModel::set_cache(get_class($this->model), $this->field, $this->model->primval.":".md5(serialize($target_model->filters)), $ids);
    return new WaxModelAssociation($this->model->scope($this->default_scope)->filter("group_token", $this->model->group_token), $target_model, $ids, $this->field);
  }

	/**
	 * get the choices for the field
	 * @return array
	 */
  public function get_choices() {
    $j = new $this->target_model($this->default_scope);
    if($this->identifier) $j->identifier = $this->identifier;
    foreach($j->filter("group_token", Session::get("GROUP"))->all() as $row) $this->choices[$row->{$row->primary_key}]=$row->{$row->identifier};
    return $this->choices;
  }


}
?>