<?php

////////////
// Routes //
////////////

// Page ouverte à tous
$app->get('/about', function ($request, $response, $args) {
    global $Auth;

    $flash = $this->flash;
    $RouteHelper = new \Spring\RouteHelper($this, $request, 'A propos');

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'about.php', compact('Auth', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('about');

/////////////////
// Espace Icam //
/////////////////

$app->get('/', function ($request, $response, $args) {
    global $Auth, $DB;

    $flash = $this->flash;
    $RouteHelper = new \Spring\RouteHelper($this, $request, 'Accueil');
    $PBars = new \Spring\ProgressBars();
    $NbAdmin = $DB->findCount('administrateurs');
    $js_for_layout[] = 'countdown.js';
    
    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' index");
    
    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $this->renderer->render($response, 'home.php', compact('RouteHelper', 'Auth', 'PBars', 'NbAdmin', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', 'js_for_layout', $args));
})->setName('home');


$app->get('/liste_participants', function ($request, $response, $args) {
    global $Auth, $DB;

    $flash = $this->flash;
    $RouteHelper = new \Spring\RouteHelper($this, $request, 'Participants');
    $form = new \Spring\Forms();
    $Listguests = new \Spring\Listeguests();
    $js_for_layout[] = 'admin_search_guest.js';
    
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $this->renderer->render($response, 'liste_participants.php', compact('RouteHelper', 'Auth', 'form', 'Listguests', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', 'js_for_layout', $args));
})->setName('liste_participants');


$app->get('/ajout_guest/{guest_id}', function ($request, $response, $args) {

    $guest_id=$request->getAttribute('guest_id');

    global $Auth, $DB;

    $flash = $this->flash;
    $RouteHelper = new \Spring\RouteHelper($this, $request, 'guest');
    $RouteHelper->publicPath = '../public/';
    $form = new \Spring\Forms();
    $js_for_layout[] = 'admin_edit_guest.js';


    if (isset($guest_id) && $guest_id != -1 && \Spring\Participant::isGuest($guest_id)) {
        $Guest = new \Spring\Participant($guest_id);

        if ($Guest->is_icam == 0) { // On redirige vers la page d'édition de l'icam qui guest

            $Guest = null;
            $Guest = new \Spring\Participant(Participant::findIcamGarantId($guest_id));
        }

        // Cas où on édite un User
    }else if (isset($guest_id) && $guest_id != -1 && !\Spring\Participant::isGuest($guest_id)){

        // Cas où l'id donnée ne corresponds à aucun utilisateur
        $this->flash->addMessage('danger', "<strong>Erreur :</strong> Cet id ne correspond à aucun invité");
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('liste_participants'));

    }else if ((isset($guest_id) && $guest_id == -1)){

        $Guest = new \Spring\Participant();
        // Cas de l'ajout d'un nouvel utilisateur
        $_GET["id"] = -1;

    }else{
        
        $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
        $this->renderer->render($response, 'ajout_guest.php', compact('RouteHelper', 'Auth', 'form', 'Guest', 'guest_id', $args));
        return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', 'js_for_layout', $args));

    }
    
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $this->renderer->render($response, 'ajout_guest.php', compact('RouteHelper', 'Auth', 'form', 'Guest', 'guest_id', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', 'js_for_layout', $args));

})->setName('ajout_guest');


$app->post('/ajout_guest/{guest_id}', function ($request, $response, $args) {
    global $Auth, $DB;

    $flash = $this->flash;
    $RouteHelper = new \Spring\RouteHelper($this, $request, 'guest');
    $RouteHelper->publicPath = '../public/';
    $js_for_layout[] = 'admin_edit_guest.js';


    if (empty($_POST['is_icam'])) $_POST['is_icam']=0;

    if (isset ($_POST['id'],$_POST['nom'],$_POST['prenom'],$_POST['email'],$_POST['is_icam'],$_POST['promo'])) {
        
        $form = new \Spring\Forms();
        $Guest = new \Spring\Participant();

        $validate = array(
            'prenom' => array('rule'=>'notEmpty','message' => 'Entrez votre prénom'),
            'nom'    => array('rule'=>'notEmpty','message' => 'Entrez votre nom'),
            'email'  => array('rule'=>'([a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4})?','message' => 'Email non valide')
        );

        $form->setValidates($validate);

        $d = $Guest->checkForm($_POST); // $_POST for guest table : 'id','prenom','nom','email','repas','promo','telephone','is_icam'
        $form->set($d);

        if ($form->validates($d)) { // fin pré-traitement

            if (!empty($_POST['date']) && !is_int($_POST['date']) && preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $_POST['date'])) {

                $Guest->date = strtotime($_POST['date']);

            }

            $Guest->save();
            $this->flash->addMessage('success', "Sauvegarde effectuée :D");

            if ($Guest->is_icam == 1) {

                $Guest->saveguests($_POST['guests']);

            }

            $guest_id = $Guest->id;

            return $response->withStatus(302)->withHeader('Location', $this->router->pathFor('liste_participants'));

        }else{
            while ($er = array_pop($form->errors)) {
                $this->flash->addMessage('danger', $er);
            }
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('erreur_ajout'));
           
        }
    }

});

$app->get('/liste_participants/supprimer/{guest}', function ($request, $response, $args) {
    global $Auth;

    $guest = $request->getAttribute('guest');
    \Spring\Participant::deleteGuest($guest);
    $this->flash->addMessage('success', "Suppression réussie");
    return $response->withStatus(302)->withHeader('Location', $this->router->pathFor('liste_participants'));

})->setName('supprimer_guest');


$app->get('/entrees', function ($request, $response, $args) {
    global $Auth;

    $flash = $this->flash;
    $RouteHelper = new \Spring\RouteHelper($this, $request, 'Entrées');
    $Listeguests = new \Spring\ListeguestsEntrees(array('perPages'=>0));

    if ((isset($_GET['page']) && $_GET['page'] != $Listeguests->page) || (isset($_POST['page']) && $_POST['page'] != $Listeguests->page)) {
        return $response->withStatus(302)->withHeader('Location', $this->router->pathFor('liste_participants'));
    }

    $js_for_layout[] = 'admin_search_guest_entrees.js';

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'entrees.php', compact('Auth', 'Listeguests', 'RouteHelper', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('entrees');


/////////////////////
// Fichier Annexes //
/////////////////////


$app->post('/resultat_recherche.php', function ($request, $response, $args) {
    global $Auth, $DB;

     $dataForm = array();
      if (isset($_GET['options'],$_GET['action'],$_GET['recherche1'],$_GET['recherche2']))
        $dataForm = $_GET;
      else
        $dataForm = $_POST;

    $Listguests = new \Spring\Listeguests($dataForm);
    
    $this->renderer->render($response, 'resultat_recherche.php', compact('Auth', 'Listguests', $args));
})->setName('resultat_recherche.php');


$app->post('/resultat_guest_soiree.php', function ($request, $response, $args) {
    global $Auth, $DB;

    $dataForm = array();
    if (isset($_GET['recherche1']))
        $dataForm = $_GET;
    else
        $dataForm = $_POST;

    if (isset($dataForm['recherche1']) && $dataForm['recherche1'] == '')
        $dataForm['perPages'] = 0;
    else
        $dataForm['perPages'] = 10;
    
    $Listeguests = new \Spring\ListeguestsEntrees($dataForm);
    
    $this->renderer->render($response, 'resultat_guest_soiree.php', compact('Auth', 'Listeguests', $args));
})->setName('resultat_guest_soiree.php');


$app->post('/soiree_validee.php', function ($request, $response, $args) {
    global $Auth, $DB;
    
    $this->renderer->render($response, 'soiree_validee.php', compact('Auth', 'DB', $args));

})->setName('soiree_validee.php');


$app->get('/ajout_guest', function ($request, $response, $args) {

    header("Location:ajout_guest/-1");exit;

})->setName('erreur_ajout');


// $app->post('/resultat_guest', function ($request, $response, $args) {
//     global $Auth;
//     $dataForm = array();

//     if (isset($_GET['options'],$_GET['action'],$_GET['recherche1'],$_GET['recherche2']))
//         $dataForm = $_GET;
//     else
//         $dataForm = $_POST;
//     $ListGuests = new ListGuests($dataForm);
//     $this->renderer->render($response, 'resultat_guest.php', compact('Auth', 'ListGuests', $args));

// })->setName('resultat_guest');


// $app->post('/verifier_guest', function ($request, $response, $args) {
//     global $Auth, $DB;
    
//     $this->renderer->render($response, 'verifier_guest.php', compact('Auth', 'DB', $args));

// })->setName('verifier_guest');