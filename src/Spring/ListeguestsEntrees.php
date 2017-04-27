<?php

namespace Spring;

class ListeguestsEntrees extends Listeguests{

	const perPages = 10;

	function __construct($data=array()){
		$data['options']['fields'] = array('id','bracelet_id','prenom','nom','promo','is_icam','paiement','inscription','arrived','price');
		parent::__construct($data);
		// debug($this->generalData(),'generalData');
		// debug($this->guestsList,'guestsList');
	}

	// private function getGuestsList($leftJoin=''){
	// 	$leftJoin = ' LEFT JOIN entrees ON entrees.guest_id = id ';
	// 	debug($leftJoin,'$leftJoin$leftJoin$leftJoin');
	// 	return parent::getGuestsList($leftJoin);
	// }

	//UPDATE entrees LEFT JOIN guests ON id = guest_id SET `arrived` = '1', `arrival_time` = CURRENT_TIME( '' ) WHERE promo =119

	protected function getWhereForSearch(){
		$where = '';$q = array();
		$kw = $this->keyword;
        if (!empty($kw)) {
        	$where .= '';
        	// ------------------------------ Recherche en fonction du mot clé ------------------------------ //
		    $motclef = htmlspecialchars(str_replace('!', '', $kw));
        	$explode = explode(' ', $motclef);
		    $where .= '(prenom LIKE :motclef OR nom LIKE :motclef'.(($this->keyword >= 0)?' OR bracelet_id LIKE :motclef':'').') 
';
        	if (count($explode) > 1) {
        		$q = array('motclef'=>'%'.$motclef.'%','explode0'=>'%'.$explode[0].'%','explode1'=>'%'.$explode[1].'%');
			    $where .= 'OR (prenom LIKE :explode0 AND nom LIKE :explode1)
			    	OR (prenom LIKE :explode1 AND nom LIKE :explode0)
';
        	}else{
			    $q = array('motclef'=>'%'.$motclef.'%');
        	}
		}

		return array('where'=>$where,'data'=>$q,'leftJoin'=>'LEFT JOIN entrees ON entrees.guest_id = id');
	}

	public function getGuestAsTr(){
		ob_start();

		if($this->countguests){
	        foreach ($this->guestsList as $guest) { ?>
<tr class="<?php echo ($guest['is_icam']==1)?'':'warning' ?>">
  <td>
    <span class="badge badge-<?php echo ($guest['is_icam']==1)?'success':'info';?>" title="<?php echo ($guest['is_icam']==1)?'Icam '.$guest['promo']:'Invité';?>">
      <?php if(!empty($guest['invitor']['id'])) echo '<span class="hidden">'.$guest['invitor']['id'].'.</span>' ?>
      <?php echo $guest['id'] ?>
    </span>
  </td>
  <td>
    <span id="span_bracelet_id_for<?php echo $guest['id'] ?>" data-braceletid="<?php echo $guest['bracelet_id'] ?>" data-guestid="<?php echo $guest['id'] ?>" class="span_bracelet_id badge badge-<?php echo ($guest['is_icam']==1)?'success':'info';?>" title="<?php echo ($guest['is_icam']==1)?'Icam '.$guest['promo']:'Invité';?>" style="cursor:pointer;">
      <?php echo (empty($motclef))? $guest['bracelet_id']: preg_replace('/('.$motclef.')/i', "<em>$1</em>", $guest['bracelet_id']); ?>
    </span>
  </td>
  <td><?php echo (empty($motclef))? $guest['prenom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $guest['prenom']); ?></td>
  <td><?php echo (empty($motclef))? $guest['nom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $guest['nom']); ?></td>
  <td><?php echo $guest['promo'] ?></td>
  <td><?php $date = (!empty($guest['inscription']))?date('m-d',strtotime($guest['inscription'])):'mm-dd';
  	echo $date; ?>
  </td>
  <td>
    <div class="pull-right">
    	<?php if (!$guest['arrived']){ ?>
			<a href="#" class="btn btn-success btn-mini arrivalMarkUp" title="Marquer l'invité comme arrivé au Gala" id="<?php echo $guest['id'] ?>">V</a>
    	<?php }else{ ?>
			<a href="#" class="btn btn-danger btn-mini arrivalMarkUp" title="Marquer l'invité comme NON arrivé au Gala" id="<?php echo $guest['id'] ?>">X</a>
    	<?php } ?>
      <a href="#" rel="popover">
        <i class="icon-info-sign"></i>
      </a>
      <div class="infos hidden">
        <div class="title hidden">
          <i class="icon-<?php echo (isset($guest['sexe']) && $guest['sexe']==2)?'girl':'user'; ?>"></i>
          <?php echo $guest['prenom'].' '.$guest['nom'].(empty($guest['promo'])?'':' <small>('.$guest['promo'].')</small>')?></div>
        <div class="message hidden">
          <?php if(!empty($guest['inscription'])){ ?><i class="icon-calendar"></i> Inscrit le <?php echo $guest['inscription'].'<br>'; }?>
          <?php if(!empty($guest['paiement'])){ ?><i class="icon-shopping-cart"></i> Paiement : <?php echo $guest['paiement'].'<br>'; }?>
          <?php if(!empty($guest['price'])){ ?><i class="icon-shopping-cart"></i> Place : <?php echo $guest['price'].'€<br>'; }?>
          <?php 
          if (!empty($guest['guests']) && is_array($guest['guests'])) {
            echo '<strong>Invité'.(count($guest['guests'])==1?'':'s').' :</strong><br>';
            foreach ($guest['guests'] as $guest) {
              ?><i class="icon-<?php echo (isset($guest['sexe']) && $guest['sexe']==2)?'girl':'user'; ?>"></i> <?php echo $guest['prenom'].' '.$guest['nom'].((!empty($guest['paiement']))?' <small><em>('.$guest['paiement'].')</em></small>':'').'<br>';
            }
          }elseif(!empty($guest['invitor'])){
          echo '<strong>Invité par :</strong><br>';
            $guest['invitor'] = Participant::getInvitorStatic($guest['id']);
            ?><i class="icon-<?php echo (isset($guest['invitor']['sexe']) && $guest['invitor']['sexe']==2)?'girl':'user'; ?>"></i> <?php echo $guest['invitor']['prenom'].' '.$guest['invitor']['nom'].' ('.$guest['invitor']['promo'].') <small><em>('.$guest['invitor']['paiement'].')</em></small>'.'<br>' ?><?php
          }?>
        </div>
      </div>
      <a href="admin_edit_guest.php?id=<?php echo Participant::findIcamGarantId($guest['id']); ?>" title="Editer l'utilisateur #<?php echo $guest['id']; ?>"><i class="icon-pencil"></i></a>
      <?php /* ?><a href="admin_liste_guests.php?del_guest=<?php echo $guest['id']; ?>" title="Supprimer l'utilisateur #<?php echo $guest['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cet invité et ses invités ?');"><i class="icon-trash"></i></a><?php //*/ ?>
    </div>
  </td>
</tr>
	        	<?php
	        }
	    }else{?>
	        <tr>
	          <td colspan="9">
	            <em>Aucun invité trouvé.</em>
	          </td>
	        </tr>
	    <?php }

	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	public function getTHead(){
		global $DB;
		ob_start(); ?>
			<tr>
				<th>Id</th>
				<th>Bracelet</th>
				<th>Prenom</th>
				<th>Nom</th>
				<th>Promo</th>
				<th>Inscription</th>
				<th>Actions</th>
			</tr>
	    <?php 
	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	public function getActionsGroupees($id){
		global $DB;
		ob_start(); ?>
<div class="pull-left form-search" style="margin-left:15px;">
	<div class="input-append">
	  <input class="input-medium search-query span6" id="recherche<?php echo $id ?>" name="recherche<?php echo $id ?>" placeholder="#bracelet OU prenom nom OU nom prenom" type="text" value="<?php echo $this->keyword; ?>">
	  <button class="btn" type="submit">Search</button>
	</div>
	<small class="loader" style="margin-left:10px; display:none;"><img src="img/icons/spinner.gif" alt="loader"></small>
</div>
<p class="pull-right">
	<em><span class="guestCount" title="nombre d'utilisateurs affichés"><?php echo $this->countSqlReturnedGuests.'/'.$this->countGuests; ?></span> invités</em>
</p>
	    <?php 
	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	public function getNbGuestArrived(){
		global $DB;
		$sql = 'SELECT COUNT(arrived) FROM entrees WHERE arrived = 1';
		$nb = $DB->query($sql);
		$res = array_pop($nb);
		$rep = intval($res['COUNT(arrived)']);
		return $rep;
	}

}