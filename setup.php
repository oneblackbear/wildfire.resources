<?
CMSApplication::register_module("client", array('plugin_name'=>'wildfire.resources', 'assets_for_cms'=>true, "display_name"=>"Clients", "link"=>"/admin/client/"));
CMSApplication::register_module("jobcomment", array("display_name"=>"Comments on jobs", "link"=>"/admin/jobcomment/"));
?>