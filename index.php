<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Aplikasi pembuat laporan kerja bulanan otomatis dengan ekspor DOCX berbasis template Word">
<title>Laporan Kerja Bulanan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="app-header">
  <div>
    <h1>📋 Laporan Kerja Bulanan</h1>
    <p>Input kegiatan, kehadiran, dan catatan laporan lalu ekspor ke Word sesuai template asli</p>
  </div>
  <div class="hdr-actions">
    <button class="btn btn-outline" onclick="openSettings()">⚙️ Pengaturan</button>
    <button class="btn btn-outline" onclick="saveData()">💾 Simpan</button>
    <button class="btn btn-outline" onclick="openHistory()">🕰️ Riwayat</button>
    <button class="btn btn-outline" onclick="doPrint()">🖨️ Cetak / PDF</button>
    <button class="btn btn-outline" onclick="doDownloadWord()">📄 Download Word</button>
    <a href="logout.php" class="btn btn-outline" style="color: #ef4444; border-color: #ef4444; text-decoration: none;">🚪 Keluar</a>
  </div>
</header>

<div class="wrap screen-ui">
  <div class="card">
    <div class="card-title">🗓️ Pilih Periode Laporan</div>
    <div class="form-grid">
      <div class="fg">
        <label>Laporan Bulan Ke-</label>
        <input type="number" id="reportNum" min="1" max="99" value="1">
      </div>
      <div class="fg">
        <label>Bulan</label>
        <select id="bulan">
          <option value="1">Januari</option><option value="2">Februari</option>
          <option value="3">Maret</option><option value="4">April</option>
          <option value="5">Mei</option><option value="6">Juni</option>
          <option value="7">Juli</option><option value="8">Agustus</option>
          <option value="9">September</option><option value="10">Oktober</option>
          <option value="11">November</option><option value="12">Desember</option>
        </select>
      </div>
      <div class="fg">
        <label>Tahun</label>
        <input type="number" id="tahun" min="2020" max="2035" value="2026">
      </div>
      <div class="fg" style="justify-content:flex-end;padding-top:18px">
        <button class="btn btn-primary btn-lg" onclick="generateReport()">✨ Generate Laporan</button>
      </div>
    </div>
  </div>

  <div id="reportSec" style="display:none">
    <div class="status-bar" id="statusBar">
      <div class="dot"></div>
      <span id="statusTxt">Memuat hari libur...</span>
    </div>

    <div class="tabs">
      <button class="tab-btn active" data-tab="keg" onclick="switchTab('keg')">📝 Kegiatan Harian</button>
      <button class="tab-btn" data-tab="had" onclick="switchTab('had')">🕐 Kehadiran</button>
      <button class="tab-btn" data-tab="cat" onclick="switchTab('cat')">📌 Catatan Laporan</button>
    </div>

    <div id="tab-keg">
      <div class="card">
        <div class="sec-hdr">
          <div class="sec-title">B. Kegiatan Harian</div>
          <span class="month-badge" id="kegBadge"></span>
        </div>
        <div class="alert alert-info">💡 Isi kolom <strong>Kegiatan</strong>, <strong>Uraian</strong>, <strong>Pemberi Tugas</strong>, dan <strong>Penyelenggara</strong> untuk setiap hari kerja.</div>
        <div class="tbl-wrap">
          <table class="act-tbl">
            <thead><tr>
              <th style="width:38px">No</th>
              <th style="width:110px">Hari/Tanggal</th>
              <th style="width:170px">Kegiatan</th>
              <th>Uraian Kegiatan dan Tindak Lanjut</th>
              <th style="width:130px">Pemberi Tugas</th>
              <th style="width:140px">Penyelenggara</th>
            </tr></thead>
            <tbody id="kegBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="tab-had" style="display:none">
      <div class="card">
        <div class="sec-hdr">
          <div class="sec-title">3. Kehadiran</div>
          <button class="btn btn-outline" style="margin-left:auto; margin-right:15px; padding:4px 10px; font-size:0.85rem;" onclick="randomizeAllAttendance()">🎲 Acak Jam Hadir</button>
          <span class="month-badge" id="hadBadge"></span>
        </div>
        <div class="alert alert-info">💡 Isi jam <strong>Masuk</strong> dan <strong>Pulang</strong> dengan format HH.MM. Untuk kondisi tidak hadir, pilih status <strong>Ijin</strong>, <strong>Sakit</strong>, <strong>Cuti</strong>, atau <strong>Tidak Masuk</strong>.</div>
        <div style="overflow-x:auto">
          <table class="att-tbl">
            <thead>
              <tr>
                <th rowspan="2" style="width:42px">No</th>
                <th rowspan="2" style="width:76px">Hari</th>
                <th rowspan="2">Tanggal</th>
                <th colspan="2">Kehadiran</th>
                <th rowspan="2">Status</th>
              </tr>
              <tr><th style="width:78px">Masuk</th><th style="width:78px">Pulang</th></tr>
            </thead>
            <tbody id="hadBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="tab-cat" style="display:none">
      <div class="card">
        <div class="sec-hdr">
          <div class="sec-title">C. Evaluasi, Saran, dan Penutup</div>
          <span class="month-badge" id="catBadge"></span>
        </div>
        <div class="alert alert-info">💡 Bagian ini dipakai untuk mengisi isi bab capaian, evaluasi, saran, dan penutup pada file Word.</div>
        <div class="form-grid notes-grid">
          <div class="fg full-span">
            <label>Capaian a</label>
            <textarea id="capaian1" rows="3" placeholder="Mengikuti beberapa kegiatan di lingkungan kementerian/lembaga lain."></textarea>
          </div>
          <div class="fg full-span">
            <label>Capaian b</label>
            <textarea id="capaian2" rows="3" placeholder="Memperbaiki beberapa fitur website JDIH pada server lokal."></textarea>
          </div>
          <div class="fg full-span">
            <label>Evaluasi</label>
            <textarea id="evaluasi1" rows="4" placeholder="Tulis evaluasi bulan ini..."></textarea>
          </div>
          <div class="fg full-span">
            <label>Saran</label>
            <textarea id="saran1" rows="4" placeholder="Tulis saran perbaikan..."></textarea>
          </div>
          <div class="fg full-span">
            <label>Penutup</label>
            <textarea id="penutup" rows="4" placeholder="Demikianlah laporan kegiatan bulanan ini dibuat..."></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="emptyState" class="card">
    <div class="empty">
      <div class="ico">📋</div>
      <h3>Belum ada laporan</h3>
      <p>Pilih bulan dan tahun di atas, lalu klik <strong>Generate Laporan</strong>.</p>
    </div>
  </div>
</div>

<div class="modal-overlay" id="settModal">
  <div class="modal">
    <h2>⚙️ Pengaturan Profil</h2>
    <div class="form-grid">
      <div class="fg"><label>Nama Lengkap</label><input id="sNama" placeholder="Bismar, S.T."></div>
      <div class="fg"><label>Judul Laporan</label><input id="sJudulLaporan" placeholder="Jasa Konsultansi Perorangan Programmer Website JDIH"></div>
      <div class="fg"><label>Jabatan pada TTD / Kehadiran</label><input id="sJabatan" placeholder="Tenaga Sub Profesional..."></div>
      <div class="fg"><label>Nama Atasan</label><input id="sAtasan" placeholder="Tuti Rohayati, S.H."></div>
      <div class="fg"><label>NIP Atasan</label><input id="sNIP" placeholder="197211271992032001"></div>
      <div class="fg"><label>Jabatan Atasan</label><input id="sJabAtasan" placeholder="Kepala Bagian..."></div>
      <div class="fg"><label>Instansi/Unit Kerja</label><input id="sInstansi" placeholder="Biro Hukum ATR/BPN"></div>
      <div class="fg"><label>Tanggal Mulai Kontrak</label><input id="sKAwal" placeholder="1 Februari 2026"></div>
      <div class="fg"><label>Tanggal Akhir Kontrak</label><input id="sKAkhir" placeholder="30 November 2026"></div>
      <div class="fg"><label>Jam Masuk Default</label><input id="sJMasuk" placeholder="07.58"></div>
      <div class="fg"><label>Jam Pulang Default</label><input id="sJPulang" placeholder="17.05"></div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-muted" onclick="closeSettings()">Batal</button>
      <button class="btn btn-primary" onclick="saveSettings()">💾 Simpan</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="histModal">
  <div class="modal" style="max-width: 600px;">
    <h2>🕰️ Riwayat Laporan</h2>
    <div id="histList" style="max-height: 400px; overflow-y: auto; margin-top: 15px;">
      <p style="text-align: center; color: #64748b;">Memuat riwayat...</p>
    </div>
    <div class="modal-actions" style="margin-top: 20px;">
      <button class="btn btn-muted" onclick="closeHistory()">Tutup</button>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>
<div class="print-area" id="printArea"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pizzip/3.1.8/pizzip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/docxtemplater/3.47.4/docxtemplater.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
