<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Daengweb - Upload image</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('upload.image') }}" method="post" enctype="multipart/form-data">
                        @csrf



                        <div class="form-group">
                            <label for="">Pilih gambar</label>
                            <input type="file" name="image">
                            <p class="text-danger">{{ $errors->first('image') }}</p>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-danger btn-sm">Upload</button>
                        </div>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="col-md-6 alert-success">
                    <p>Data Scan Passpor :</p>
                    {{ session('success') }}
                    <br/><br/>

                    <p>====================================================</p>

                    @if (session()->has('tipedokumen') == 'P' )
                        @php
                            $passport = 'Passport';
                            echo "<p>Tipe Dokumen : ".$passport."</p>";
                        @endphp

                    @endif


                    <p>No Passport : {{ session('nopassport') }} </p>

                    @if (session()->has('negaraissued') == 'IDN' )
                        @php
                            $negaraissued = 'INDONESIA';
                            echo "<p>Negara Passport : ".$negaraissued."</p>";
                        @endphp

                    @endif

                    <p>Tgl Expire : {{ session('tglexpire') }} </p>
                    <p>====================================================</p>
                    <p>Nama Awal : {{ session('namaawal') }} </p>
                    <p>Nama Belakang : {{ session('namabelakang') }} </p>
                    <p>No KTP : {{ session('noktp') }} </p>

                    @if (session()->has('gender') == 'M' )
                        @php
                            $gender = 'Laki-Laki';
                            echo "<p>Jenis Kelamin : ".$gender."</p>";
                        @endphp
                    @elseif(session()->has('gender') == 'M')

                        @php
                            $gender = 'Perempuan';
                            echo "<p>Jenis Kelamin : ".$gender."</p>";
                        @endphp

                    @endif

                    <p>Tgl Lahir : {{ session('tgllahir') }} </p>

                    @if (session()->has('warganegara') == 'IDN' )
                        @php
                            $warganegara = 'INDONESIA';
                            echo "<p>Warga Negara : ".$warganegara."</p>";
                        @endphp

                    @endif

                    <p><br/><br/></p>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>