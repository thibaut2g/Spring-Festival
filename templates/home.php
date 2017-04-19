<div class="jumbotron">
        <h1>Spring Festival <small><strong>Administration</strong></small></h1> <!--titre en haut-->

        <div class="numbers">
            <div class="bloc" id="days"></div>

            <div class="bloc" id="hours"></div>

            <div class="bloc" id="minutes"></div>

            <div class="bloc last" id="seconds"></div>
        </div>
</div>

<div class="row">

    <div class="col-xs-6">
        <img src="<?= $RouteHelper->publicPath ?>img/logospring.jpg" alt="logo du spring" >
    </div>

    <div class="col-xs-6">
        <h1 class="page-header">Contenu du site</h1>
        <table class="table">
            <!-- <caption><h2>Contenu du site</h2></caption> -->
            <tbody>
                <tr>
                  <td><strong><?= $PBars->totalGuests; ?></strong></td>
                  <td><em rel="tooltip" title="Progression du jour">(+<?= $PBars->progressionGuest; ?>)</em></td>
                  <td><?= $PBars->getguests(); ?></td>
                  <td>Total d'invités au Spring</td>
                </tr>
                <tr>
                  <td><strong><?= $PBars->Icam; ?></strong></td>
                  <td><em rel="tooltip" title="Nouveaux Icams du jour">(+<?= $PBars->progressionIcam; ?>)</em></td>
                  <td><?= $PBars->getIcamAndTheirguests(); ?></td>
                  <td>Les Icams au Spring</td>
                </tr>
                <?php if ($Auth->isAdmin()): ?>
                <tr>
                  <td><strong rel="tooltip"><?= $PBars->recettes; ?> €</strong></td>
                  <td><em rel="tooltip" title="Recettes du jour">(+<?= $PBars->progressionRecettes; ?>)</em></td>
                  <td></td>
                  <td>Recettes totales du Spring</td>
                </tr>
                <?php endif ?>
                <?php if ($Auth->isAdmin()): ?>
                <tr>
                    <td><strong><?= $NbAdmin ?></strong></td>
                    <td></td>
                    <td>Administrateurs</td>
                </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>


<style>

	.numbers .bloc {
	    width: 97px;
	    height: 87px;
	    background: url(../img/number.png) no-repeat;
	    float: left;
	    text-align: center;
	    color: #767676;
	    margin-right: 30px; 
	}

	.numbers .bloc strong {
	      font-size: 60px;
	      font-weight: bold;
	      text-align: center;
	      display: block;
	      color: #2a3c49;
	      margin-bottom: 15px; 
	}

	.numbers .last {
	    margin-right: 0px; 
	}

	.numbers {
		display: block;
	    overflow: hidden;
	    *zoom: 1; 
	}

</style>