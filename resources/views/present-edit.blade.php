@extends('layouts.app')
@section('title', 'Edit Absensi')
@section('content')
<div class="container mt-4">
    <h2>Edit Absensi</h2>
    <form action="{{ route('present.update', $present->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Acara</label>
            <input type="text" name="acara" value="{{ old('acara', $present->acara) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="{{ old('tanggal', $present->tanggal) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Jam Buka</label>
            <div class="row">
                <div class="col-6">
                    <select name="jam_buka_jam" class="form-control" required>
                        @for($h = 0; $h <= 23; $h++)
                            <option value="{{ $h }}" {{ old('jam_buka_jam', \Carbon\Carbon::parse($present->jam_buka)->hour) == $h ? 'selected' : '' }}>
                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-6">
                    <select name="jam_buka_menit" class="form-control" required>
                        @for($m = 0; $m < 60; $m++)
                            <option value="{{ $m }}" {{ old('jam_buka_menit', \Carbon\Carbon::parse($present->jam_buka)->minute) == $m ? 'selected' : '' }}>
                                {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('present.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection