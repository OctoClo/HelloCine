<?php
  header("content-type: text/html; charset=ISO-8859-15");
  require_once "../api-allocine-helper.php";

  // Créer l'objet qui s'occupera de la requête
  $helper = new AlloHelper;

	// Récupérer les paramètres passés en GET
	$lat = $_GET['latitude'];
	$long = $_GET['longitude'];
	$codeCinema = $_GET['cinema'];

	// Fonction pour découper une chaîne selon plusieurs délimiteurs
	function multiexplode ($delimiters, $string) {
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return $launch;
	}

	try
  {
		// Ouvrir le HTML
		print('<head><title>HelloCine - Films</title>
		<link rel="icon" type="image/x-icon" href="img/cinema.ico" />
		<link href="css/bootstrap.min.css" rel="stylesheet"></head>');

		// Envoi de la requête pour les paramètres passés
    $cinemasEtFilms = $helper->showtimesByPosition($lat, $long, $radius=7);

		// Première boucle : Chercher le cinéma passé en paramètre
		foreach ($cinemasEtFilms->theaterShowtimes as $cinemaEtFilms)
		{
			if ($cinemaEtFilms['place']['theater']['code'] == $codeCinema)
			{
				$cinema = $cinemaEtFilms['place']['theater'];

				// Titre de la page
				print('<body><div class="container"><center><h1>HelloCine</h1></center>');

				// Nom du cinéma
				print('<div class="row"><div class="col-sm-12">');
				print("<h2>".$cinema['name']."</h2>");

				// Adresse + Lien vers la map et le cinéma
				print("<p><b>Adresse :</b> ".$cinema['address']." - ".$cinema['postalCode']." ".$cinema['city']);

				print("   <a href='helloCineMapCinema.php?latitude=".$lat."&longitude=".$long."&cinema=".$codeCinema."'><span class='glyphicon glyphicon-map-marker' aria-hidden='true'></span>   Voir sur la carte</a></p>");
				print('<br><br></div></div>');

				// On commence par afficher le premier film
				$sauterFilmSuivant = false;

				// Deuxième boucle : Afficher tous les films (Affiche, nom, date de sortie, genre(s), format, langue, horaires et trailer)
				foreach ($cinemaEtFilms['movieShowtimes'] as $indice => $movie1)
				{
					if (!$sauterFilmSuivant)
					{
						$movie = $movie1->onShow->movie;

						// Affiche
						print('<div class="row"><div class="col-sm-2">');
						print("<img class='img-thumbnail img-responsive' src='".$movie->poster['href']."' alt='Logo'>"); // Original : 400x~550
						print('<br><br></div>');

						// Nom
						print('<div class="col-sm-5">');
						print("<h3>".$movie['title']."</h3>");

						// Date de sortie et genre(s)
						print("<p><b>Date de sortie :</b> ".$movie->release['releaseDate']." - <b>Genre(s) :</b> "); // Genre(s)
						foreach ($movie['genre'] as $geenre)
						{
							print($geenre['$']." ");
						}
						print('</p>');

						// Format et langue
						print("<p><b>Format :</b> ".$movie1->screenFormat['$']." - <b>Langue :</b> ".$movie1->version['$']."</p>");

						// Horaires
						print("<p><b>Horaires :</b>");
						// Séparer la chaîne horaires en mots selon les espaces et les nouvelles lignes
						$horaires = multiexplode(array(" ", PHP_EOL), $movie1->display);
						foreach ($horaires as $horaire)
						{
							// Si la première lettre est le S de Scéances, indiquer qu'on n'est pas dans une parenthèse et revenir à la ligne
							if ($horaire[0] == "S")
							{
								$parenthese = 0;
								print("<br>");
							}
							// Si la première lettre est une parenthèse, indiquer qu'on est dans une parenthèse
							else if ($horaire[0] == "(")
								$parenthese = 1;
							// Si la dernière lettre est la virgule qui vient juste après la fin de parenthèse, indiquer qu'on vient de sortir de la parenthèse
							else if ($horaire[strlen($horaire) - 1] == ",")
								$parenthese = 2;
							// Si on est sorti de la parenthèse à l'itération précédente, indiquer qu'on n'est plus dans une parenthèse
							else if ($parenthese == 2)
								$parenthese = 0;

							// Si on n'est pas dans une parenthèse, afficher le mot courant
							if (!$parenthese)
								print($horaire." ");
						}
						print('</p>');

						// Si on n'est pas au dernier film du cinéma, regarder quel est le film suivant
						if ($indice != count($cinemaEtFilms['movieShowtimes']) - 1)
						{
							$filmSuivant = $cinemaEtFilms['movieShowtimes'][$indice + 1];

							// Le film suivant est le même que l'actuel -> afficher ses horaires à la suite
							if ($filmSuivant->onShow->movie['title'] == $movie['title'])
							{
								print("<p><b>Horaires de la semaine suivante :</b>");
								$horaires = multiexplode(array(" ", PHP_EOL), $filmSuivant->display);
								foreach ($horaires as $horaire)
								{
									if ($horaire[0] == "S")
									{
										$parenthese = 0;
										print("<br>");
									}
									else if ($horaire[0] == "(")
										$parenthese = 1;
									else if ($horaire[strlen($horaire) - 1] == ",")
										$parenthese = 2;
									else if ($parenthese == 2)
										$parenthese = 0;

									if (!$parenthese)
										print($horaire." ");
								}
								print('</p>');

								// Retenir de ne pas afficher le prochain film
								$sauterFilmSuivant = true;
							}
						}

						print('<br><br></div>');

						// Trailer
						print('<div class="col-sm-3">');
						print($movie->trailerEmbed);
						print('<br><br></div></div>');
					}
					// Ne pas afficher le film et afficher le suivant
					else
					{
						$sauterFilmSuivant = false;
					}
				}

				// Fermer le HTML
				print('</div></body>');
			}
		}

		//print_r($cinemasEtFilms->getArray()); // Afficher toutes les donnees
  }
  catch (ErrorException $error)
  {
    // En cas d'erreur
    echo "Erreur numero ", $error->getCode(), " : ", $error->getMessage(), PHP_EOL;
  }
?>
