<?php
include("config.php");
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

            var vigo = {
                lat: 42.2260878,
                lng: -8.7429354
            };
            var madrid = {
                lat: 40.4379332,
                lng: -3.7495756
            };
            var city = new google.maps.Marker({
                position: vigo,
                map: map,
                icon: icon,
            });
            var city = new google.maps.Marker({
                position: madrid,
                map: map,
                icon: icon,
            });

        }
    </script>
</head>

<body>
    <div class="jugador">
        <h1>Abraham</h1>
        <h2>Gobierno: ESPAÑA</h2>
    </div>
    <div class="info-ciudad">
        <h1>Vigo</h1>
        <h2>Tropas: 1000</h2>
        <button type="button">Mover</button>
        <button type="button">Dividir</button>
        <button type="button">Combinar</button>

    </div>
    <div class="notificaciones">
        <h1>Notificaciones</h1>
        <div class="textbox">
            <h4><strong>Abraham</strong> ha capturado <strong>Vigo</strong>!</h4>
        </div>


    </div>
    <div class="ciudades">
        <div class="ciudad">
            <img src="edificio.png" alt="">
            <h2>Vigo</h2>
        </div>
        <div class="ciudad">
            <img src="edificio.png" alt="">
            <h2>Madrid</h2>
        </div>
    </div>
    <div id="map"></div>
    
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config[0]["google"] ?>&callback=initMap">
    </script>
</body>

</html>