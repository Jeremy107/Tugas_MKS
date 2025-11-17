<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta Bebras Challenge 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Daftar Peserta Bebras Challenge 2025</h1>

<div class="school-list">
    <?php
    // Baca file JSON (server-side only; diblokir dari akses langsung via .htaccess)
    $json_file = 'data_sekolah.json';
    $json_data = file_get_contents($json_file);
    if ($json_data === false) {
        die("Error: Tidak dapat membaca file $json_file.");
    }
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error: Format JSON tidak valid di $json_file. " . json_last_error_msg());
    }

    // Kelompokkan data berdasarkan sekolah (untuk menampilkan daftar pendamping)
    $grouped_data = [];
    foreach ($data as $item) {
        $school_name = $item['sekolah'];
        if (!isset($grouped_data[$school_name])) {
            $grouped_data[$school_name] = [
                'pdf_file' => $item['pdf_file'], // Ambil PDF file dari entri pertama
                'pendamping_list' => []
            ];
        }
        // Gabungkan semua pendamping untuk sekolah ini (tanpa mengekspos kode verifikasi ke klien)
        foreach ($item['pendamping'] as $idx => $pendamping_nama) {
            $grouped_data[$school_name]['pendamping_list'][] = [
                'name' => $pendamping_nama,
                'idx' => $idx
            ];
        }
    }

    // Urutkan daftar sekolah
    ksort($grouped_data);

    foreach ($grouped_data as $school_name => $school_info) {
        $sekolah_nama = htmlspecialchars($school_name, ENT_QUOTES, 'UTF-8');
        $pdf_file = $school_info['pdf_file'];
        $pendamping_list = $school_info['pendamping_list'];

        echo "<div class='school-item'>";
        echo "<h3>$sekolah_nama</h3>";
        echo "<ul class='pendamping-list'>";

        foreach ($pendamping_list as $pendamping) {
            $pendamping_nama = htmlspecialchars($pendamping['name'], ENT_QUOTES, 'UTF-8');
            // Siapkan payload aman untuk JavaScript menggunakan json_encode
            $pdf_js = json_encode($pdf_file, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            $school_js = json_encode($school_name, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            $pendamping_js = json_encode($pendamping['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

            echo "<li>";
            echo "<strong>Pendamping:</strong> $pendamping_nama ";
            echo "<button class='download-btn' onclick='openModal($school_js, $pendamping_js, $pdf_js)'>Download PDF</button>";
            echo "</li>";
        }

        echo "</ul>";
        echo "</div>";
    }
    ?>
</div>

<!-- The Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Verifikasi Unduhan</h3>
        <p id="modal-pendamping-name"></p>
        <p>Masukkan 4 digit terakhir nomor telepon pendamping untuk mengunduh PDF.</p>
        <form id="verificationForm" method="POST" action="download.php">
            <input type="hidden" id="schoolInput" name="school">
            <input type="hidden" id="pendampingInput" name="pendamping">
            <label for="verificationCode">Kode Verifikasi (4 Digit):</label>
            <input type="text" id="verificationCode" name="code" placeholder="XXXX" maxlength="4" required>
            <div id="errorMessage" class="error-message"></div>
            <button type="submit">Verifikasi dan Unduh</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("myModal");

    function openModal(schoolName, pendampingName, pdfFile) {
        document.getElementById("schoolInput").value = schoolName;
        document.getElementById("pendampingInput").value = pendampingName;
        document.getElementById("modal-pendamping-name").textContent = "Pendamping: " + pendampingName + " (" + schoolName + ")";
        document.getElementById("verificationCode").value = ""; // Kosongkan input user
        document.getElementById("errorMessage").textContent = ""; // Kosongkan error
        modal.style.display = "block";
    }

    function closeModal() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    // Validasi ringan di sisi klien; verifikasi utama dilakukan di server
    document.getElementById("verificationForm").addEventListener("submit", function(event) {
        const errorMessageDiv = document.getElementById("errorMessage");
        errorMessageDiv.textContent = "";
        const input_code = document.getElementById("verificationCode").value.trim();
        if (input_code.length !== 4 || isNaN(input_code)) {
            event.preventDefault();
            errorMessageDiv.textContent = "Kode harus berupa 4 digit angka.";
            return false;
        }
        // Biarkan form submit ke server untuk verifikasi dan pengunduhan
        closeModal();
        return true;
    });
</script>

</body>
</html>