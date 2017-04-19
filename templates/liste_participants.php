<h1 class="page-header clearfix">

  <div class="pull-left">Liste des participants au Spring Festival</div>
  <div class="pull-right">
    <a id="export" href="export_liste_participants.php" class="btn btn-primary btn-large" onlick="">Exporter</a>
    <a href="ajout_invite" class="btn btn-primary btn-large">Ajouter un invité</a>
  </div>

</h1>

<?php 

  if(!isset ($form))
    $form = new form();

  $form->set($Listguests->getListFormParams());
  if (!isset($form->data['options']['selectguests']))
    $form->data['options']['selectguests'] = 1;
  if (!isset($form->data['options']['selectAllPromos']))
    $form->data['options']['selectAllPromos'] = 1;
  if (isset($form->data['options']['selectAllPromos'],$form->data['options']['selectguests']) && $form->data['options']['selectguests'] == 0 && $form->data['options']['selectAllPromos'] == 0 && empty($form->data['options']['promo'])) {
    $form->data['options']['selectguests'] = 1;
    $form->data['options']['selectAllPromos'] = 1;
  }

  if (isset($form->data['options']['selectAllPromos'],$form->data['options']['promo']) && $form->data['options']['selectAllPromos'] == 0 && count($form->data['options']['promo']) == count(Participant::$promos))
    $form->data['options']['promo'] = array();

  elseif (isset($form->data['options']['selectAllPromos'],$form->data['options']['promo']) && $form->data['options']['selectAllPromos'] == 1)
    $form->data['options']['promo'] = Participant::$promos;
?>

<div id="post"></div>


<form id="form" action="#">

  <?php echo $form->input('page', 'hidden', array('value'=>$Listguests->page)); ?>

  <div class="clearfix"><?php echo $Listguests->getActionsGroupees(1); ?></div>
  <div class="pagination"><?php echo $Listguests->getPagination(); ?></div>


  <table class="table table-bordered table-striped" id="guestsList">

      <thead>
        <?php echo $Listguests->getTHead(); ?>
      </thead>
      <tbody id="resultat">
        <?php echo $Listguests->getGuestAsTr(); ?>
      </tbody>
      <?php if ($Listguests->countguests > 10): ?>
      <tfoot>
        <?php echo $Listguests->getTHead(); ?>
      </tfoot>
      <?php endif ?>

  </table>

  <?php if ($Listguests->countguests > 10): ?>

    <div class="pagination"><?php echo $Listguests->getPagination(); ?></div>
    <div class="clearfix"><?php echo $Listguests->getActionsGroupees(2); ?></div>

  <?php endif ?>

</form>

<?php
  // Functions::tablesorter('guestsList','[8,1],[4,0]','0: {sorter: false},11: {sorter: false}');

?>