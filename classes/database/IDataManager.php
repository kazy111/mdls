<?php

interface IDataManager {
  // index list
  function get_items($category = NULL, $pagesize = NULL, $page = 0);
  function get_feeds($pagesize = NULL, $page = 0);
  
  function sanitize($str);
  
  // maintenance
  function get_feed($id);
  function get_item($id);

  function set_feed($data);
  function set_item($data);

  function delete_feed($id);
  function delete_item($id);

  function is_exist_item($uid);
  
  // for tag cloud
  function get_categories();
  
  function initialize_db();
  function delete_db();
  function query($sql);
}

?>
