<?php

namespace Spring;

class Functions{

    /**
     *
     * @param <type> $message
     * @param <type> $type 
     */
    static function setFlash($message, $type = 'success'){ // On créer un tableau dans lequel on stock un message et un type qu'on place dans la variable flash de la variable $_session
        $_SESSION['flash'][] = array(
            'message'   => $message,
            'type'      => $type
        );
    }

    /**
     *
     * @return string
     */
    static function flash(){ //parcourir dans les flash de la $_session, le array contenant le message défini grâce au setflash
        if(isset($_SESSION['flash'])){
            $html = '';
            foreach ($_SESSION['flash'] as $k => $v) {
                if(isset($v['message'])){
                    $html .= '<div class="alert alert-'.$v['type'].'"><button class="close" data-dismiss="alert">×</button>'.$v['message'].'</div>';
                }
            }
            $html .= '<div class="clear"></div>';
            $_SESSION['flash'] = array();
            return $html;
        }
    }

    /**
    * Permet de supprimer une valeur dans la bdd.
    * @global PDO $DB
    * @param string $name
    * @param string $title
    * @return boolean
    **/
    static function check_delete($name,$title=null,$page=null){
        global $DB;

        if (empty($page)) {
            $page = 'admin_liste_'.$name.'s.php';
        }
        
        if(!empty($_GET['del_'.$name])){
            $id=$_GET['del_'.$name];
            if(empty($title)) $title=$name;

            if ($DB->findCount($name.'s',"id=$id") != 0) {
                $DB->delete($name.'s','id='.$id);
                Functions::setFlash('<strong>'.$title.' #'.$id.' supprimé</strong>','success');
                header('Location:'.$page);exit;
            }else{
                Functions::setFlash('<strong>'.$title.' inconnu</strong>','error');
                header('Location:'.$page);exit;
            }
        }else{
            return false;
        }
    }

    /**
    * Permet de supprimer une valeur dans la bdd.
    * @global PDO $DB
    * @param string $name
    * @param string $title
    * @return boolean
    **/
    static function check_activation($name,$title=null){
        global $DB;
        if(!empty($_GET['activate_'.$name])){
            $id=$_GET['activate_'.$name];
            if(empty($title)) $title=$name;
            if ($DB->findCount($name.'s',"id=$id") != 0) {
                $DB->save($name.'s',array('online'=> 1),array('update'=>array('id'=>$id)));  // On passe le champ activer à 1 <=> actif
                Functions::setFlash('<strong>'.$title.' #'.$id.' activé</strong>','success');
                header('Location:admin_liste_'.$name.'s.php');exit;
            }else{
                Functions::setFlash('<strong>'.$title.' inconnu</strong>','error');
                header('Location:admin_liste_'.$name.'s.php');exit;
            }
        }else if (!empty($_GET['disactivate_'.$name])) {
            $id=$_GET['disactivate_'.$name];
            if(empty($title)) $title=$name;
            if ($DB->findCount($name.'s',"id=$id") != 0) {
                $DB->save($name.'s',array('online'=> 0),array('update'=>array('id'=>$id)));  // On passe le champ activer à 1 <=> actif
                Functions::setFlash('<strong>'.$title.' #'.$id.' desactivé</strong>','info');
                header('Location:admin_liste_'.$name.'s.php');exit;
            }else{
                Functions::setFlash('<strong>'.$title.' inconnu</strong>','error');
                header('Location:admin_liste_'.$name.'s.php');exit;
            }
        }else{
            return false;
        }
    }

    /**
    * Permet de supprimer une valeur dans la bdd.
    * @param string $name
    * @param string $title
    * @return boolean
    **/
    static function check_global_actions($name,$title=null){
        global $DB;
        if (isset($_POST['action'],$_POST[$name.'s']) && ($_POST['action'] != -1 || ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1))) {
            if ($_POST['action'] == -1 && isset($_POST['action2']) && $_POST['action2'] != -1) {
                $_POST['action'] = $_POST['action2'];
            }
            if ($_POST['action'] == 'delete') {
                $flash = array();
                foreach ($_POST[$name.'s'] as $id) {
                    if($DB->delete($name.'s','id='.$id)){
                        $flash[] = '<strong>'.$title.' #'.$id.' supprimé</strong>';
                    }
                }
                if(!empty($flash))Functions::setFlash(implode('<br/>', $flash),'success');
            }else if ($_POST['action'] == 'online') {
                $flash = array();
                foreach ($_POST[$name.'s'] as $id) {
                    if($DB->save($name.'s',array('online'=> 1),array('update'=>array('id'=>$id)))){
                        $flash[] = '<strong>'.$title.' #'.$id.' en ligne</strong>';
                    }
                }
                if(!empty($flash))Functions::setFlash(implode('<br/>', $flash),'success');
            }else if ($_POST['action'] == 'offline'){
                $flash = array();
                foreach ($_POST[$name.'s'] as $id) {
                    if($DB->save($name.'s',array('online'=> 0),array('update'=>array('id'=>$id)))){
                        $flash[] = '<strong>'.$title.' #'.$id.' hors ligne</strong>';
                    }
                }
                if(!empty($flash))Functions::setFlash(implode('<br/>', $flash),'success');
            }
        }else{
            return false;
        }
    }

    /**
     * Fonction maintenance : redirige automatiquement vers la page unfinished.php
     **/
    static function maintenance(){
        global $Auth;
        if(!isset ($_SESSION)){session_start();}
        $maintenance = Config::getDbConfig('maintenance');
        // debug($_SESSION,'Session');
        // debug($maintenance,'maintenance');
        // debug($_SERVER['SCRIPT_NAME'],'$_SERVER');
        if ($maintenance == true) {
            if ($Auth->isAdmin() || (isset($_SERVER['SCRIPT_NAME']) && preg_match("/connection.php/", $_SERVER['SCRIPT_NAME']) || preg_match('/maintenance.php/', $_SERVER['SCRIPT_NAME']) ) ) {
                /*
                Cas ou : 
                On est loggé en tant qu'admin $Auth->isAdmin()
                On est sur la page connection.php (preg_match("/connection.php/", $_SERVER['SCRIPT_NAME']))
                On est sur la page maintenance.php (preg_match("/maintenance.php/", $_SERVER['SCRIPT_NAME']))
                */
            }else{ // Sinon, redirection !
                Functions::setFlash('redirection maintenance..','error');
                header('Location:maintenance.php');exit;
            }
        }
    }

    static function isGuest($id){
        global $DB;
        if (is_numeric($id) && $DB->findCount('invites',array('id'=>$id)) == 1) return true;
        else return false;
    }

    static function isPage(){
        $i=0;
        foreach (func_get_args() as $key => $v){
            if ($v == 'index') {
                if (preg_match('/\/$/', $_SERVER['REQUEST_URI']))       $i++;
            }
            if(preg_match('/\/'.$v.'\.php/', $_SERVER['REQUEST_URI']))  $i++;
        }
        if($i>0){return TRUE;}
        else{return FALSE;}
    }

    static function getFirstVals($array){
        if (isset($array[0]) && is_array($array[0])) {
          $trash = array();
          foreach ($array as $value) {
            if (isset($value['id'],$value['name']))
                $trash[$value['id']] = $value['name'];
            elseif(isset($value['date'],$value['count']))
                $trash[$value['date']] = $value['count'];
            else
                $trash[] = current($value);
          }
          return $trash;
        }else{
          return $array;
        }
    }

    static function getTables($tables,$db){
        $trash = array();
        foreach ($tables as $table) {
            $trash[$table] = getFirstVals($db->query('SHOW COLUMNS FROM '.$table));
        }
        return $trash;
    }

    static function AreArraysDifferent($Array2008,$Array2010){
        if (sizeof($Array2008) != sizeof($Array2010))
            return true;
        elseif (sizeof($Array2008) != sizeof(array_intersect_assoc($Array2008,$Array2010))) {
            return true;
        }else{
            return false;
        }
    }

    static function arrayDif($a1,$a2){
        $dif = array();
        foreach ($a1 as $v) {
            if (!in_array($v, $a2))
                $dif[] = $v;
        }
        return $dif;
    }

    static function arrayInter($a1,$a2){
        $inter = array();
        foreach ($a1 as $v) {
            if (in_array($v, $a2))
                $inter[] = $v;
        }
        return $inter;
    }

    /**
     * Combine several arrays together
     * @return array All arrays given in one array
     */
    static function arrayMerge(){
        $arrayCombine = array();
        foreach (func_get_args() as $array) {
            foreach ($array as $k => $v) {
                $arrayCombine[$k] = $v;
            }
        }
        return $arrayCombine;
    }

    static function getTermId($col){
        return Functions::getId('terms',$col);
    }

    /**
     *
     * @param <type> $id
     */
    static function tablesorter($id,$col='[1,0]',$header = '0: {sorter: false}'){
        echo "<script src='js/jquery.tablesorter.min.js'></script>
    <script >
        jQuery(function() {
            jQuery('table#$id').addClass('tablesorter').tablesorter({
                sortList: [".$col."],
                headers : {".$header."}
            });
        });
    </script>";
    }

    static function getProgressBar( $pourcent, $width, $height, $color, $class='success' ) {
        $bar = '<div style="margin:0 auto;height:'.$height.'px;width:'.$width.'px;border:1px solid '.$color.';text-align:left;display:inline-block;position:relative;" class="progress progress-'.$class.'">
            <div class="bar" style="width: '.$pourcent.'%;"></div>
        </div>';
        return $bar;
    }

    static function getMultipleProgressBar( $options,$mainbar) {
        if (empty($mainbar)) {
            $mainbar = array('height'=>'6','width'=>'40','display'=>'block','class'=>'success');
        }
        $returnbar = '<span style="height:'.$mainbar['height'].'px;width:'.(($mainbar['width']>0)?$mainbar['width'].'px':$mainbar['width']).';text-align:left;margin:auto;'.((!empty($mainbar['display']) && $mainbar['display']=='inline-block')?'margin:auto 5px;':'').'display:'.((!empty($mainbar['display']))?$mainbar['display']:'block').';" class="progress'.((!empty($options['all']) && (!empty($mainbar['class']) && $mainbar['class'] != 'no'))?' bar-'.((!empty($mainbar['class']))?$mainbar['class']:$options['all'][count($options['all'])-1]['class']):'').'">';
            if (!empty($options['sum'])) {
                foreach ($options['all'] as $key => $bar) {
                    if ($key<count($options['all'])-1) {
                        $returnbar .= '<span class="bar'.((!empty($bar['class']))?' bar-'.$bar['class']:'').'" style="width: '.round($bar['pourcent']/$options['sum']*100,2).'%;"'.((!empty($bar['title']))?' rel="tooltip" title="'.$bar['title'].'" data-original-title="'.$bar['title'].'"':'').'></span>';
                    }else{
                        $returnbar .= '<span class="bar'.((!empty($bar['class']))?' bar-'.$bar['class']:'').'" style="width: '.round($bar['pourcent']/$options['sum']*100-0.01,2).'%;"'.((!empty($bar['title']))?' rel="tooltip" title="'.$bar['title'].'" data-original-title="'.$bar['title'].'"':'').'></span>';
                    }//rel="tooltip" href="#" data-original-title
                }
            }
        $returnbar .= '</span>';
        return $returnbar;
    }

} // End of Class


