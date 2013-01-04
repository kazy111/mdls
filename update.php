<?php

  include_once('./header.php');

  $users = $manager->get_all_users();
  print_r($users);
  foreach($users as $user){
    $user->update($manager);
  }

?>

