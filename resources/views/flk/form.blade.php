<!DOCTYPE html>
<html>
<head>
    <title>Formulir Lamaran Kerja (FLK)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="text-center mb-4">
    <h2 style="background-color: #d4edda; display: inline-block; padding: 10px 20px; border-radius: 8px;">
        <b>Formulir Lamaran Kerja (FLK)</b>
    </h2>
</div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('flk.submit') }}">
        @csrf

        <!-- ============================
             DATA PRIBADI
        ============================== -->
        <div class="row mb-3">
    <div class="col-md-6">
        <label for="posisi" class="form-label"><b>Posisi yang Dilamar</b></label>
        <input type="text" name="posisi" id="posisi" class="form-control" placeholder="Contoh: IT STAFF" required>
    </div>
    <div class="col-md-6">
        <label for="gaji" class="form-label"><b>Gaji yang Diharapkan</b></label>
        <input type="text" name="gaji" id="gaji" class="form-control" placeholder="Contoh: 3.000.000 - 3.500.000" required>
    </div>
</div>
        <h4><b>Data Pribadi</b></h4>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-control">
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Tempat Lahir</label>
                <input type="text" name="tempat_lahir" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Usia</label>
                <input type="number" name="usia" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Agama</label>
                <input type="text" name="agama" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Suku Bangsa</label>
                <input type="text" name="suku" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Status Perkawinan</label>
                <select name="status_kawin" class="form-control">
                    <option value="Belum Menikah">Belum Menikah</option>
                    <option value="Menikah">Menikah</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label>No HP</label>
            <input type="text" name="no_hp" class="form-control">
        </div>

        <div class="mb-3">
            <label>Alamat Domisili</label>
            <textarea name="alamat_domisili" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-3">
            <label>Alamat KTP</label>
            <textarea name="alamat_ktp" class="form-control" rows="2"></textarea>
        </div>
        <div class="mb-3">
    <label>SIM Apa yang Anda Miliki</label><br>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="sim[]" value="A" id="sim_a">
        <label class="form-check-label" for="sim_a">A</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="sim[]" value="C" id="sim_c">
        <label class="form-check-label" for="sim_c">C</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="sim[]" value="B1" id="sim_b1">
        <label class="form-check-label" for="sim_b1">B1</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="sim[]" value="B2" id="sim_b2">
        <label class="form-check-label" for="sim_b2">B2</label>
    </div>
</div>


        <h4 class="mt-5"><b>LATAR BELAKANG KELUARGA</b></h4>

<!-- ORANG TUA -->
<h5 class="mt-3">Orang Tua</h5>
<div class="row mb-3">
    <div class="col-md-3">
        <label>Nama Ayah</label>
        <input type="text" name="ayah_nama" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Usia Ayah</label>
        <input type="number" name="ayah_usia" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Pendidikan Terakhir</label>
        <input type="text" name="ayah_pendidikan" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Pekerjaan</label>
        <input type="text" name="ayah_pekerjaan" class="form-control">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <label>Nama Ibu</label>
        <input type="text" name="ibu_nama" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Usia Ibu</label>
        <input type="number" name="ibu_usia" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Pendidikan Terakhir</label>
        <input type="text" name="ibu_pendidikan" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Pekerjaan</label>
        <input type="text" name="ibu_pekerjaan" class="form-control">
    </div>
</div>

<!-- PASANGAN -->
<h5 class="mt-3">Pasangan (jika ada)</h5>
<div class="row mb-3">
    <div class="col-md-3">
        <label>Nama Pasangan</label>
        <input type="text" name="pasangan_nama" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Usia</label>
        <input type="number" name="pasangan_usia" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Pendidikan</label>
        <input type="text" name="pasangan_pendidikan" class="form-control">
    </div>
    <div class="col-md-3">
        <label>Pekerjaan</label>
        <input type="text" name="pasangan_pekerjaan" class="form-control">
    </div>
</div>

<!-- ANAK -->
<h5 class="mt-3">Anak</h5>
<div class="mb-3">
    <label>Jumlah Anak</label>
    <input type="number" name="jumlah_anak" class="form-control" placeholder="Masukkan jumlah anak jika ada">
</div>

<!-- SAUDARA -->
<h5 class="mt-4">Saudara Kandung</h5>
<table class="table table-bordered" id="tabel-saudara">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Usia</th>
            <th>Pendidikan Terakhir</th>
            <th>Pekerjaan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input name="saudara_nama[]" type="text" class="form-control"></td>
            <td><input name="saudara_usia[]" type="number" class="form-control"></td>
            <td><input name="saudara_pendidikan[]" type="text" class="form-control"></td>
            <td><input name="saudara_pekerjaan[]" type="text" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button></td>
        </tr>
    </tbody>
</table>
<button type="button" class="btn btn-secondary btn-sm mb-4" onclick="tambahBaris()">+ Tambah Saudara</button>

<script>
function tambahBaris() {
    const baris = document.querySelector('#tabel-saudara tbody tr');
    const clone = baris.cloneNode(true);
    clone.querySelectorAll('input').forEach(input => input.value = '');
    document.querySelector('#tabel-saudara tbody').appendChild(clone);
}
function hapusBaris(button) {
    const baris = button.closest('tr');
    const tabel = document.querySelector('#tabel-saudara tbody');
    if (tabel.rows.length > 1) {
        baris.remove();
    }
}
</script>

       <h4 class="mt-5"><b>LATAR BELAKANG PENDIDIKAN</b></h4>

<!-- =======================
     Pendidikan Formal
========================== -->
<h5 class="mt-3">1. Pendidikan Formal</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Tingkat</th>
            <th>Nama Sekolah / Universitas</th>
            <th>Jurusan</th>
            <th>Dari</th>
            <th>Sampai</th>
            <th>IPK / Nilai Akhir</th>
        </tr>
    </thead>
    <tbody>
        @foreach(['SMA/SMK', 'D3', 'S1', 'S2'] as $jenjang)
        <tr>
            <td><input type="text" name="pendidikan_formal[{{ $jenjang }}][tingkat]" class="form-control" value="{{ $jenjang }}" readonly></td>
            <td><input type="text" name="pendidikan_formal[{{ $jenjang }}][nama]" class="form-control"></td>
            <td><input type="text" name="pendidikan_formal[{{ $jenjang }}][jurusan]" class="form-control"></td>
            <td><input type="month" name="pendidikan_formal[{{ $jenjang }}][dari]" class="form-control"></td>
            <td><input type="month" name="pendidikan_formal[{{ $jenjang }}][sampai]" class="form-control"></td>
            <td><input type="text" name="pendidikan_formal[{{ $jenjang }}][ipk]" class="form-control"></td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- =======================
     Pendidikan Non Formal
========================== -->
<h5 class="mt-4">2. Pendidikan Non Formal (Kursus, Pelatihan, Seminar, dll)</h5>
<table class="table table-bordered" id="tabel-nonformal">
    <thead>
        <tr>
            <th>Nama Kursus / Pelatihan</th>
            <th>Penyelenggara</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input name="nonformal_nama[]" type="text" class="form-control"></td>
            <td><input name="nonformal_penyelenggara[]" type="text" class="form-control"></td>
            <td><input name="nonformal_tanggal[]" type="date" class="form-control"></td>
            <td><input name="nonformal_keterangan[]" type="text" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisNonformal(this)">Hapus</button></td>
        </tr>
    </tbody>
</table>
<button type="button" class="btn btn-secondary btn-sm mb-3" onclick="tambahBarisNonformal()">+ Tambah Kursus</button>

<script>
function tambahBarisNonformal() {
    const baris = document.querySelector('#tabel-nonformal tbody tr');
    const clone = baris.cloneNode(true);
    clone.querySelectorAll('input').forEach(input => input.value = '');
    document.querySelector('#tabel-nonformal tbody').appendChild(clone);
}
function hapusBarisNonformal(button) {
    const baris = button.closest('tr');
    const tabel = document.querySelector('#tabel-nonformal tbody');
    if (tabel.rows.length > 1) {
        baris.remove();
    }
}
</script>

<h4 class="mt-5"><b>KEMAMPUAN</b></h4>

<!-- Kemampuan Bahasa -->
<h5 class="mt-3">1. Kemampuan Berbahasa</h5>
<p>Mohon berikan tanda checklist (√) sesuai dengan kemampuan berbahasa Anda:</p>
<div class="mb-3">
  <label class="form-label">Bahasa Inggris</label><br>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="inggris_lisan" id="inggris_lisan" value="1">
    <label class="form-check-label" for="inggris_lisan">Lisan</label>
  </div>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="inggris_tulisan" id="inggris_tulisan" value="1">
    <label class="form-check-label" for="inggris_tulisan">Tulisan</label>
  </div>
</div>
<div class="mb-3">
  <label class="form-label">Bahasa Mandarin</label><br>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="mandarin_lisan" id="mandarin_lisan" value="1">
    <label class="form-check-label" for="mandarin_lisan">Lisan</label>
  </div>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="mandarin_tulisan" id="mandarin_tulisan" value="1">
    <label class="form-check-label" for="mandarin_tulisan">Tulisan</label>
  </div>
</div>

<!-- Kemampuan Komputer -->
<h5 class="mt-4">2. Komputerisasi</h5>
<p>Centang dan sebutkan:</p>
<div class="mb-3">
  <label class="form-label">Hardware</label><br>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="komp_hardware_ok" id="komp_hardware_ok" value="1">
    <label class="form-check-label" for="komp_hardware_ok">✓</label>
  </div>
  <input type="text" name="komp_hardware" class="form-control d-inline-block w-75" placeholder="">
</div>
<div class="mb-3">
  <label class="form-label">Software</label><br>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="komp_software_ok" id="komp_software_ok" value="1">
    <label class="form-check-label" for="komp_software_ok">✓</label>
  </div>
  <input type="text" name="komp_software" class="form-control d-inline-block w-75" placeholder="">
</div>

<h4 class="mt-5"><b>LATAR BELAKANG ORGANISASI & PRESTASI</b></h4>

<!-- 1. Aktivitas Sosial -->
<h5 class="mt-3">1. Aktivitas Sosial</h5>
<table class="table table-bordered" id="tabel-organisasi">
  <thead>
    <tr>
      <th>Nama Organisasi</th>
      <th>Bidang</th>
      <th>Periode Kepesertaan</th>
      <th>Keterangan</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><input type="text" name="org_nama[]" class="form-control" placeholder=""></td>
      <td><input type="text" name="org_bidang[]" class="form-control" placeholder=""></td>
      <td><input type="text" name="org_periode[]" class="form-control" placeholder=""></td>
      <td><input type="text" name="org_keterangan[]" class="form-control" placeholder=""></td>
      <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisOrg(this)">Hapus</button></td>
    </tr>
  </tbody>
</table>
<button type="button" class="btn btn-secondary btn-sm mb-4" onclick="tambahBarisOrg()">+ Tambah Aktivitas</button>

<!-- 2. Penghargaan -->
<h5 class="mt-4">2. Penghargaan</h5>
<table class="table table-bordered" id="tabel-penghargaan">
  <thead>
    <tr>
      <th>Prestasi</th>
      <th>Tahun</th>
      <th>Keterangan</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><input type="text" name="penghargaan_nama[]" class="form-control" placeholder=""></td>
      <td><input type="number" name="penghargaan_tahun[]" class="form-control" placeholder=""></td>
      <td><input type="text" name="penghargaan_keterangan[]" class="form-control" placeholder=""></td>
      <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisPh(this)">Hapus</button></td>
    </tr>
  </tbody>
</table>
<button type="button" class="btn btn-secondary btn-sm mb-4" onclick="tambahBarisPh()">+ Tambah Penghargaan</button>

<script>
  function tambahBarisOrg() {
    const baris = document.querySelector('#tabel-organisasi tbody tr');
    const clone = baris.cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = '');
    document.querySelector('#tabel-organisasi tbody').appendChild(clone);
  }
  function hapusBarisOrg(btn) {
    const tbody = document.querySelector('#tabel-organisasi tbody');
    if (tbody.rows.length > 1) btn.closest('tr').remove();
  }

  function tambahBarisPh() {
    const baris = document.querySelector('#tabel-penghargaan tbody tr');
    const clone = baris.cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = '');
    document.querySelector('#tabel-penghargaan tbody').appendChild(clone);
  }
  function hapusBarisPh(btn) {
    const tbody = document.querySelector('#tabel-penghargaan tbody');
    if (tbody.rows.length > 1) btn.closest('tr').remove();
  }
</script>

        <!-- ============================ -->
        <button type="submit" class="btn btn-primary">Kirim Formulir</button>
    </form>
</body>
</html>
