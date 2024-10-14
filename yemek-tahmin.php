<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adana Trafik Durumu - Teknofest Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome için -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Teknofest Şablon Stilleri */
        body {
            background-image: url('/logos/teknofest.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container-custom {
            margin-top: 5%;
        }

        .content-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        /* Sol üst köşedeki logo */
        .logo-container-left {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1000;
        }

        .logo-container-left img {
            height: 50px;
        }

        /* Sağ üst köşedeki logo */
        .logo-container-right {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
        }

        .logo-container-right img {
            height: 50px;
        }

        /* Dashboard Kartları */
        .card {
            margin-bottom: 20px;
        }

        /* Harita Stilleri */
        .map {
            width: 100%;
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Genel Başlık */
        h1 {
            margin-bottom: 30px;
        }

        /* Responsive Haritalar */
        @media (min-width: 768px) {
            .traffic-container {
                flex-wrap: nowrap;
            }
            .map {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>

    <?php
    session_start();
    ?>

    <!-- Sol Üst Logo -->
    <div class="logo-container-left">
        <img src="/logos/Teknofest_logo.png" alt="Teknofest">
    </div>

    <!-- Sağ Üst Logo -->
    <div class="logo-container-right">
        <img src="/logos/t3_logo.jpg" alt="T3 Logo">
    </div>

    <div class="container container-custom">
        <div class="row">
            <div class="col-12">
                <div class="content-container">
                    <!-- Genel Başlık -->
                    <h1 class="text-center"><i class="fas fa-road"></i> Adana Trafik Durumu Dashboard</h1>

                    <!-- Rotalar için Trafik Durumu Kartları -->
                    <div class="row">
                        <?php foreach ($traffic_data as $data): ?>
                            <div class="col-md-3">
                                <div class="card text-white 
                                    <?php
                                        switch ($data['trafik_durumu']) {
                                            case 'Trafik yok':
                                                echo 'bg-success';
                                                break;
                                            case 'Trafik var':
                                                echo 'bg-warning';
                                                break;
                                            case 'Yoğun trafik var':
                                                echo 'bg-danger';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                                break;
                                        }
                                    ?>
                                ">
                                    <div class="card-body">
                                        <div class="card-title">
                                            <i class="fas fa-map-marker-alt fa-2x"></i>
                                            <span class="float-right"><?php echo htmlspecialchars($data['trafik_durumu']); ?></span>
                                        </div>
                                        <h5 class="card-text"><?php echo htmlspecialchars($data['rota']); ?></h5>
                                        <p class="card-text">Normal Süre: <?php echo htmlspecialchars($data['normal_sure']); ?></p>
                                        <p class="card-text">Trafikli Süre: <?php echo htmlspecialchars($data['trafikli_sure']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Haritalar -->
                    <div class="traffic-container d-flex flex-wrap justify-content-between">
                        <?php foreach ($routes as $route_name => $coords): ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <?php echo htmlspecialchars($route_name); ?>
                                    </div>
                                    <div class="card-body">
                                        <div id="map_<?php echo md5($route_name); ?>" class="map"></div>
                                        <div class="status text-center mt-2">Trafik Durumu: <?php echo htmlspecialchars($traffic_data[array_search($route_name, array_column($traffic_data, 'rota'))]['trafik_durumu']); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Grafikler Bölümü Kaldırıldı -->

                </div> <!-- .content-container -->
            </div> <!-- .col-12 -->
        </div> <!-- .row -->
    </div> <!-- .container -->

    <!-- Google Maps API ve Haritalar Scriptleri -->
    <script>
        function initMap() {
            <?php foreach ($routes as $route_name => $coords): 
                $map_id = md5($route_name);
                // Koordinatları JavaScript formatına dönüştür
                $lat = $coords['origin'][0];
                $lng = $coords['origin'][1];
            ?>
                // <?php echo htmlspecialchars($route_name); ?> Haritası
                var origin_<?php echo $map_id; ?> = {lat: <?php echo $lat; ?>, lng: <?php echo $lng; ?>};
                var map_<?php echo $map_id; ?> = new google.maps.Map(document.getElementById('map_<?php echo $map_id; ?>'), {
                    zoom: 14,
                    center: origin_<?php echo $map_id; ?>
                });
                var marker_<?php echo $map_id; ?> = new google.maps.Marker({
                    position: origin_<?php echo $map_id; ?>,
                    map: map_<?php echo $map_id; ?>,
                    title: '<?php echo htmlspecialchars($route_name); ?>'
                });

                // Rotanın hedef noktasını haritaya ekle
                var destination_<?php echo $map_id; ?> = {lat: <?php echo $coords['destination'][0]; ?>, lng: <?php echo $coords['destination'][1]; ?>};
                var marker_dest_<?php echo $map_id; ?> = new google.maps.Marker({
                    position: destination_<?php echo $map_id; ?>,
                    map: map_<?php echo $map_id; ?>,
                    title: '<?php echo htmlspecialchars($route_name); ?> - Hedef'
                });

                // İki nokta arasında rota çizmek için DirectionsService kullanımı
                var directionsService_<?php echo $map_id; ?> = new google.maps.DirectionsService();
                var directionsRenderer_<?php echo $map_id; ?> = new google.maps.DirectionsRenderer();
                directionsRenderer_<?php echo $map_id; ?>.setMap(map_<?php echo $map_id; ?>);

                directionsService_<?php echo $map_id; ?>.route(
                    {
                        origin: origin_<?php echo $map_id; ?>,
                        destination: destination_<?php echo $map_id; ?>,
                        travelMode: 'DRIVING'
                    },
                    function(response, status) {
                        if (status === 'OK') {
                            directionsRenderer_<?php echo $map_id; ?>.setDirections(response);
                        } else {
                            console.error('Directions request failed due to ' + status);
                        }
                    }
                );
            <?php endforeach; ?>
        }

        // Sayfa Yüklendiğinde Çalışacak Fonksiyonlar
        window.onload = function() {
            initMap();
        };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $API_KEY; ?>&callback=initMap" async defer></script>

    <!-- Bootstrap ve jQuery Scriptleri -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>