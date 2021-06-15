<?php
include_once("config.php");
include_once("database.php");
include_once("funciones.php");
session_start();
if (isset($_SESSION["userid"])) {
    $sql = "SELECT * FROM gente WHERE id = " . $_SESSION["userid"];
    $do = mysqli_query($link, $sql);
    $datos_usuario = mysqli_fetch_assoc($do);
    $nombre = $datos_usuario["nombre"];
    $id_usuario = $datos_usuario["id"];
    $pais = $datos_usuario["pais"];
    $sql = "SELECT * FROM partidas WHERE id = 1";
    $do = mysqli_query($link, $sql);
    $datos_partida = mysqli_fetch_assoc($do);
    $actividad = $datos_partida["actividad"];
} else {
    header("location: login.php");
}
if (isset($_POST["reiniciar"])) {
    empezarpartida();
}
?>

<head>
    <meta charset="UTF-8">
    <title>SPAIN IS PAIN</title>
    <link rel="stylesheet" href="wargame.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@300&display=swap" rel="stylesheet">
    <style>
        #map {
            height: 100%;
        }

        /* Optional: Makes the sample page fill the window. */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
    <script>
        function hola(id, nombre) {
            console.log("Hola: " + id + " " + nombre)
        }
    </script>
    <script>
        function initMap() {
            var icon = {
                url: "edificio.png", // url
                scaledSize: new google.maps.Size(50, 60), // scaled size
                origin: new google.maps.Point(0, 0), // origin
                anchor: new google.maps.Point(10, 10) // anchor
            };
            var mapOptions = {
                zoom: 8,
                center: new google.maps.LatLng(42.2260878, -8.7429354),
                mapTypeId: 'satellite'
            };

            var map = new google.maps.Map(document.getElementById('map'), mapOptions);
            <?php
            $sql = "SELECT * FROM ciudades";
            $do = mysqli_query($link, $sql);
            while ($ciudad = mysqli_fetch_assoc($do)) {
            ?>
                var infowindow = new google.maps.InfoWindow({
                    content: '',
                });
                var ciudad = {
                    lat: <?php echo $ciudad["lat"] ?>,
                    lng: <?php echo $ciudad["lng"] ?>
                };
                var city = new google.maps.Marker({
                    position: ciudad,
                    map: map,
                    icon: icon,
                    title: "<?php echo $ciudad["nombre"] ?>",
                });
                bindInfoWindow(city, map, infowindow, "<p><?php echo $ciudad["nombre"] ?></p>");
                closeInfoWindow(city, map, infowindow);
                city.addListener("click", () => {
                    seleccionar_ciudad(<?php echo $ciudad["id"] ?>);
                });
            <?php
            }
            ?>

            function bindInfoWindow(marker, map, infowindow, html) {
                google.maps.event.addListener(marker, 'mouseover', function() {
                    infowindow.setContent(html);
                    infowindow.open(map, marker);
                });
            };

            function closeInfoWindow(marker, map, infowindow) {
                google.maps.event.addListener(marker, 'mouseout', function() {
                    infowindow.close(map, marker);
                });
            };

        }
    </script>
</head>

<body onload="updateall()">
    <div class="jugador">
        <h1><?php echo $nombre ?></h1>
        <h2>Pais: <?php echo $pais ?></h2>
    </div>
    <div class="info-ciudad">
        <div id="info-ciudad-var">
            <h1 id="info-ciudad-nombre"></h1>
            <h2 id="owner"></h2>
            <h2 id="info-ciudad-tropas-total"></h2>
            <div id="info-ciudad-tropas"></div>
        </div>
        <div id="buttons">

        </div>


    </div>
    <div class="notificaciones">
        <h1>Notificaciones</h1>
        <div class="textbox" id="actividad">
            <h4><?php echo $actividad; ?></h4>
        </div>


    </div>
    <div class="ciudades" id="ciudades">

    </div>
    <div class="controles">
        <form action="" method="POST">
            <button name="reiniciar">Reiniciar</button>
        </form>
    </div>
    <div class="dinero" id="dinero">
        <h1>1000€</h1>
    </div>
    <div id="map"></div>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config[0]["google"] ?>&callback=initMap">
    </script>
</body>

</html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    function seleccionar_ciudad(id) {
        window.localStorage.setItem('selected', id);
        var nombre = document.getElementById("info-ciudad-nombre");
        var tropas = document.getElementById("info-ciudad-tropas");
        var tropas_total = document.getElementById("info-ciudad-tropas-total");
        var owner = document.getElementById('owner');
        var botones = document.getElementById('buttons');
        $.ajax('./ajax.php', {
            type: 'POST', // http method
            data: {
                id: id
            }, // data to submit
            success: function(data) {
                //alert(data);
                tropas.innerHTML = "";
                var result = JSON.parse(data);
                if (result.ocupada.estado == null) {
                    nombre.innerHTML = result.nombre;
                    owner.innerHTML = result.pais;
                    botones.innerHTML = result.botones;
                    var total_tropas = result.tropas.total;
                    tropas_total.innerHTML = "Tropas: " + total_tropas;
                    for (var i = 0; i != Object.keys(result.tropas.tipos).length; i++) {
                        tropas.innerHTML += "<p>" + result.tropas.tipos[i].nombre + ": " + result.tropas.tipos[i].cantidad + "</p>";
                    }
                } else {
                    tropas_total.innerHTML = "";
                    nombre.innerHTML = "OCUPADA...";
                    owner.innerHTML = result.pais;
                    botones.innerHTML = "<p>Comprando " + result.ocupada.nombre + "</p>";
                    botones.innerHTML = "<p id='timer_tropa'>Quedan " + (result.ocupada.final - result.ocupada.ahora) + " Segundos</p>"

                }
            },
            error: function(jqXhr, textStatus, errorMessage) {}
        });
    }

    function starthere(ciudad) {
        $.ajax('./ajax.php', {
            type: 'POST',
            data: {
                start: ciudad
            },
            success: function(data) {
                //alert(data);
                updateall();
            },
            error: function(jqXhr, textStatus, errorMessage) {}
        });
    }


    function comprartropa(tropa, ciudad) {
        $.ajax('./ajax.php', {
            type: 'POST',
            data: {
                comprar: tropa,
                ciudad: ciudad
            },
            success: function(data) {
                //alert(data);
                updateall();
                var data = JSON.parse(data);
                setInterval(function() {
                    updateall();
                }, 1000);
            },
            error: function(jqXhr, textStatus, errorMessage) {}
        });
    }

    function updateall() {
        var ciudades = document.getElementById("ciudades");
        var actividad = document.getElementById("actividad");
        var dinero = document.getElementById("dinero");
        $.ajax('./ajax.php', {
            type: 'POST',
            data: {
                log: 'player'
            },
            success: function(data) {
                //alert(data);
                var result = JSON.parse(data);
                if (localStorage.getItem("selected")) {
                    seleccionar_ciudad(localStorage.getItem("selected"));
                }
                dinero.innerHTML = "<h1>" + result.dinero + "€</h1>";
                actividad.innerHTML = "<h4>" + result.actividad + "</h4>";
                for (var i = 0; i != Object.keys(result.mano).length; i++) {
                    ciudades.innerHTML = '<div class="ciudad" onclick="seleccionar_ciudad(' + result.mano[i].id + ')"><img src="edificio.png" alt=""><h2>' + result.mano[i].nombre + '</h2></div>'
                }
            },
            error: function(jqXhr, textStatus, errorMessage) {}
        });
    }
</script>