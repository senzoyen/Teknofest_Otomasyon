<!DOCTYPE html>

<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gönüllü Durum Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../style.css">

    <style>
        /* Mevcut CSS kodlarınız burada */
        body {
            background-color: #f4f4f9;
            color: #333;
            font-family: Arial, sans-serif;
            background-image: url('../logos/teknofest.jpg');
        }

        .container {
            margin-top: 5%;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            color: #4a4a4a;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .table {
            margin-bottom: 0;
            background-color: #fdfdfd;
            border-radius: 8px;
        }

        .table td, .table th {
            text-align: center;
            vertical-align: middle;
            color: #333;
            padding: 12px;
        }

        .table thead {
            background-color: #007bff;
            color: white;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .checked-green {
            background-color: #28a745;
            color: white;
        }

        .unchecked-red {
            background-color: #dc3545;
            color: white;
        }

        .image-gallery img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .image-gallery img:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card {
            border: 1px solid #ddd;
            margin-top: 20px;
            background-color: #f9f9f9;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        .card-body {
            padding: 15px;
            background-color: #D0D0D0;
        }

        .food-item {
            font-size: 0.9rem;
            padding: 5px;
        }

        .content-main h2 {
            color: #343a40;
            margin-bottom: 20px;
        }

        .content-main table th, .content-main table td {
            font-size: 14px;
        }
    </style>

</head>

<body>

<div class="container">
    <h1 class="text-center mb-5">Gönüllü Durum Dashboard</h1>

    <!-- Gün ve alan seçme formu -->
    <form method="post">
        <div class="mb-4">
            <label for="daySelect">Gün Seçin:</label>
            <select id="daySelect" name="selectedDay" class="form-control">
                <option value="">Seçin</option>
                <?php
                include '../dbconfig.php';
                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Bağlantı hatası: " . $conn->connect_error);
                }

                // 29 Eylül 2024 ve sonrası günleri çek
                $sql = "SELECT DISTINCT gun FROM gonullu_durum_form WHERE submitted_date >= '2024-09-29' ORDER BY gun DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $selected = (isset($_POST['selectedDay']) && $_POST['selectedDay'] == $row['gun']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row['gun']) . "' $selected>" . htmlspecialchars($row['gun']) . "</option>";
                    }
                }

                $conn->close();
                ?>
            </select>

            <label for="areaSelect">Alan Seçin:</label>
            <select id="areaSelect" name="selectedArea" class="form-control">
                <option value="">Seçin</option>
                <?php
                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Bağlantı hatası: " . $conn->connect_error);
                }

                // 29 Eylül 2024 ve sonrası alanları çek
                $sql = "SELECT DISTINCT alan FROM gonullu_durum_form WHERE submitted_date >= '2024-09-29' ORDER BY alan ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $selected = (isset($_POST['selectedArea']) && $_POST['selectedArea'] == $row['alan']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row['alan']) . "' $selected>" . htmlspecialchars($row['alan']) . "</option>";
                    }
                }

                $conn->close();
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Göster</button>
    </form>

    <!-- Buton -->
    <button id="toggleButton" class="btn btn-secondary mb-3" onclick="toggleTable()">Tabloyu Göster/Gizle</button>

    <main class="content-main">
        <div id="damacanaContainer" class="container-fluid" style="display: none;">
            <?php
            // Veritabanı bağlantısı
            include '../dbconfig.php';
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Bağlantı hatası: " . $conn->connect_error);
            }
            $conn->close();
            ?>

        </div>
    </main>

    <!-- Tablolar -->
    <div id="cateringContainer" style="display: none;">
        <?php
        include '../dbconfig.php';
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Bağlantı hatası: " . $conn->connect_error);
        }

        $areas = [
            'Teknofest Ofis', 'Kurumsal Ofis', 'T3 Ofis', 'Robolig', 'Engelsiz Yaşam',
            'Eğitim Teknolojileri', 'Finansal Teknolojiler', 'Tarım Teknolojileri', 'T3AI', 
            'TRT', 'Basın', 'Vip-1', 'Heysemist', 'Vip-2', 'Yönetim Ofis', 'Selçuk Bey VIP', 
            'VIP', 'Gönüllü - Bursiyer', 'Etkinlik Koordinatörlüğü', 'Kriz Masası', 
            'Yönetim Ofis (CIP)', 'Ulaşım Ofis', 'Pilot Event', 'TRT Kulis (ANA Sahne)', 
            'TRT Kulis (YAN Sahne)', 'Deneyap Makeathon', 'Şampiyonlar Ligi', 
            'Tematik çadırlar Vİdeo Ekibi', 'Tübitak', 'Vakıf Standı', 'Bilim Pavilyonu', 
            'Teknofest Ofis (TGS)', 'TSK'
        ];

        echo "<h3>Catering Kontrol Tablosu</h3>";
        echo "<table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Birim</th>
                        <th>9 Kontrolü</th>
                        <th>14.00 Kontrolü</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($areas as $area) {
            echo "<tr>";
            echo "<td>$area</td>";

            // Saat 09:00-11:00 arasında veri var mı kontrolü
            $today = date('Y-m-d');
            $sql = "SELECT COUNT(*) as count9 FROM gonullu_durum_form 
                    WHERE alan = '$area' AND submitted_date >= '2024-09-29' AND submitted_date = '$today' AND (saat BETWEEN '08:45:00' AND '11:00:00')";
            $result9 = $conn->query($sql);
            $row9 = $result9->fetch_assoc();
            $class9 = $row9['count9'] > 0 ? 'checked-green' : 'unchecked-red';

            // Saat 14:00-16:00 arasında veri var mı kontrolü
            $sql = "SELECT COUNT(*) as count16 FROM gonullu_durum_form 
                    WHERE alan = '$area' AND submitted_date >= '2024-09-29' AND submitted_date = '$today' AND (saat BETWEEN '13:50:00' AND '16:00:00')";
            $result16 = $conn->query($sql);
            $row16 = $result16->fetch_assoc();
            $class16 = $row16['count16'] > 0 ? 'checked-green' : 'unchecked-red';

            echo "<td class='$class9'>09:00-11:00 Kontrol</td>";
            echo "<td class='$class16'>14:00-16:00 Kontrol</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
        $conn->close();
        ?>
    </div>

    <div id="dataContainer">
        <?php
        if (isset($_POST['selectedDay']) && $_POST['selectedDay'] != '') {
            $selectedDay = $_POST['selectedDay'];
            $selectedArea = isset($_POST['selectedArea']) ? $_POST['selectedArea'] : '';

            include '../dbconfig.php';
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Bağlantı hatası: " . $conn->connect_error);
            }

            $isimOps = [
                '22849380860' => "Mustafa Çil",
                '21173001106' => "BERDA GÜNER",
                '52984509338' => "Esra Can",
                '47164130792' => "NİSA NUR KAYA",
                '19508822106' => "Berkant AĞUR",
                '37915618808' => "Yusuf Yılmaz",
                '16733141804' => "Yasemin Kara",
                '23881910800' => "Büşra Obut",
                '35488677772' => "Beyza Dursun",
                '28691205788' => "eda Gönen",
                '17266092632' => "Sena Arı",
                '35932658326' => "Hatice Tuğba Yaylak",
                '21821398824' => "Merve Yıldırım",
                '18658612490' => "Dicle Eylül GEZİCİ",
                '53656130638' => "Yiğit Mert Yozgatlı",
                '65953283214' => "aydınlı emine",
                '30596103298' => "TAHA YILMAZ TURGUT",
                '48445501966' => "ABİDİN ÜNLÜ ZEYNAL",
                '66052327236' => "Kürklü Ayşenur",
                '45733601896' => "Boyraz Ayşenur",
                '10768600480' => "Yıldırımci Sedef",
                '11921506892' => "ARIKAN MELİKE NUR",
                '10515096894' => "Ekinci Fatih Eren",
                '43471396388' => "ÖZGÜR Özkan",
                '10779096852' => "eren Hatice",
                '10527097742' => "KARADUMAN YAVUZ SELİM",
                '10237425026' => "Esmer Esma Nur",
                '10267494160' => "TOSUN MUHAMMED HÜSEYİN",
                '21764493624' => "Mete Kübra",
                '10034722212' => "EMRE EMİR",
                '25511401122' => "Gülmez Berire",
                '11475100324' => "Göregen Abdulkadir",
                '10654697716' => "SARAÇ MELTEM",
                '45379743394' => "Türkkol Gökçe",
                '15254071768' => "KURT AKİF EREN",
                '16171158112' => "Emine Doğan",
                '22876792216' => "Özer Muhammet Ertuğrul",
                '47176367988' => "Eren İncesulu",
                '58810233088' => "ÇELEBİ Ceren",
                '70489014058' => "Şirin Hafsanur",
                '10228698118' => "Naz Aslan",
                '30893227598' => "Yurtseven Arife",
                '39085714434' => "Şen Saim Görkem",
                '26755379734' => "Yılmaz Şule",
                '18098447552' => "BAYRAKTAR ANIL KAAN",
                '26231273242' => "BAŞ İrem",
                '22897231442' => "Bedirhan Dertli",
                '31657499510' => "Firat Kübra",
                '16171158122' => "Doğan Emine",
                '18425021890' => "BAL PETEK",
                '10409904816' => "TAŞKIN BERRA EZGİ",
                '21422191732' => "IDEM YUSUF",
                '35932160010' => "Duman Kübra",
                '30064656066' => "Öksüz Esra",
                '61552473766' => "doğan Sena",
                '11648444552' => "Ercan Eren",
                '13733301078' => "GÖREN YETER",
                '13682242378' => "MEHMET RECEP Algül",
                '34097116382' => "KARABACAK SILA EZEL",
                '21056343958' => "KARAMAN BUSE",
                '13316496610' => "Güney Azra Ece",
                '19292731318' => "Ceylan Yakup",
                '10138667204' => "Melike Hilal Akboğa",
                '16682038408' => "İclal Kotan",
                '13933257328' => "AYŞE NUR BORAN",
                '36070868110' => "Fatma gizem Arı",
                '39556431310' => "KÜBRA MERK",
                '10729263808' => "Buğra Şahin",
                '22908273144' => "Bedirhan Dertli",
                '18860590456' => "Esmanur Baysal",
                '26923738684' => "Murat Yalçın",
                '23660067336' => "Betül Şahin",
                '10112493898' => "AYGÜL AYDOĞDU",
                '10765970814' => "Asiye esmanur Tas",
                '41722518718' => "Furkan Ayrı",
                '23872670904' => "Melike Akhan"
            ];
            

            echo "<h3>Alan, Gün, Saat Bazında Gelen Bilgiler</h3>";
            $sql = "SELECT tc, gun, saat, alan, doluDamacanaAdet, bosDamacanaAdet, talepDamacanaAdet, teslimDamacanaAdet,
                    peynirli_su_boregi, mercimek_koftesi, tatli_kurabiye, tuzlu_kurabiye, islak_kek_tartolet,
                    mini_tiramisu, mini_brownie, mini_simit_pizza, zeytinyagli_yaprak_sarma, kanape_cesitleri,
                    mini_soguk_sandvic, meyve_cesitleri, kuru_yemis, kahve_cesitleri, cay, bitki_cayi,
                    cam_sise_su, mesrubat_cesitleri, portakal_suyu_limonata,
                    image_1, image_2, image_3, image_4, image_5, image_6, image_7, image_8, image_9, image_10
                    FROM gonullu_durum_form 
                    WHERE gun = '$selectedDay' AND submitted_date >= '2024-09-29'";

            if ($selectedArea != '') {
                $sql .= " AND alan = '$selectedArea'";
            }

            $sql .= " ORDER BY submitted_date DESC, submitted_time DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $gonderenTc = $row['tc'];
                    $gonderenIsim = isset($isimOps[$gonderenTc]) ? $isimOps[$gonderenTc] : "Bilinmiyor";

                    echo "<div class='card mb-4'>";
                    echo "<div class='card-header'>Gün: " . htmlspecialchars($row['gun']) . " | Saat: " . htmlspecialchars($row['saat']) . " | Alan: " . htmlspecialchars($row['alan']) . " | Gönderen: " . htmlspecialchars($gonderenIsim) . "</div>";
                    echo "<div class='card-body'>";

                    $yemek_listesi_var = false;
                    $yemek_keys = ['peynirli_su_boregi', 'mercimek_koftesi', 'tatli_kurabiye', 'tuzlu_kurabiye', 'islak_kek_tartolet', 
                                   'mini_tiramisu', 'mini_brownie', 'mini_simit_pizza', 'zeytinyagli_yaprak_sarma', 'kanape_cesitleri', 
                                   'mini_soguk_sandvic', 'meyve_cesitleri', 'kuru_yemis', 'kahve_cesitleri', 'cay', 'bitki_cayi', 
                                   'cam_sise_su', 'mesrubat_cesitleri', 'portakal_suyu_limonata'];
                    
                    foreach ($yemek_keys as $key) {
                        if ($row[$key] == 1) {
                            $yemek_listesi_var = true;
                            break;
                        }
                    }
                    

                    echo "<h5>Yemek Listesi:</h5><div class='row'>";
                    foreach ($yemek_keys as $key => $value) {
                        if ($row[$key]) {
                            echo "<div class='col-md-3 food-item'>$value</div>";
                        }
                    }
                    echo "</div>";

                    $image_path = 'demolar/images/' . htmlspecialchars($row[$image_col]);


                    echo "<h5>Resimler:</h5><div class='image-gallery'>";
                    for ($i = 1; $i <= 10; $i++) {
                        $image_col = 'image_' . $i;
                        if (!empty($row[$image_col])) {
                            $image_path = '../' . htmlspecialchars($row[$image_col]);
                            echo "<a href='" . htmlspecialchars($row[$image_col]) . "' target='_blank'><img src='" . htmlspecialchars($row[$image_col]) . "' alt='Resim'></a> ";
                        }
                    }
                    echo "</div>";
                    echo "</div></div>";
                }
            } else {
                echo "<p class='text-center'>Kayıt bulunamadı.</p>";
            }

            $conn->close();
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</div>
</body>

</html>
