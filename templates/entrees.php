<h1 class="page-header clearfix">
  <div class="pull-left">Entrées du Spring</div>
  <div class="pull-right">
    <a href="<?= $RouteHelper->getPathFor('liste_participants') ?>" class="btn btn-primary btn-large">Liste des invités</a>
  </div>
</h1>

<form id="form" action="#">
<div class="clearfix"><?php echo $ListeInvites->getActionsGroupees(1); ?></div><br>
<table class="table table-bordered table-striped" id="guestsList">
    <thead>
      <?php echo $ListeInvites->getTHead(); ?>
    </thead>
    <tbody id="resultat">
      <?php echo $ListeInvites->getGuestAsTr(); ?>
    </tbody>
</table>
</form>

<hr>
<p><small>
  <em>Remarque :<br>
    - Vous pouvez éditer le numéro de bracelet au besoin en cliquant dessus ;) n'oubliez pas de valider l'invité tout de même</em><br>
    - Vous pouvez vous simplifier la vie si vs cherchez "Ant Gir" ou "Gir Ant" ou "iraud toi" vous trouverez bien Antoine Giraud<br>
  Bonnes entrées & bon Spring !! Antoine Giraud <em>115</em> ;)
</small></p>
