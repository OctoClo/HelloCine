<?php
	header("content-type: text/html; charset=ISO-8859-15");
	require_once "../api-allocine-helper.php";

	// Afficher le head du HTML
	print('<head>
					<title>HelloCine - Map</title>
					<link rel="icon" type="image/x-icon" href="img/cinema.ico" />
					<link href="css/bootstrap.min.css" rel="stylesheet">
				</head>');

	// Afficher le body du HTML
	print('<body>
					<div class="container">
						<div class="row">
							<div class="col-sm-12">
								<center><h1>HelloCine</h1></center><br><br>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-2"></div>
							<div class="col-sm-8">
								<div id="label-map"></div>
								<div id="map" style="width: 800px; height: 500px"></div>
							</div>
						</div>
					</div>
				</body>');

	// Créer l'objet qui s'occupera de la requête
  $helper = new AlloHelper;

	// Récupérer les paramètres passés en GET
	$lat = $_GET['latitude'];
	$long = $_GET['longitude'];
	$codeCinema = $_GET['cinema'];

	try
  {
		// Envoi de la requête pour les paramètres passés
    $cinemasEtFilms = $helper->showtimesByPosition($lat, $long, $radius=7);

		// Première boucle : Chercher le cinéma passé en paramètre
		foreach ($cinemasEtFilms->theaterShowtimes as $cinemaEtFilms)
		{
			if ($cinemaEtFilms['place']['theater']['code'] == $codeCinema)
			{
				$cinema = $cinemaEtFilms['place']['theater'];

				// Echapper le nom du cinéma pour les ' qui cassent tout
				$nomCinema = addslashes($cinema['name']);

				// Afficher le javascript du HTML : construire le marker selon le cinéma passé en paramètre
				print('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyClsGI_PEZZkgjdaMZmh-57pETIOa7ivfI&signed_in=true&callback=" async defer></script>
								<script type="text/javascript">
			  					var markers = [
										{
											"title":\''.$nomCinema.'\',
											"lat":\''.$cinema['geoloc']->lat.'\',
											"lng":\''.$cinema['geoloc']->long.'\',
											"description":\'<h5>'.$nomCinema.'</h5>'.$cinema['address']." - ".$cinema['postalCode']." ".$cinema['city'].'\'
										}
									];

							    window.onload = function () {
							      LoadMap();
							    }

							    function LoadMap() {
							      var mapOptions = {
							        center: new google.maps.LatLng(markers[0].lat, markers[0].lng),
							        zoom: 14,
							        mapTypeId: google.maps.MapTypeId.ROADMAP
							      };

							      var map = new google.maps.Map(document.getElementById("map"), mapOptions);

							      //Create and open InfoWindow.
							      var infoWindow = new google.maps.InfoWindow();

							      for (var i = 0; i < markers.length; i++) {
							        var data = markers[i];
							        var myLatlng = new google.maps.LatLng(data.lat, data.lng);
							        var marker = new google.maps.Marker({
							          position: myLatlng,
							          map: map,
							          title: data.title
							        });

							        //Attach click event to the marker.
							        (function (marker, data) {
							            google.maps.event.addListener(marker, "click", function (e) {
							              //Wrap the content inside an HTML DIV in order to set height and width of InfoWindow.
							              infoWindow.setContent("<div style = \'width:200px;min-height:40px\'>" + data.description + "</div>");
							              infoWindow.open(map, marker);
							            });
							        })(marker, data);

											document.getElementById("label-map").innerHTML = "Emplacement du cinema '.$cinema['name'].' :<br><br>";
							      }
							    }
								</script>');
			}
		}
	}
	catch (ErrorException $error)
  {
    // En cas d'erreur
    echo "Erreur numero ", $error->getCode(), " : ", $error->getMessage(), PHP_EOL;
  }
?>
