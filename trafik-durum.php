<?php
// Google Maps API anahtarınızı buraya girin
$API_KEY = 'AIzaSyC_DJ8cTmC6yVIjjrfZ1WxYgyTOf5uZF84'; // Lütfen buraya geçerli bir API anahtarı girin

// Rotaların koordinatlarını tanımlayın
$routes = array(
    'Uçak Caddesi' => array(
        'origin' => array(36.97406410902629, 35.26413739991367),
        'destination' => array(36.987405838227076, 35.286042962205045)
    ),
    'Tekel Caddesi' => array(
        'origin' => array(36.987261967745766, 35.25822241529867),
        'destination' => array(36.974224282218884, 35.26405577987138)
    ),
    'D400' => array(
        'origin' => array(36.99417247167962, 35.28791699518535),
        'destination' => array(36.99336628976318, 35.2996378617872)
    ),
    'Obalar Caddesi' => array(
        'origin' => array(36.974316404285865, 35.279422490291076),
        'destination' => array(36.9778949083623, 35.30191056133447)
    )
);

// Trafik bilgilerini tutmak için bir dizi oluştur
$traffic_data = array();

// Her rota için trafik bilgisini hesaplayın
foreach ($routes as $route_name => $coords) {
    list($origin_latitude, $origin_longitude) = $coords['origin'];
    list($destination_latitude, $destination_longitude) = $coords['destination'];

    // Koordinatları 'lat,lng' formatına dönüştürün
    $origin = $origin_latitude . ',' . $origin_longitude;
    $destination = $destination_latitude . ',' . $destination_longitude;

    // API isteği için URL oluşturun
    $url = 'https://maps.googleapis.com/maps/api/directions/json?' .
           'origin=' . urlencode($origin) .
           '&destination=' . urlencode($destination) .
           '&mode=driving' .
           '&departure_time=now' .
           '&key=' . $API_KEY;

    // İstek gönder ve yanıtı al
    $response = @file_get_contents($url);
    
    if ($response === FALSE) {
        // İstek başarısız olduysa hata mesajı ekle
        $traffic_data[] = array(
            'rota' => $route_name,
            'trafik_durumu' => 'Veri alınamadı',
            'normal_sure' => '-',
            'trafikli_sure' => '-'
        );
        continue;
    }

    $data = json_decode($response, true);

    if (isset($data['status']) && $data['status'] == 'OK') {
        // Süre ve trafik bilgilerini al
        $leg = $data['routes'][0]['legs'][0];
        $duration = $leg['duration']['value']; // Trafik olmadan süre (saniye cinsinden)

        // duration_in_traffic mevcut mu kontrol et
        if (isset($leg['duration_in_traffic'])) {
            $duration_in_traffic = $leg['duration_in_traffic']['value'];
        } else {
            $duration_in_traffic = $duration;
        }

        // Trafik oranını hesapla
        $traffic_ratio = $duration_in_traffic / $duration;

        // Trafik durumunu belirle
        if ($traffic_ratio <= 1.1) {
            $traffic_status = "Trafik yok";
        } elseif ($traffic_ratio <= 1.4) {
            $traffic_status = "Trafik var";
        } else {
            $traffic_status = "Yoğun trafik var";
        }

        // Süreleri okunabilir metne dönüştür
        $normal_duration_text = isset($leg['duration']['text']) ? $leg['duration']['text'] : '-';
        $traffic_duration_text = isset($leg['duration_in_traffic']['text']) ? $leg['duration_in_traffic']['text'] : $leg['duration']['text'];

        // Trafik verilerini diziye ekle
        $traffic_data[] = array(
            'rota' => $route_name,
            'trafik_durumu' => $traffic_status,
            'normal_sure' => $normal_duration_text,
            'trafikli_sure' => $traffic_duration_text
        );
    } else {
        // Hata durumunda bilgileri diziye ekle
        $traffic_data[] = array(
            'rota' => $route_name,
            'trafik_durumu' => 'Veri alınamadı',
            'normal_sure' => '-',
            'trafikli_sure' => '-'
        );
    }
}
?>
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
