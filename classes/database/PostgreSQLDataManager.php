<?php
include_once 'IDataManager.php';
include_once 'PostgreSQLDB.php';

class PostgreSQLDataManager implements IDataManager {
  private $db;

  function __construct($db_host, $db_name, $db_user, $db_passwd)
  {
    $this->db = new PostgreSQLDB($db_host, $db_name, $db_user, $db_passwd);
  }

  function sanitize($str)
  {
    return $this->db->sanitize($str);
  }

  function query($sql)
  {
    return $this->db->query($sql);
  }

  /**
    * Get user object from DB
    */
  function get_user($id)
  {
    $ret = $this->db->query_ex('SELECT id, user_screen_name, user_name, type, email, last_login FROM user_table WHERE id = ' . $this->db->sanitize($id));

    if (!$ret || !array_key_exists('id', $ret)) {
      return FALSE;
    }

    $user = New user($ret['id'], $ret['user_screen_name'], $ret['user_name'], $ret['type'], $ret['email'], date('c', strtotime($ret['last_login'])));

    $this->get_service($user);
    
    return $user;
  }

  function get_user_by_screen_name($scname)
  {
    $ret = $this->db->query_ex('SELECT id, user_screen_name, user_name, type, email, last_login FROM user_table WHERE user_screen_name = \'' . $this->db->sanitize($scname) . '\'');

    if (!$ret || !array_key_exists('id', $ret)) {
      return FALSE;
    }

    $user = New user($ret['id'], $ret['user_screen_name'], $ret['user_name'], $ret['type'], $ret['email'], date('c', strtotime($ret['last_login'])));

    $this->get_service($user);
    
    return $user;
  }

  function get_all_users()
  {
    $result = $this->db->query('SELECT id, user_screen_name, user_name, type, email, last_login FROM user_table');

    $users = array();

    if ( $result ) {
      while(($arr = $this->db->fetch($result)) != NULL ){
        $user = New user($arr['id'], $arr['user_screen_name'], $arr['user_name'], $arr['type'], $arr['email'], $arr['last_login']);
        $this->get_service($user);
        $users[] = $user;
      }
    }
    
    return $users;
  }

  function get_service($user)
  {
    $sql = 'SELECT user_id, type, public, enable, access_token, access_token_secret,'
            .' access_token_expire, refresh_token, service_user, last_update FROM service_table '
        .' WHERE user_id = ' . $user->id;

    $result = $this->db->query($sql);

    while(($arr = $this->db->fetch($result)) != NULL ){
      $type = (int)$arr['type'];
      $service = service_factory::create_instance($user->id, $type);
      $service->set_access_token($arr['access_token']);
      $service->set_access_token_secret($arr['access_token_secret']);
      $service->set_access_token_expire($arr['access_token_expire']);
      $service->set_refresh_token($arr['refresh_token']);
      $service->set_enable($arr['enable']==='t');
      $service->set_service_user($arr['service_user']);
      $service->set_public($arr['public']==='t');
      $service->set_last_update(date('c', strtotime($arr['last_update'])));
      $user->set_service($type, $service);
    }

  }

  function get_likes_count($user)
  {
    $sql = 'SELECT COUNT(i.*) AS c FROM like_table l INNER JOIN item_table i ON l.item_id = i.id '
        .' WHERE l.user_id = ' . $user->id;

    $result = $this->db->query($sql);

    while(($arr = $this->db->fetch($result)) != NULL ){
      return $arr['c'];
    }
    return 0;
  }

  function get_likes($user, $page_index, $item_count)
  {
    $sql = 'SELECT i.* FROM like_table l INNER JOIN item_table i ON l.item_id = i.id '
        .' WHERE l.user_id = ' . $user->id;

    switch (0) {
      case 1:
        $sql .= ' ORDER BY i.post_date DESC';
        break;
      
      default:
        $sql .= ' ORDER BY l.create_date DESC';
        break;
    }
    $sql .= ' LIMIT ' . $item_count . ' OFFSET ' . ($page_index * $item_count);

    $result = $this->db->query($sql);

    //print_r($result);
    $list = array();
    while(($arr = $this->db->fetch($result)) != NULL ){
      $item = New item($arr['service_type'], $arr['media_id'], $arr['service_type'], $arr['permalink'], $arr['title'], $arr['author'], $arr['description'], $arr['category'], $arr['post_date'], $arr['thumbnail'], $arr['tag']);
      $list[] = $item;
    }
    $user->set_list(1, $list);
  }

  function upsert_user($id, $user_screen_name, $user_name, $type)
  {
    // update user (only user exists)
    $sql = 'UPDATE user_table SET user_screen_name = \'' . $this->db->sanitize($user_screen_name)
            . '\', user_name = \'' . $this->db->sanitize($user_name)
            . '\', type = ' . $this->db->sanitize($type)
            . ', last_login = current_timestamp' 
            . ' where id = ' . $this->db->sanitize($id);
    $this->db->query($sql);

    // insert user (only user not exists)
    $sql = 'INSERT INTO user_table (id, user_screen_name, user_name, type, email, last_login) SELECT '
            . $this->db->sanitize($id) . ', \'' . $this->db->sanitize($user_screen_name)
            . '\', \'' . $this->db->sanitize($user_name)
            . '\', ' . $this->db->sanitize($type)
            . ', \'\', current_timestamp WHERE NOT EXISTS (SELECT 1 FROM user_table WHERE id = ' . $this->db->sanitize($id) . ')';
    $this->db->query($sql);
  }

  function insert_item($item)
  {
    // insert user (only item not exists)
    $sql = 'INSERT INTO item_table (media_id, service_type, permalink, title, author, description, category, post_date, thumbnail, tag) SELECT \''
            . $this->db->sanitize($item->get_media_id()) . '\', '
            . $item->get_service_type() . ', \''
            . $this->db->sanitize($item->get_permalink()) . '\', \''
            . $this->db->sanitize($item->get_title()) . '\', \''
            . $this->db->sanitize($item->get_author()) . '\', \''
            . $this->db->sanitize($item->get_description()) . '\', \''
            . $this->db->sanitize($item->get_category()) . '\', \''
            . $this->db->sanitize($item->get_post_date()) . '\', \''
            . $this->db->sanitize($item->get_thumbnail()) . '\', \''
            . $this->db->sanitize($item->get_tag()) . '\''
            . ' WHERE NOT EXISTS (SELECT 1 FROM item_table WHERE permalink = \'' . $item->get_permalink() . '\')';
    $this->db->query($sql);

    // select item
    $sql = 'SELECT id FROM item_table WHERE permalink = \'' . $item->get_permalink() . '\'';
    $ret = $this->db->query_ex($sql);

    return $ret['id'];
  }

  function insert_likes($user_id, $item, $create_date)
  {
    $item_id = $this->insert_item($item);

    if(!$item_id){
      return FALSE;
    }
    // insert user (only user not exists)
    $sql = 'INSERT INTO like_table (user_id, item_id, create_date) SELECT '
            . $this->db->sanitize($user_id) . ', ' . $this->db->sanitize($item_id)
            . ', \''. $create_date .'\' WHERE NOT EXISTS (SELECT 1 FROM like_table WHERE user_id = ' . $this->db->sanitize($user_id) . ' AND item_id = ' . $this->db->sanitize($item_id) . ')';
    return $this->db->affected_rows($this->db->query($sql));
  }

  function upsert_service($service)
  {
    // update user (only user exists)
    $query = 'UPDATE service_table SET public = ' . ($service->get_public() ? 'true' : 'false')
            . ', access_token = \'' . $service->get_access_token()
            . '\', access_token_secret = \'' . $service->get_access_token_secret()
            . '\', access_token_expire = \'' . $service->get_access_token_expire()
            . '\', refresh_token = \'' . $service->get_refresh_token()
            . '\', service_user = \'' . $service->get_service_user()
            . '\', enable = ' . ($service->get_enable() ? 'true' : 'false')
            . ' where user_id = ' . $service->get_user_id() . ' AND type = ' . $service->get_type();
    $this->db->query($query);

    // insert user (only user not exists)
    $query = 'INSERT INTO service_table (user_id, type, public, enable, access_token, access_token_secret,'
            .' access_token_expire, refresh_token, service_user, last_update) SELECT '
            . $service->get_user_id() . ', ' . $service->get_type()
            . ', ' . ($service->get_public() ? 'true' : 'false')
            . ', ' . ($service->get_enable() ? 'true' : 'false')
            . ', \'' . $service->get_access_token()
            . '\', \'' . $service->get_access_token_secret()
            . '\', \'' . $service->get_access_token_expire()
            . '\', \'' . $service->get_refresh_token()
            . '\', \'' . $service->get_service_user()
            . '\', \'1900-01-01T00:00:00Z\' WHERE NOT EXISTS (SELECT 1 FROM service_table'
            . ' WHERE user_id = ' . $service->get_user_id() . ' AND type = ' . $service->get_type() . ')';
    $this->db->query($query);
  }

  function update_service_last_update($service)
  {
    $sql = 'UPDATE service_table SET last_update = \''. $service->get_last_update() .'\' '
            . ' WHERE user_id = ' . $service->get_user_id() . ' AND type = ' . $service->get_type();
    $this->db->query($sql);
  }

  function delete_service($service)
  {
    $sql = 'DELETE FROM service_table'
            . ' WHERE user_id = ' . $service->get_user_id() . ' AND type = ' . $service->get_type();
    $this->db->query($sql);
  }

  function get_item($id){
    return $this->db->query_ex('select uid, title, description, body, permalink, date, category from item_table where id = '.$id);
  }
  function get_feed($id){
    return $this->db->query_ex('select name, author, site_url, feed_url from feed_table where id = '.$id);
  }
  
  function get_items($category = NULL, $pagesize = NULL, $page = 0){
    $now = time();
    $sql = 'select i.id, i.uid, i.description, i.title, i.body, i.permalink, i.date, i.category, f.name, f.author, f.site_url, f.feed_url from item_table as i '
      . ' inner join feed_table as f on i.feed_id = f.id '
        .' where date <= '.$now;
    if($category)
      $sql .= ' and category = \''.$category.'\'';
    $sql .=' order by date desc';
    if($pagesize)
      $sql .= ' limit '.$pagesize.' offset '.($pagesize * $page).';';
    $result = $this->db->query($sql);
    $list = array();
    while(($arr = $this->db->fetch($result)) != NULL ){
      $list[] = $arr;
    }
    return $list;
  }
  
  function get_feeds($pagesize = NULL, $page = 0){
    $sql = 'select f.id, name, author, site_url, feed_url, MAX(i.date) as lastdate from feed_table as f '
          .' left join item_table as i on f.id = i.feed_id '
          .' where i.date IS NULL or i.date <='.time()
          .' group by f.id, f.name, f.author, f.site_url, f.feed_url'
          .' order by MAX(i.date) desc';
    if($pagesize)
      $sql .= ' limit '.$pagesize.' offset '.($pagesize * $page).';';
    $result = $this->db->query($sql);
    $list = array();
    while(($arr = $this->db->fetch($result)) != NULL ){
      $list[] = $arr;
    }
    return $list;
  }

  function get_categories(){
    
    $sql = 'select category, COUNT(id) AS count, MAX(date) AS lastdate from item_table where category <> \'\' '
          .' and date <= '.time()
          .' group by category';
    $result = $this->db->query($sql);
    $list = array();
    while(($arr = $this->db->fetch($result)) != NULL ){
      $list[] = $arr;
    }
    return $list;
  }

  function is_exist_item($uid){
    $ret = $this->db->query_ex('select id, date from item_table where uid = \''.$uid.'\'');
    return ($ret && array_key_exists('id', $ret)) ? $ret : FALSE;
  }

  function set_item($data){
    if(!array_key_exists('id', $data) || $data['id'] == '' || !is_numeric($data['id'])){
      // create
      $this->db->query('insert into item_table (feed_id, uid, title, body, permalink, date, category, description) values ('
                       .$data['feed_id'].', \''.$data['uid'].'\', \''.$data['title'].'\', \''.$data['body'].'\', \''
                       .$data['permalink'].'\', '.$data['date'].', \''.$data['category'].'\', \''.$data['description'].'\')');
    } else {
      $this->db->query('update item_table set title = \''.$data['title'].'\', body = \''
                       .$data['body'].'\', permalink = \''.$data['permalink'].'\', uid = \''
                       .$data['uid'].'\', category = \''.$data['category'].'\', date = '.$data['date']
                       .', description = \''.$data['description'].'\' '
                       .' where id='.$data['id']);
    }
  }

  function set_feed($data){
    if(!array_key_exists('id', $data) || $data['id'] == '' || !is_numeric($data['id'])){
      // create
      $this->db->query('insert into feed_table (name, author, site_url, feed_url) values (\''
                       .$data['name'].'\', \''.$data['author'].'\', \''.$data['site_url'].'\', \''
                       .$data['feed_url'].'\')');
    } else {
      $this->db->query('update feed_table set name = \''.$data['name'].'\', author = \''
                       .$data['author'].'\', site_url = \''.$data['site_url'].'\', feed_url = \''
                       .$data['feed_url'].'\' where id='.$data['id']);
    }
  }

  function delete_item($id){
    $this->db->query('delete from item_table where id = '.$id);
  }
  
  function delete_feed($id){
    $this->db->query('delete from feed_table where id = '.$id);
  }
  
  
  function try_query($sql){
    if( $this->db->query($sql) ){
      print '<b>success:</b> '.$sql."<br>\n";
    }else{
      print '<span style="color: red;"><b>fail:</b> '.$sql."</span><br>\n";
    }
  }
  
  function initialize_db() {
    $this->try_query('CREATE TABLE user_table ('
                     .'id BIGINT NOT NULL,' // Twitter User ID Number
                     .'user_screen_name VARCHAR(256) NOT NULL,'
                     .'user_name TEXT NOT NULL,'
                     .'type SMALLINT NOT NULL DEFAULT 0,'
                     .'email TEXT NOT NULL,'
                     .'last_login TIMESTAMP WITH TIME ZONE NOT NULL,'
                     .'PRIMARY KEY (id))');

    $this->try_query('CREATE TABLE service_table ('
                     .'user_id BIGINT NOT NULL,'
                     .'type SMALLINT NOT NULL,'
                     .'public BOOLEAN NOT NULL DEFAULT false,'
                     .'access_token TEXT,'
                     .'access_token_secret TEXT,'
                     .'access_token_expire TEXT,'
                     .'refresh_token TEXT,'
                     .'service_user TEXT,'
                     .'enable BOOLEAN NOT NULL DEFAULT false,'
                     .'last_update TIMESTAMP WITH TIME ZONE NOT NULL,'
                     .'PRIMARY KEY (user_id, type))');

    $this->try_query('CREATE TABLE item_table ('
                     .'id SERIAL NOT NULL,'    // unique id of DB
                     .'media_id TEXT NOT NULL,' // unique id of the service
                     .'service_type INTEGER NOT NULL,' // NOT service id
                     .'author TEXT NOT NULL,'
                     .'title TEXT NOT NULL,'
                     .'permalink TEXT NOT NULL,'
                     .'description TEXT NOT NULL,'
                     .'thumbnail TEXT NOT NULL,'
                     .'post_date TIMESTAMP WITH TIME ZONE NOT NULL,'
                     .'category TEXT NOT NULL DEFAULT \'\','
                     .'tag TEXT NOT NULL DEFAULT \'\','
                     .'PRIMARY KEY(id),'
                     .'UNIQUE (permalink) )');

    $this->try_query('CREATE TABLE list_table ('
                     .'id SERIAL NOT NULL,'
                     .'user_id INTEGER NOT NULL,'
                     .'title TEXT NOT NULL,'
                     .'description TEXT NOT NULL,'
                     .'PRIMARY KEY(id))');

    $this->try_query('CREATE TABLE like_table ('
                     .'user_id INTEGER NOT NULL,'
                     .'item_id INTEGER NOT NULL,'
                     .'create_date TIMESTAMP WITH TIME ZONE NOT NULL,'
                     .'PRIMARY KEY(user_id, item_id))');

    $this->try_query('CREATE TABLE timeline_table ('
                     .'user_id INTEGER NOT NULL,'
                     .'item_id INTEGER NOT NULL,'
                     .'create_date TIMESTAMP WITH TIME ZONE NOT NULL,'
                     .'PRIMARY KEY(user_id, item_id))');

    $this->try_query('CREATE TABLE list_item_table ('
                     .'list_id INTEGER NOT NULL,'
                     .'item_id INTEGER NOT NULL,'
                     .'sort_order INTEGER NOT NULL DEFAULT 0,'
                     .'PRIMARY KEY(list_id, item_id))');
  }
  
  function delete_db(){
    $this->try_query('drop table user_table;');
    $this->try_query('drop table service_table;');
    $this->try_query('drop table item_table;');
    $this->try_query('drop table like_table;');
    $this->try_query('drop table timeline_table;');
    $this->try_query('drop table list_table;');
    $this->try_query('drop table list_item_table;');
  }
  
}

?>
