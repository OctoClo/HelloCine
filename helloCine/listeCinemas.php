<?php
  header("content-type: text/html; charset=ISO-8859-15");
  require_once "../api-allocine-helper.php";

  // Créer l'objet qui s'occupera de la requête
  $helper = new AlloHelper;

	// Récupérer les paramètres passés en GET
	$lat = $_GET['latitude'];
	$long = $_GET['amp;longitude'];

	try
  {
    // Envoi de la requête pour les paramètres passés
    $cinemasEtFilms = $helper->showtimesByPosition($lat, $long, $radius=10);

		// Lien vers la map et tous les cinémas
		print('<div class="row"><div class="col-sm-1"></div><div class="col-sm-11">');
		print('<a href="helloCineMapCinemas.php?latitude='.$lat.'&longitude='.$long.'"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>   Localiser tous les cinemas</a><br><br></div></div>');

		print('<div class="row"><div class="col-sm-1"></div><div class="col-sm-10"><div class="list-group">');

		// Première boucle : Afficher tous les cinémas (Nom, adresse et coordonnées pour la map) avec un lien vers leurs films
		foreach ($cinemasEtFilms->theaterShowtimes as $cinemaEtFilms)
		{
			$cinema = $cinemaEtFilms['place']['theater'];

			// Nom
			print("<a class='list-group-item' href='listeFilmsCinema.php?latitude=".$lat."&longitude=".$long."&cinema=".$cinema['code']."'><center><h2>".$cinema['name']."</h2>");

			// Adresse
			print("<p><b>Adresse :</b> ".$cinema['address']." - ".$cinema['postalCode']." ".$cinema['city']."</p></center>");
			print('</a>');
		}

		// Fermer le HTML
		print('</div></div></div>');
  }
  catch (ErrorException $error)
  {
    // En cas d'erreur
    echo "Erreur numero ", $error->getCode(), " : ", $error->getMessage(), PHP_EOL;
  }
?>
