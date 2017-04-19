<?php
	
	$form->set($Guest->getAttrIdGuest());

?>

<h1 class="page-header">
<?php 
if ($invite_id == -1) {
    echo "Ajouter un invité";
}else{
    echo "Editer l'invité";
}

?>
    
</h1>

<form action="<?= $invite_id ?>" method="post" class="form-horizontal">

    <fieldset>
        <legend>Icam / Permanent :</legend>

        <div class="row">
        	<div >
        		<?php echo $form->input('id', 'hidden', array('value'=>$invite_id)); ?>
        		<?php echo $form->input('is_icam', 'hidden', array('value'=>true)); ?>
        	    <?php if($Auth->isAdmin())
			    	echo $form->input('inscription','Date d\'inscription : ', array('maxlength'=>"20",'class'=>'datetimepicker'));
			    	else echo $form->input('inscription', 'hidden');?>
        	    <?php echo $form->input('nom','Nom : ', array('maxlength'=>"155")); ?>
        	    <?php echo $form->input('prenom','Prénom : ', array('maxlength'=>"155"/*, 'required'=>'1'*/)); ?>
        		<?php echo $form->select('sexe', 'Homme/Femme : ', array('data'=>\Spring\Participant::$sexe)); ?>
        		<?php echo $form->select('paiement', 'Paiement : ', array('data'=>\Spring\Participant::$paiement)); ?>
        	    <?php echo $form->select('promo','Promotion : ', array('data'=>\Spring\Participant::$promos)); ?>
        	    <?php echo $form->input('email','Email : ', array('maxlength'=>'255')); ?>
        	    <?php echo $form->input('telephone','Telephone : ', array('maxlength'=>'255')); ?>
        	    <?php echo $form->input('bracelet_id','Numero du bracelet : ', array('maxlength'=>'4','class'=>'input-mini bracelet_id')); ?>

        	</div>
        </div>

    </fieldset>

    <fieldset class="isIcam">

    	<?php $nb = ((count($Guest->invites)>2)?count($Guest->invites):2);
    	 for ($i=0; $i < $nb; $i+=2) { ?>
			<div class="row">
				<?php echo \Spring\Participant::getGuestForm($i, $form); ?>
				<?php if ($i+1 < $nb) echo \Spring\Participant::getGuestForm($i+1, $form); ?>
			</div>
		<?php } ?>

    </fieldset>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Sauvegarder</button>
        &nbsp;
        <button class="btn" type="reset">Annuler</button>
    </div>

</form>
<!-- 
<script src="js/ajout_invite.js"></script> -->
