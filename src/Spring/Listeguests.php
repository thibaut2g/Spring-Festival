<?php

namespace Spring;

/**
* Classe Listguests, la liste des participants du Gala
*/
class Listeguests{

	const perPages = 120;

	private $keyword;
	private $page;
	private $perPages;
	private $options;
	private $globalCountguests;
	private $countguests;
	private $countSqlReturnedguests;
	private $countPages;
	private $guestsList;

	/**
	 * __construct construction de la classe
	 * @param $data array les données pour initialiser
	 **/
	function __construct($data=array()){
		if (empty($data['recherche1']) && !empty($data['recherche2']))
			$this->keyword = $data['recherche2'];
		elseif (!empty($data['recherche1']))
			$this->keyword = $data['recherche1'];
		elseif (!empty($_GET['keyword']))
			$this->keyword = urldecode($_GET['keyword']);
		elseif (!empty($data) && !is_array($data))
			$this->keyword = $data;
		else
			$this->keyword = '';
		if ($this->keyword == '!') $this->keyword = '';

		if (!empty($data['page']))
			$this->page = $data['page'];
		elseif (!empty($_GET['page']))
			$this->page = $_GET['page'];
		else
			$this->page = 1;

		$this->perPages = (isset($data['perPages']) && $data['perPages'] >= 0) ? $data['perPages'] : self::perPages;
		$this->options  = !empty($data['options']) ? $data['options'] : array();

		$this->globalCountguests = Participant::getguestsCount();
		if ($this->perPages > 0)
			$this->guestsList = $this->getguestsList();
		else{
			$this->guestsList = array();
			$this->countguests = $this->globalCountguests;
			$this->countPages  = 1;
		}
		$this->countSqlReturnedguests = count($this->guestsList);
	}

	/**
	 * getguestsList création de la liste des participants en fonction des paramètres voulus
	 * @return array le tableau des participants sélectionnés
	 **/
	private function getguestsList(){
		global $DB;
		$where = '';$q = array();

		$whereSearch = $this->getWhereForSearch();

		if (isset($whereSearch['leftJoin']) && $whereSearch['leftJoin'] != '')
			$leftJoin = $whereSearch['leftJoin'];
		else
			$leftJoin = '';
		if (!empty($whereSearch['data']))
			$q = $whereSearch['data'];
		if (!empty($whereSearch['where']))
			$whereSearch = $whereSearch['where'];
		else
			$whereSearch = '';

		$whereOptions = $this->getWhereForOptions();

		$where = (!empty($whereSearch) || !empty($whereOptions))?'WHERE ':' ';
		if (!empty($whereSearch))
			$where .= $whereSearch;
		if (!empty($whereSearch) && !empty($whereOptions))
			$where .= ' AND ';
		if (!empty($whereOptions))
			$where .= $whereOptions;

	    // --------------------- Vérification sur le nombre de pages --------------------- //
		$this->countguests = current($DB->queryFirst('SELECT COUNT(id) FROM guests '.$where, $q));
		$this->countPages  = ceil($this->countguests/$this->perPages);
		if (empty($this->options)  || !empty($this->options['fields'])
			|| isset($this->options['selectAllPromos'],$this->options['promo'],$this->options['selectguests'])
			&& !( Participant::getPromosCount() > count($this->options['promo'])
			&& $this->options['selectAllPromos'] == 0 && !empty($this->options['promo']) && $this->options['selectguests'] == 1)){
			if ($this->page > $this->countPages || $this->page < 1){
				$this->page = 1;
			}
		}

	    // --------------------- Sélection des invités dans la base --------------------- //
	    $fields = (!empty($this->options['fields']))?((is_array($this->options['fields']))?implode(',', $this->options['fields']):$this->options['fields']):'*';
		$sql = 'SELECT '.$fields.' FROM guests '.(($leftJoin != '')?$leftJoin:'').' '.$where;
		if (empty($this->options) || !empty($this->options['fields'])
			|| (isset($this->options['selectAllPromos'],$this->options['promo'],$this->options['selectguests'])
					&& !( Participant::getPromosCount() > count($this->options['promo'])
								&& $this->options['selectAllPromos'] == 0 && !empty($this->options['promo']) && $this->options['selectguests'] == 1))
		)
			$sql .= ' ORDER BY bracelet_id ASC LIMIT '.(($this->page-1)*$this->perPages).','.$this->perPages;
		$retour = $DB->query($sql, $q);

		// debug($sql,'$sql');

		// On récupère les invités des icams des promos sélectionnées
		if (!empty($retour) && isset($this->options['selectAllPromos'],$this->options['promo'],$this->options['selectguests'])
			&& Participant::getPromosCount() > count($this->options['promo'])
			&& $this->options['selectAllPromos'] == 0 && !empty($this->options['promo']) && $this->options['selectguests'] == 1 ){
			$options = $this->options;
			$options['selectguests'] = 1;
			$options['selectAllPromos'] = 0;
			$options['promo'] = array();
			$whereOptions = $this->getWhereForOptions($options);
			$retour2 = array();
			foreach ($retour as $guestIcam) {
				$retour2[] = $guestIcam;
				$sql = 'SELECT '.$fields.' FROM guests LEFT JOIN icam_has_guest ihg ON ihg.guest_id = id '.(($leftJoin != '')?$leftJoin:'').' WHERE icam_id = :icam_id '.((!empty($whereOptions))?' AND '.$whereOptions:'');
				$retourguests = $DB->query($sql, array('icam_id'=>$guestIcam['id']));
				if (!empty($retourguests)) {
					foreach ($retourguests as $invite) {
						$retour2[] = $invite;
					}
				}
			}
			$this->countguests = count($retour2);
			$this->countPages  = ceil($this->countguests/$this->perPages);
			if ($this->page > $this->countPages || $this->page < 1){
				$this->page = 1;
			}
			if ($this->countPages > 1){
				$retour = array();
				$start = 0+($this->page-1)*$this->perPages;
				$end = ($this->page)*$this->perPages;
				for ($i=$start; $i < $end; $i++) { 
					if (isset($retour2[$i])) {
						$retour[] = $retour2[$i];
					}
				}
			}else{
				$this->page = 1;
				$retour = $retour2;
			}
		}

		if (!empty($this->options['monsieurx'])) {
			$retour2 = array();
			foreach ($retour as $guestIcam) {
				$sql = 'SELECT '.$fields.' FROM guests LEFT JOIN icam_has_guest ihg ON ihg.icam_id = id WHERE guest_id = :guest_id';
				$retourIcam = $DB->queryFirst($sql, array('guest_id'=>$guestIcam['id']));
				$retour2[] = $retourIcam;
				$retour2[] = $guestIcam;
			}
			$this->countguests = count($retour2);
			$this->countPages  = ceil($this->countguests/$this->perPages);
			if ($this->page > $this->countPages || $this->page < 1){
				$this->page = 1;
			}
			if ($this->countPages > 1){
				$retour = array();
				$start = 0+($this->page-1)*$this->perPages;
				$end = ($this->page)*$this->perPages;
				for ($i=$start; $i < $end; $i++) { 
					if (isset($retour2[$i])) {
						$retour[] = $retour2[$i];
					}
				}
			}else{
				$this->page = 1;
				$retour = $retour2;
			}
		}

		foreach ($retour as $k => $guest) {
			$fields = array('id','bracelet_id','prenom','nom','promo','is_icam','paiement','inscription','sexe');
			if ($guest['is_icam']==1){
				$retour[$k]['guests'] = Participant::getguests($guest['id'],$fields);
				$retour[$k]['count_guests'] = current($DB->queryFirst('SELECT COUNT(*) FROM icam_has_guest WHERE icam_id = '.$guest['id']));	
			}
	    	else
	    		$retour[$k]['invitor'] = Participant::getInvitorStatic($guest['id'],$fields);
		}
		return $retour;
	}

	/**
	 * getWhereForSearch création des conditions SQL lié champ de recherche classique
	 * @return string Conditions du champ de recherche pour aller dans la requette SQL
	 **/
	protected function getWhereForSearch(){
		$where = '';$q = array();

        if (!empty($this->keyword)) {
        	$where .= '';
        	// ------------------------------ Recherche en fonction du mot clé ------------------------------ //
		    $motclef = htmlspecialchars(str_replace('!', '', $this->keyword));
		    $q = array('motclef'=>'%'.$motclef.'%');
		    if(preg_match('/^[!]/i', $this->keyword)){
		    	$promo = str_replace('!', '', $this->keyword);
			    $promos = Participant::$promos;
			    if (in_array($promo, $promos) || in_array($promo, $promos['Intégrés']) || in_array($promo, $promos['Apprentis']) || in_array($promo, $promos['Formations Continues'])) {
		    		if ($promo >= 116) {
		    			$q = array();
			    		$where .= 'promo != '.$promo;
		    		}else{
				    	$q = array('motclef'=>$promo);
				    	$where .= 'promo != :motclef';
		    		}
		    	}
		    }else if (!empty($motclef) && $motclef == 'repas') {
				$where = 'repas = 1 ';
		    }else if (!empty($motclef) && $motclef == 'buffet') {
				$where = 'buffet = 1 ';
		    }else if (!empty($motclef) && $motclef == 'formules') {
				$where = '(repas = 1 OR buffet = 1) ';
		    }else if (!empty($motclef) && $motclef == 'online' || $motclef == 'offline' || $motclef == 'on' || $motclef == 'off') {
		    	$where .= 'online = '.(($motclef == 'online' || $motclef == 'on')?'1':'0').' ';
		    }else{
		    	$where .= '('.implode(' LIKE :motclef OR ', array('prenom' ,'nom' ,'email' ,'promo' ,'inscription' ,'bracelet_id')).' LIKE :motclef)
				';
				$explode = explode(' ', $motclef);
	        	if (count($explode) > 1) {
	        		$q = array('motclef'=>'%'.$motclef.'%','explode0'=>'%'.$explode[0].'%','explode1'=>'%'.$explode[1].'%');
				    $where .= 'OR (prenom LIKE :explode0 AND nom LIKE :explode1)
				    	OR (prenom LIKE :explode1 AND nom LIKE :explode0)
					';
	        	}else{
				    $q = array('motclef'=>'%'.$motclef.'%');
	        	}
		    }
		}
		return array('where'=>$where,'data'=>$q);
	}

	/**
	 * getWhereForOptions création des conditions SQL lié aux options du champ de recherche avancée
	 * @param $options array Les options de la recherche avancée
	 * @return string Conditions pour aller dans la requette SQL des options propres au champ de recherche avancé
	 **/
	protected function getWhereForOptions($options = array()){
		if (empty($options)) {
			$options = $this->options;
		}
		if (!empty($options) && isset($options['selectguests'],$options['selectAllPromos'],$options['promo'],$options['formule'],$options['paiement'],$options['sexe'],$options['noBracelet'],$options['monsieurx'],$options['doublons'])) {
			$whereOptions = '';
	    	if (!is_array($options)) {
                $whereOptions.= '('.$options.')
				';
            }else{
            	if (!empty($options['promo'])) {
	            	foreach ($options['promo'] as $k => $v) {
	            		if (!($v > 100))
	            			$options['promo'][$k] = '"'.$v.'"';
	            	}
            	}

                $cond = array();
                if ($options['selectguests'] == 0 && $options['selectAllPromos'] == 1)
                	$cond[] = 'promo != "" ';
                else if ($options['selectguests'] == 1 && $options['selectAllPromos'] == 0 && empty($options['promo']))
                	$cond[] = 'promo = "" ';
                else if ($options['selectAllPromos'] == 0 && !empty($options['promo']))
                	$cond[] = '(promo IN('.implode(',', $options['promo']).') )';

                if (!empty($options['paiement']) && is_array($options['paiement']) && count($options['paiement'])<count(Participant::$paiement))
                	$cond[] = 'paiement IN("'.implode('","', $options['paiement']).'")';
                if (!empty($options['sexe']) && is_array($options['sexe']) && count($options['sexe'])<count(Participant::$sexe))
                	$cond[] = 'sexe IN("'.implode('","', $options['sexe']).'")';

                if (!empty($options['noBracelet']) && $options['noBracelet'] == 1) {
                	$cond[] = '(bracelet_id = 0 OR bracelet_id IS NULL OR bracelet_id = "")';
                	$this->perPages = 3000;
                }
                // elseif (!empty($options['promMissingOnes']) && $options['noBracelet'] == 1) {
                // 	$cond[] = '(bracelet_id = 0 OR bracelet_id IS NULL OR bracelet_id = "")';
                // 	$this->perPages = 3000;
                // }
                elseif (!empty($options['monsieurx'])) {
                	$cond[] = '(nom = "monsieur" OR prenom = "monsieur" OR nom = "x" OR prenom = "x" )';
                	$this->perPages = 3000;
                }elseif (!empty($options['doublons']) && $options['doublons'] == 1) {
                	$cond[] = 'bracelet_id IN( SELECT tmptable.bracelet_id FROM ( SELECT bracelet_id FROM guests WHERE bracelet_id != 0 GROUP BY bracelet_id HAVING COUNT(bracelet_id) >1 ) AS tmptable )';
                	$this->perPages = 3000;
                }
                if (!empty($cond)) {
               		$whereOptions .= '('.implode(' AND ',$cond).')
					';
                }
            }
            return $whereOptions;
	    }else return '';
	}

	/**
	 * getGuestAsTr retourne la liste des participants
	 * @return html Retourne la liste des participants sous forme de ligne tr d'un tableau
	 **/
	public function getGuestAsTr(){
		ob_start();

		if($this->countguests){
	        foreach ($this->guestsList as $guest) { ?>
				<tr class="<?php echo ($guest['is_icam']==1)?'':'warning' ?>">
				  <td>
				    <input id="guest_<?php echo $guest['id']; ?>" class="checkbox" type="checkbox" value="<?php echo $guest['id']; ?>" name="guests[]">
				  </td>

				  <td>
				    <span class="badge badge-<?php echo ($guest['is_icam']==1)?'success':'info';?>" title="<?php echo ($guest['is_icam']==1)?'Icam '.$guest['promo']:'Invité';?>">
				      <?php if(!empty($guest['invitor']['id'])) echo '<span class="hidden">'.$guest['invitor']['id'].'.</span>' ?>
				      <?php echo $guest['id'] ?>
				    </span>
				  </td>

				  <td>
				    <span class="badge badge-<?php echo ($guest['is_icam']==1)?'success':'info';?>" title="<?php echo ($guest['is_icam']==1)?'Icam '.$guest['promo']:'Invité';?>">
				      <?php echo (empty($motclef))? $guest['bracelet_id']: preg_replace('/('.$motclef.')/i', "<em>$1</em>", $guest['bracelet_id']); ?>
				    </span>
				  </td>

				  <td>
				  	<?php echo (empty($motclef))? $guest['prenom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $guest['prenom']); ?>	
				  </td>

				  <td>
				  	<?php echo (empty($motclef))? $guest['nom']: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $guest['nom']); ?>
				  </td>

				  <td>
				  	<?php echo (empty($motclef) || empty($guest['email']))? $guest['email']."&nbsp;": preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $guest['email']); ?>
				  		
				  </td>

				  <td>
				  	<?php echo (empty($motclef) || empty($guest['promo']))? $guest['promo']."&nbsp;": preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $guest['promo']); ?>
				  </td>

				  <td>
				  	<?php 
				    	if ($guest['is_icam']==1 && !empty($guest['count_guests'])) echo $guest['count_guests']
				  	?>&nbsp;
				  </td>

				  <td>
				  	<?php $date = (!empty($guest['inscription']))?date('m-d',strtotime($guest['inscription'])):'mm-dd';
				    	echo (empty($motclef))? $date: preg_replace('/('.$motclef.')/i', "<strong>$1</strong>", $date);
				  	?>
				  </td>

				  <td>
				    <div class="pull-right">
				      <a href="#" rel="popover">
				        Infos
				      </a>
				      <div class="infos hidden">
				        <div class="title hidden">
				          <i class="icon-<?php echo (isset($guest['sexe']) && $guest['sexe']==2)?'girl':'user'; ?>"></i>
				          <?php echo $guest['prenom'].' '.$guest['nom'].(empty($guest['promo'])?'':' <small>('.$guest['promo'].')</small>')?></div>
				        <div class="message hidden">
				          <?php if(!empty($guest['email'])){ ?><i class="icon-envelope"></i> <?php echo $guest['email'].'<br>'; }?>
				          <?php if(!empty($guest['telephone'])){ ?><i class="icon-phone"></i> <?php echo $guest['telephone'].'<br>'; }?>
				          <?php if(!empty($guest['inscription'])){ ?><i class="icon-calendar"></i> Inscrit le <?php echo $guest['inscription'].'<br>'; }?>
				          <?php if(!empty($guest['paiement'])){ ?><i class="icon-shopping-cart"></i> Paiement : <?php echo $guest['paiement'].'<br>'; }?>
				          <?php if(!empty($guest['price'])){ ?><i class="icon-shopping-cart"></i> Place : <?php echo $guest['price'].'€<br>'; }?>
				          <?php 
				          if (!empty($guest['guests']) && is_array($guest['guests'])) {
				            echo '<strong>Invité'.(count($guest['guests'])==1?'':'s').' :</strong><br>';
				            foreach ($guest['guests'] as $invite) {
				              ?><i class="icon-<?php echo (isset($invite['sexe']) && $invite['sexe']==2)?'girl':'user'; ?>"></i> <?php echo $invite['prenom'].' '.$invite['nom'].((!empty($invite['paiement']))?' <small><em>('.$invite['paiement'].')</em></small>':'').'<br>';
				            }
				          }elseif(!empty($guest['invitor'])){
				          echo '<strong>Invité par :</strong><br>';
				            $guest['invitor'] = Participant::getInvitorStatic($guest['id']);
				            ?><i class="icon-<?php echo (isset($guest['invitor']['sexe']) && $guest['invitor']['sexe']==2)?'girl':'user'; ?>"></i> <?php echo $guest['invitor']['prenom'].' '.$guest['invitor']['nom'].' ('.$guest['invitor']['promo'].') <small><em>('.$guest['invitor']['paiement'].')</em></small>'.'<br>' ?><?php
				          }?>
				        </div>
				      </div>
				      <a href="ajout_invite/<?php $resultat = \Spring\Participant::findIcamGarantId($guest['id']); echo $resultat
				       ?>" title="Editer l'utilisateur #<?php echo $guest['id']; ?>">Edit</a>
				      <a href="liste_participants/supprimer/<?php echo $guest['id']; ?>" title="Supprimer l'utilisateur #<?php echo $guest['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cet invité et ses invités ?');">Sup</a>              
				    </div>
				  </td>

				</tr>
	        	<?php
	        }
	    }else{?>
	        <tr>
	          <td colspan="12">
	            <em>Aucun invité trouvé.</em>
	          </td>
	        </tr>
	    <?php }

	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	/**
	 * getTHead retourne la la ligne représentant la tête du tableau
	 * @return html la tête du tableau
	 **/
	public function getTHead(){
		global $DB;
		ob_start(); ?>
			<tr>
				<th><input onclick="toggleChecked(this.checked)" class="checkbox" type="checkbox"></th>
				<th>Id</th>
				<th>Bracelet</th>
				<th>Prenom</th>
				<th>Nom</th>
				<th><i class="icon-envelope"></i> Email</th>
				<th>Promo</th>
				<th>Nb Invités</th>
				<th>Inscription</th>
				<th>Actions</th>
			</tr>
	    <?php 
	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	/**
	 * getActionsGroupees retourne la pagination correspondant à la liste
	 * @param $id int numero des actions groupés (il y en a plusieurs par page)
	 * @return html Retourne les actions groupées, le champ de recherche, le lien vers la recherche avancée, le compteur des participants affichés
	 **/
	public function getActionsGroupees($id){
		global $DB;
		ob_start(); ?>
		<!-- <p class="actions form-inline pull-left">
			<select name="action" id="action<?php echo $id ?>" class="span2">
			  <option selected="selected" value="-1">Action Groupée</option>
			  <option value="delete">Supprimer </option>
			</select>
			<button class="btn" type="submit">Appliquer</button>
		</p> -->
		<div class="pull-left form-search" style="margin-left:15px;">
			<div class="input-append">
			  <input class="input-medium search-query" id="recherche<?php echo $id ?>" name="recherche<?php echo $id ?>" placeholder="Rechercher ..." type="text" value="<?php echo $this->keyword; ?>">
			  <button class="btn" type="submit">Rechercher</button>
			</div>
		</div>
		<!-- <?php if ($id == 1): ?>
		<div class="pull-left" style="margin-left:15px;">
			<a id="BtnRechercheAvancee" href="#FormRechercheAvancee" class="btn btn-primary" onclick="jQuery('#FormRechercheAvancee').slideToggle(); return false;">Recherche Avancée</a>
			<small class="loader" style="margin-left:10px; display:none;"><img src="img/icons/spinner.gif" alt="loader"></small>
		</div>
		<?php endif ?> -->
		<p class="pull-right">
			<em><span class="guestCount" title="nombre d'utilisateurs affichés"><?php echo $this->countSqlReturnedguests.'/'.$this->countguests; ?></span> invités</em>
		</p>
	    <?php 
	    $return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	/**
	 * getPagination retourne la pagination correspondant à la liste
	 * @param $forjs boolean pour savoir si on doit échaper les fins de ligne, pour pouvoir être utilisé en javascript
	 * @return html la pagination en html
	 **/
	public function getPagination($forjs=false){
		global $DB;
		/*	//pagination-centered
			<!-- <li class="disabled"><a href="#">«</a></li> -->
			<!-- <li><a href="#">»</a></li> -->
		*/
		ob_start();
		?><ul>
			<?php for ($i=1; $i <= $this->countPages; $i++): ?>
				<li id="p<?php echo $i ?>" <?php echo ($i == $this->page)?'class="active"':''; ?>>
					<a class="page" id="p<?php echo $i ?>" href="admin_liste_guests.php?page=<?php echo $i ?>">
						<?php echo $i ?>
					</a>
				</li>
			<?php endfor; ?>
		</ul>
		<?php 
		$return = ob_get_contents();
		ob_end_clean();
		if ($forjs) {
			$return = str_replace("
", "\\
", trim($return));
		}
		return $return;
	}

	// -------------------- Export functions -------------------- //

	/**
	 * exportParticipantsList génération en csv de la liste des participants sélectionés $this->guestsList
	 * @return csv fichier csv des participants sélectionnés
	 **/
	public function exportParticipantsList(){
		global $DB;
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.csv');
		// $sql = 'SELECT id , bracelet_id , nom , prenom , repas , buffet , is_icam , promo , email , telephone , inscription, icam_id AS idGarantIcam
		// FROM guests g
		// LEFT JOIN icam_has_guest ihg ON g.id = ihg.guest_id';
		$ParticipantsArray = $this->guestsList;
		$output = fopen('php://output', 'w');

		// output the column headings
		fputcsv($output, array('id','bracelet_id','nom','prenom','is_icam','promo','email','telephone','inscription','sexe','paiement','price','icam_id'),';');
		foreach ($ParticipantsArray as $guest) {
			$array = array(
				'id'              => $guest['id'],
				'bracelet_id'     => $guest['bracelet_id'],
				'nom'             => $guest['nom'],
				'prenom'          => $guest['prenom'],
				'is_icam'         => $guest['is_icam'],
				'promo'           => $guest['promo'],
				'email'           => $guest['email'],
				'telephone'       => $guest['telephone'],
				'inscription'     => $guest['inscription'],
				'sexe'            => $guest['sexe'],
				'paiement'        => $guest['paiement'],
				'price'           => $guest['price'],
				'icam_id'         => ($guest['is_icam'] == 0)?$array['icam_id'] = Participant::findIcamGarantId($guest['id']):''
			);
			fputcsv($output, $array,';');
		}
		fclose($output);
	}

	// -------------------- Informations functions -------------------- //

	public function getUrlParams($name){
		$retour = '';
		if ($name == 'export')
			$retour .= "export_liste_participants.php";
		$retour .= '?keyword='.$this->keyword;
		if (!empty($this->options) && is_array($this->options)) {
			foreach ($this->options as $k => $v) {
				$retour .= "&amp;".$k."=".$v;
			}
		}
		return $retour;
	}

	public function getListFormParams(){
		return array(
			'keyword'    => $this->keyword,
			'recherche1' => $this->keyword,
			'recherche2' => $this->keyword,
			'page'       => $this->page,
			'perPages'   => $this->perPages,
			'options'    => $this->options
		);
	}

	public function generalData(){
		return array(
			'keyword'                => $this->keyword,
			'page'                   => $this->page,
			'perPages'               => $this->perPages,
			'options'                => $this->options,
			'globalCountguests'      => $this->globalCountguests,
			'countguests'            => $this->countguests,
			'countSqlReturnedguests' => $this->countSqlReturnedguests,
			'countPages'             => $this->countPages
		);
	}

	// -------------------- Getters & Setters -------------------- //

	public function __get($var){
		if (!isset($this->$var)) {
			// if (isset($this->guestsNumbers[$var])) {
			// 	return $this->guestsNumbers[$var];
			// }elseif (isset($this->icamAndTheirguests[$var])) {
			// 	return $this->icamAndTheirguests[$var];
			// }elseif (isset($this->nightOptions[$var])) {
			// 	return $this->nightOptions[$var];
			// }else{
				return false;
			// }
		}else return $this->$var;
	}
}