<!DOCTYPE html>
<html lang="fr">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <!-- Le styles -->
    <link href="<?= $RouteHelper->publicPath ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $RouteHelper->publicPath ?>css/main.css" rel="stylesheet">
    <meta name="description" content="Site internet du Spring pour les inscriptions">
    <meta name="author" content="Thibaut de Gouberville 118, Guillaume Dubois 119, Antoine Giraud 115">
    <link rel="shortcut icon" href="<?= $RouteHelper->publicPath ?>img/Art.ico">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="http://getbootstrap.com/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <title><?= $RouteHelper->getPageTitle() ?></title>
  </head>

  <body>
    <nav class="navbar navbar-fixed-top navbar-inverse">

      <div class="container">

        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?= $RouteHelper->getPathFor() ?>">Spring Festival</a>
        </div>

        <div id="navbar" class="collapse navbar-collapse">

          <ul class="nav navbar-nav">

            <?php if ($Auth->isAdmin()){ ?>

            <li class="dropdown" id="admin">

                <li><a href="<?= $RouteHelper->getPathFor('liste_participants') ?>"><i class="icon-book"></i> Liste des participants</a></li>
                <li><a href="<?= $RouteHelper->getPathFor('ajout_guest') ?>"><i class="icon-plus"></i> Ajouter un invité</a></li>
                <li><a href="<?= $RouteHelper->getPathFor('entrees') ?>"><i class="icon-plus"></i> Entrées soir même</a></li>


            </li>

            <?php } ?>


            </ul>

          <?php if ($Auth->isLogged()): ?>
          <ul class="nav navbar-nav navbar-right">

              <li><a><em>Bonjour <?php echo $_SESSION['Auth']['prenom']?></em></a></li> 


            <li><a href="<?= $RouteHelper->getPathFor('logout') ?>">Déconnexion</a></li>
          </ul>

          <?php endif ?>
        </div><!-- /.nav-collapse -->
      </div><!-- /.container -->
    </nav><!-- /.navbar -->

    <div class="container">
      <?php foreach ($flash->getMessages() as $key => $flashs): ?>
        <?php foreach ($flashs as $flashMsg): ?>
          <div class="alert alert-<?= $key ?>"><button class="close" data-dismiss="alert">×</button><?php echo $flashMsg ?></div>
        <?php endforeach ?>
      <?php endforeach ?>