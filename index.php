<?php
session_start();

// ==========================================
// 1. CLASS INDUK (ENTITY) - MENU
// ==========================================
class Menu {
    protected $id;
    protected $nama;
    protected $harga;
    protected $stok;

    public function __construct($id, $nama, $harga, $stok = true) {
        $this->id = $id;
        $this->nama = $nama;
        $this->harga = $harga;
        $this->stok = $stok;
    }

    public function getId() { return $this->id; }
    public function getNama() { return $this->nama; }
    public function getHarga() { return $this->harga; }
    public function getStok() { return $this->stok; }

    public function displayInfo() {
        return "ID: " . $this->id . " | Menu: " . $this->nama . " | Harga: Rp " . number_format($this->harga, 0, ',', '.');
    }
}

// ==========================================
// 2. CLASS TURUNAN 1 - MAKANAN
// ==========================================
class Makanan extends Menu {
    private $tingkatPedas;

    public function __construct($id, $nama, $harga, $tingkatPedas, $stok = true) {
        parent::__construct($id, $nama, $harga, $stok);
        $this->tingkatPedas = $tingkatPedas;
    }

    public function getTingkatPedas() { return $this->tingkatPedas; }
    public function setTingkatPedas($tingkatPedas) { $this->tingkatPedas = $tingkatPedas; }

    public function displayInfo() {
        return parent::displayInfo() . " | Kategori: Makanan | Pedas: " . $this->tingkatPedas;
    }
}

// ==========================================
// 2. CLASS TURUNAN 2 - MINUMAN
// ==========================================
class Minuman extends Menu {
    private $ukuran;

    public function __construct($id, $nama, $harga, $ukuran, $stok = true) {
        parent::__construct($id, $nama, $harga, $stok);
        $this->ukuran = $ukuran;
    }

    public function getUkuran() { return $this->ukuran; }
    public function setUkuran($ukuran) { $this->ukuran = $ukuran; }

    public function displayInfo() {
        return parent::displayInfo() . " | Kategori: Minuman | Ukuran: " . $this->ukuran;
    }
}

// ==========================================
// CLASS TRANSAKSI KANTIN
// ==========================================
class TransaksiKantin {
    private $daftarPesanan = [];

    public function tambahPesanan(Menu $menu) {
        if ($menu->getStok()) {
            $this->daftarPesanan[] = $menu;
            return true;
        }
        return false;
    }

    public function hitungTotal() {
        $total = 0;
        foreach ($this->daftarPesanan as $item) {
            $total += $item->getHarga();
        }
        return $total;
    }

    public function hitungDiskon($total) {
        if ($total >= 50000) {
            return $total * 0.10;
        }
        return 0;
    }

    public function getDaftarPesanan() {
        return $this->daftarPesanan;
    }
}

// Katalog Utama Menu Kantin
$katalogUtama = [
    "MN-01" => ["nama" => "Nasi Goreng Spesial", "harga" => 20000, "kategori" => "Makanan", "stok" => true],
    "MN-02" => ["nama" => "Ayam Geprek", "harga" => 15000, "kategori" => "Makanan", "stok" => true],
    "MN-03" => ["nama" => "Sate Ayam", "harga" => 22000, "kategori" => "Makanan", "stok" => true],
    "MN-04" => ["nama" => "Mie Ayam", "harga" => 15000, "kategori" => "Makanan", "stok" => true],
    "MN-05" => ["nama" => "Es Teh Manis", "harga" => 5000, "kategori" => "Minuman", "stok" => true],
    "MN-06" => ["nama" => "Es Jeruk", "harga" => 7000, "kategori" => "Minuman", "stok" => true],
    "MN-07" => ["nama" => "Air Putih", "harga" => 3000, "kategori" => "Minuman", "stok" => true],
    "MN-08" => ["nama" => "Es Kelapa", "harga" => 10000, "kategori" => "Minuman", "stok" => false],
    "MN-09" => ["nama" => "Jus Alpukat", "harga" => 12000, "kategori" => "Minuman", "stok" => false]
];

// Session untuk menyimpan pesanan
if (!isset($_SESSION['pesanan_input'])) {
    $_SESSION['pesanan_input'] = [];
}

$sudahInputData = false;

// Proses Form Input Pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $idMenu = $_POST['nomor_menu'];

    if (isset($katalogUtama[$idMenu]) && $katalogUtama[$idMenu]['stok']) {
        $item = $katalogUtama[$idMenu];

        if ($item['kategori'] === 'Makanan') {
            $levelPedas = $_POST['level_pedas'] ?? 'Level 1';
            $pesananBaru = new Makanan($idMenu, $item['nama'], $item['harga'], $levelPedas, true);
        } else {
            $ukuran = $_POST['ukuran'] ?? 'Medium';
            $pesananBaru = new Minuman($idMenu, $item['nama'], $item['harga'], $ukuran, true);
        }

        $_SESSION['pesanan_input'][] = $pesananBaru;
        $_SESSION['sudah_input'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (isset($_SESSION['sudah_input']) && $_SESSION['sudah_input'] === true && !empty($_SESSION['pesanan_input'])) {
    $sudahInputData = true;
}

if (isset($_GET['reset'])) {
    unset($_SESSION['pesanan_input']);
    unset($_SESSION['sudah_input']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Menghitung Transaksi Pesanan
$transaksi = new TransaksiKantin();
foreach ($_SESSION['pesanan_input'] as $item) {
    $transaksi->tambahPesanan($item);
}

$subtotal = $transaksi->hitungTotal();
$diskon = $transaksi->hitungDiskon($subtotal);
$totalBayar = $subtotal - $diskon;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pemesanan Kantin</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f4f7f6;
            padding: 30px 15px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(255, 126, 95, 0.3);
            margin-bottom: 25px;
        }
        .form-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        .form-box h3 {
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-group {
            flex: 1;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background-color: #fff;
        }
        .btn {
            background-color: #ff7e5f;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            width: 100%;
        }
        .btn:hover { background-color: #e66b4e; }
        .btn-reset {
            background-color: #718093;
            text-align: center;
            display: block;
            text-decoration: none;
            margin-top: 10px;
            color: white;
            padding: 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        /* GRID KATALOG MENU */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-left: 5px solid #ff7e5f;
        }
        .card.minuman { border-left-color: #4facfe; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
        }
        .badge-makanan { background-color: #ff7e5f; }
        .badge-minuman { background-color: #4facfe; }
        .badge-stok-ada { background-color: #2ed573; font-size: 11px; }
        .badge-stok-habis { background-color: #ff4757; font-size: 11px; }
        
        .card h3 { font-size: 18px; color: #333; margin-bottom: 8px; }
        .card p { color: #666; font-size: 14px; margin: 4px 0; }

        /* STRUK BILL RESTO */
        .bill-box {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            max-width: 450px;
            margin: 0 auto 25px auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            font-family: 'Courier New', Courier, monospace;
            border-top: 6px solid #333;
        }
        .bill-header { text-align: center; border-bottom: 2px dashed #bbb; padding-bottom: 15px; margin-bottom: 15px; }
        .bill-header h2 { font-size: 20px; color: #222; }
        .bill-header p { font-size: 12px; color: #666; }
        .bill-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .bill-table th { text-align: left; font-size: 13px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .bill-table td { padding: 6px 0; font-size: 13px; color: #333; }
        .bill-table td.price { text-align: right; }
        .bill-item-detail { font-size: 11px; color: #777; display: block; }
        .bill-divider { border-top: 2px dashed #bbb; margin: 10px 0; }
        .bill-row { display: flex; justify-content: space-between; font-size: 13px; padding: 3px 0; }
        .bill-row.total { font-size: 16px; font-weight: bold; color: #27ae60; margin-top: 5px; }
        .bill-footer { text-align: center; margin-top: 15px; font-size: 11px; color: #888; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🍱 Sistem Pemesanan Kantin</h1>
        <p>Implementasi Object-Oriented Programming (OOP) PHP</p>
    </div>

    <!-- Form Pilih Nomor Menu & Kategori -->
    <div class="form-box">
        <h3>➕ Pilih & Input Pesanan</h3>
        <form action="" method="POST">
            <div class="form-row">
                <!-- Fitur Pilihan Kategori -->
                <div class="form-group">
                    <label>Filter Kategori:</label>
                    <select id="filterKategori" onchange="filterMenuKategori()">
                        <option value="Semua">-- Semua Kategori --</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                    </select>
                </div>

                <!-- Pilihan Nomor Menu (MN-01, MN-02, dst) -->
                <div class="form-group">
                    <label>Pilih Kode Menu (Tersedia):</label>
                    <select name="nomor_menu" id="selectMenu" onchange="toggleOption()" required>
                        <option value="">-- Pilih Kode Menu --</option>
                        <?php foreach ($katalogUtama as $id => $item): ?>
                            <?php if ($item['stok']): ?>
                                <option value="<?php echo $id; ?>" data-kategori="<?php echo $item['kategori']; ?>">
                                    [<?php echo $id; ?>] <?php echo $item['nama']; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <!-- Pilihan Pedas (Khusus Makanan) -->
                <div class="form-group" id="groupPedas">
                    <label>Tingkat Pedas:</label>
                    <select name="level_pedas">
                        <option value="Level 1">Level 1 (Tidak Pedas)</option>
                        <option value="Level 2">Level 2 (Sedang)</option>
                        <option value="Level 3">Level 3 (Pedas)</option>
                        <option value="Level 4">Level 4 (Sangat Pedas)</option>
                        <option value="Level 5">Level 5 (Extra Pedas)</option>
                    </select>
                </div>

                <!-- Pilihan Ukuran (Khusus Minuman) -->
                <div class="form-group" id="groupUkuran" style="display: none;">
                    <label>Ukuran Minuman:</label>
                    <select name="ukuran">
                        <option value="Small">Small</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="Large">Large</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="tambah" class="btn">Tambah ke Pesanan</button>
            <?php if ($sudahInputData): ?>
                <a href="?reset=true" class="btn-reset" onclick="return confirm('Reset seluruh pesanan?')">Reset Pesanan</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Katalog Menu Berwarna -->
    <div class="grid">
        <?php foreach ($katalogUtama as $id => $item): ?>
            <?php 
                $isMakanan = $item['kategori'] === 'Makanan';
                $kategoriClass = $isMakanan ? '' : 'minuman';
                $badgeClass = $isMakanan ? 'badge-makanan' : 'badge-minuman';
            ?>
            <div class="card <?php echo $kategoriClass; ?>">
                <div>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $item['kategori']; ?></span>
                    <?php if ($item['stok']): ?>
                        <span class="badge badge-stok-ada">Tersedia</span>
                    <?php else: ?>
                        <span class="badge badge-stok-habis">Tidak Tersedia</span>
                    <?php endif; ?>
                </div>
                <h3>[<?php echo $id; ?>] <?php echo $item['nama']; ?></h3>
                <p><strong>Kategori:</strong> <?php echo $item['kategori']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- STRUK BILL RESTORAN -->
    <?php if ($sudahInputData): ?>
    <div class="bill-box">
        <div class="bill-header">
            <h2>KANTIN DIGITAL</h2>
            <p>Struk Pembayaran Pesanan</p>
            <p><?php echo date("d/m/Y H:i"); ?> WIB</p>
        </div>

        <table class="bill-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: right;">Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaksi->getDaftarPesanan() as $pesanan): ?>
                <tr>
                    <td>
                        <strong><?php echo $pesanan->getNama(); ?></strong>
                        <span class="bill-item-detail">
                            <?php 
                                if ($pesanan instanceof Makanan) {
                                    echo "Pedas: " . $pesanan->getTingkatPedas();
                                } else {
                                    echo "Ukuran: " . $pesanan->getUkuran();
                                }
                            ?>
                        </span>
                    </td>
                    <td class="price">Rp <?php echo number_format($pesanan->getHarga(), 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="bill-divider"></div>

        <div class="bill-row">
            <span>Subtotal:</span>
            <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
        </div>
        <div class="bill-row">
            <span>Diskon (10% min. 50rb):</span>
            <span style="color: #e74c3c;">- Rp <?php echo number_format($diskon, 0, ',', '.'); ?></span>
        </div>

        <div class="bill-divider"></div>

        <div class="bill-row total">
            <span>TOTAL:</span>
            <span>Rp <?php echo number_format($totalBayar, 0, ',', '.'); ?></span>
        </div>

        <div class="bill-footer">
            <p>*** TERIMA KASIH ***</p>
            <p>Selamat Menikmati Hidangan Anda</p>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function filterMenuKategori() {
    var kategori = document.getElementById('filterKategori').value;
    var selectMenu = document.getElementById('selectMenu');
    var options = selectMenu.options;

    for (var i = 0; i < options.length; i++) {
        var opt = options[i];
        if (opt.value === "") continue;

        var optKategori = opt.getAttribute('data-kategori');
        if (kategori === "Semua" || optKategori === kategori) {
            opt.style.display = "";
        } else {
            opt.style.display = "none";
        }
    }
    selectMenu.value = "";
    toggleOption();
}

function toggleOption() {
    var select = document.getElementById('selectMenu');
    var selectedOption = select.options[select.selectedIndex];
    var kategori = selectedOption ? selectedOption.getAttribute('data-kategori') : '';
    
    var groupPedas = document.getElementById('groupPedas');
    var groupUkuran = document.getElementById('groupUkuran');

    if (kategori === 'Makanan') {
        groupPedas.style.display = 'block';
        groupUkuran.style.display = 'none';
    } else if (kategori === 'Minuman') {
        groupPedas.style.display = 'none';
        groupUkuran.style.display = 'block';
    } else {
        groupPedas.style.display = 'block';
        groupUkuran.style.display = 'none';
    }
}
</script>

</body>
</html>