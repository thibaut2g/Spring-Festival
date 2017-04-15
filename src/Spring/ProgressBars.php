<?php

namespace Spring;

/**
* Classe des ProgressBars pour le Gala
*/
class ProgressBars{

	private $totalinvites;
	private $difference;
	private $invitesNumbers;
	private $icamAndTheirinvites;
	private $nightOptions;
	private $recettes;
	private $totalinvitesArrived;

	const height='10';
	const width='120';

	function __construct($id=-1,$attr=array()){
		global $DB;
		$this->totalinvites = $DB->findCount('invites');
		$this->totalinvitesArrived    = $DB->findCount('entrees',array('arrive'=>1),'invite_id');
		$this->totalinvitesNotArrived = $this->totalinvites - $this->totalinvitesArrived;

		$this->difference = current($DB->queryFirst('SELECT d.count-dd.count FROM (SELECT COUNT(*) count FROM invites  WHERE inscription >= CURDATE() AND inscription < CURDATE() + INTERVAL 1 DAY)d, (SELECT COUNT(*) count FROM invites  WHERE inscription >= CURDATE() - INTERVAL 1 DAY AND inscription < CURDATE())dd'));
        // -------------------------------------------------- //
		$this->invitesNumbers['invites']= $DB->findCount('lien_icam_invite','','icam_id');
		$this->invitesNumbers['Icam']  = $this->totalinvites - $this->invites;
		$this->invitesNumbers['les2018']= $DB->findCount('invites',array('promo'=>2018));
		$arrayPromsIcamLille          = array_merge(Participant::$promos['Intégrés'],Participant::$promos['Intégrés'],array(Participant::$promos['Erasmus']));
		$this->invitesNumbers['icamLille'] = $DB->findCount('invites',array('is_icam'=>1,'cond'=>'promo = "'.implode('" OR promo = "', $arrayPromsIcamLille).'"'));
		$this->invitesNumbers['permanantsIngesParents'] = $DB->findCount('invites',array('cond'=>'promo = "Permanent" OR promo = "Ingenieur" OR promo = "Parent"'));
		$this->invitesNumbers['EtudiantsSans2018']       = $this->Icam - $this->les2018 - $this->permanantsIngesParents;
		$this->invitesNumbers['progressionGuest']       = current($DB->queryFirst('SELECT COUNT(*) count FROM invites  WHERE inscription >= CURDATE() AND inscription < CURDATE() + INTERVAL 1 DAY'));
        // -------------------------------------------------- //
        $this->icamAndTheirinvites = array(
	        'IcamOneGuest' => current($DB->queryFirst('SELECT COUNT(*) count FROM (SELECT COUNT(*) c FROM lien_icam_invite GROUP BY icam_id)i  WHERE i.c=1')),
	        'IcamTwoGuest' => current($DB->queryFirst('SELECT COUNT(*) count FROM (SELECT COUNT(*) c FROM lien_icam_invite GROUP BY icam_id)i  WHERE i.c=2')),
	        'progressionIcam' => current($DB->queryFirst('SELECT COUNT(*) count FROM invites  WHERE is_icam=1 AND inscription >= CURDATE() AND inscription < CURDATE() + INTERVAL 1 DAY'))
        );
        $this->icamAndTheirinvites['IcamNoGuest']  = $this->Icam - $this->IcamOneGuest - $this->IcamTwoGuest;
        // -------------------------------------------------- //
        $this->recettes = current($DB->queryFirst('SELECT (SUM(price)) globalPrice FROM invites'));
        $this->progressionRecettes = current($DB->queryFirst('SELECT (SUM(price)) dayRecettes FROM invites  WHERE inscription >= CURDATE() AND inscription < CURDATE() + INTERVAL 1 DAY'));
        $this->prixBDE = (3*($this->invitesNumbers['EtudiantsSans2018']));
        /*
"SELECT * FROM (SELECT COUNT(id) total FROM `invites`)total,
(SELECT COUNT(id) avant FROM `invites` WHERE inscription < '2012-12-25')avant,
(SELECT COUNT(id) apres FROM `invites` WHERE inscription > '2012-12-25')apres,
(SELECT MAX(count) rush FROM (SELECT COUNT(id) count FROM `invites` GROUP BY DATE(inscription))count)rush"
        ///*/
	}

	public function getinvites($width=self::width,$height=self::height){
        return Functions::getMultipleProgressBar(
            array('sum'=>$this->totalinvites, 'all'=>array(
                array('pourcent'=>$this->les2018,'class'=>'warning','title'=>'Nous les 2018 : '.$this->les2018.'/'.$this->totalinvites),
                array('pourcent'=>$this->EtudiantsSans2018,'class'=>'success','title'=>'Etudiants Icam +2018 : '.$this->EtudiantsSans2018.'/'.$this->totalinvites),
                array('pourcent'=>$this->invites,'class'=>'info','title'=>'Invités extérieurs : '.$this->invites.'/'.$this->totalinvites),
                array('pourcent'=>$this->permanantsIngesParents,'class'=>'warning','title'=>'Permanants, Ingénieurs, Parents : '.$this->permanantsIngesParents.'/'.$this->totalinvites)
            )),
            array('height'=>$height,'width'=>$width,'color'=>'green','class'=>'warning','display'=>'inline-block')
        );
	}

	public function geticamAndTheirinvites($width=self::width,$height=self::height){
        return Functions::getMultipleProgressBar(
            array('sum'=>$this->Icam, 'all'=>array(
                array('pourcent'=>$this->IcamTwoGuest,'class'=>'success','title'=>'Icams/Permanants ayant 2 invités : '.$this->IcamTwoGuest.'/'.$this->Icam),
                array('pourcent'=>$this->IcamOneGuest,'class'=>'info','title'=>'Icams/Permanants ayant 1 invités : '.$this->IcamOneGuest.'/'.$this->Icam),
                array('pourcent'=>$this->IcamNoGuest,'class'=>'danger','title'=>'Icams/Permanants ayant 0 invités : '.$this->IcamNoGuest.'/'.$this->Icam),
            )),
            array('height'=>$height,'width'=>$width,'color'=>'','class'=>'danger','display'=>'inline-block')
        );
	}

	public function getNightOptions($width=self::width,$height=self::height){
        return Functions::getMultipleProgressBar(
            array('sum'=>$this->totalinvites, 'all'=>array(
                array('pourcent'=>$this->repas,'class'=>'info','title'=>'Invités allant au repas : '.$this->repas.'/'.$this->totalinvites),
                array('pourcent'=>$this->buffet,'class'=>'success','title'=>'Invités allant au buffet : '.$this->buffet.'/'.$this->totalinvites),
                array('pourcent'=>$this->nonRepas,'class'=>'warning','title'=>'Invités allant qu\'à la soirée : '.$this->nonRepas.'/'.$this->totalinvites),
            )),
            array('height'=>$height,'width'=>$width,'color'=>'','class'=>'warning','display'=>'inline-block')
        );
	}

	public function getinvitesArrival($width=self::width,$height=self::height){
        return Functions::getMultipleProgressBar(
            array('sum'=>$this->totalinvites, 'all'=>array(
                array('pourcent'=>$this->totalinvitesArrived,'class'=>'success','title'=>'Invités arrivés : '.$this->totalinvitesArrived.'/'.$this->totalinvites),
                array('pourcent'=>$this->totalinvitesNotArrived,'class'=>'danger','title'=>'Invités que l\'on attend encore : '.$this->totalinvitesNotArrived.'/'.$this->totalinvites),
            )),
            array('height'=>$height,'width'=>$width,'color'=>'red','class'=>'danger','display'=>'inline-block')
        );
	}

	public function __get($var){
		if (!isset($this->$var)) {
			if (isset($this->invitesNumbers[$var])) {
				return $this->invitesNumbers[$var];
			}elseif (isset($this->icamAndTheirinvites[$var])) {
				return $this->icamAndTheirinvites[$var];
			}elseif (isset($this->nightOptions[$var])) {
				return $this->nightOptions[$var];
			}
		}else return $this->$var;
	}
}