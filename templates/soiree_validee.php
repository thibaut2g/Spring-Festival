<?php

  if (!empty($_POST['id']) && \Spring\Participant::isGuest($_POST['id'])) {
    if (!empty($_POST['action']) && ($_POST['action'] == 'ok' || $_POST['action'] == 'remove')) {
      if ($DB->findCount('entrees',array('guest_id'=>$_POST['id'])) == 0) {
        $DB->save('entrees',array('guest_id'=>$_POST['id'],'arrived'=>1,'arrival_time'=>date('H:i:s')),'insert');
      }
      if ($_POST['action'] == 'ok') {
        $DB->save('entrees',array('arrived'=>1,'arrival_time'=>date('Y-m-d H:i:s')),array('update'=>array('guest_id'=>$_POST['id'])));
        echo "btn-error";
      }else{
        $DB->save('entrees',array('arrived'=>0,'arrival_time'=>''),array('update'=>array('guest_id'=>$_POST['id'])));
        echo "btn-success";
      }
    }
    if(!empty($_POST['id']) && !empty($_POST['change_bracelet']) && $_POST['change_bracelet'] >= 0){
      $DB->save('guests',array('arrived'=>1),array('update'=>array('guest_id'=>$_POST['id'])));
      echo "Bracelet_id changed";
    }
  }


?>