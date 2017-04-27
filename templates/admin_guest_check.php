<?php
if(isset($_POST['bracelet_id']) && !empty($_POST['id']) && (\Spring\Participant::isGuest($_POST['id']) || $_POST['id'] == -1) && $_POST['bracelet_id'] >= 0){
    $bracelet_id_exists = \Spring\Participant::bracelet_exists($_POST['id'],$_POST['bracelet_id']);
    if (!empty($_POST['saveit']) && (!$bracelet_id_exists || $_POST['bracelet_id'] == 0)) {
      if ($_POST['bracelet_id'] == 0) {
        $bracelet_id_exists = 0;
      }
      $DB->save('guests',array('bracelet_id'=>$_POST['bracelet_id']),array('update'=>array('id'=>$_POST['id'])));
    }
    echo $bracelet_id_exists;
}else{
  echo 0;
}

?>