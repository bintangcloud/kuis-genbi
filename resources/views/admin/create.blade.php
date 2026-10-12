<!DOCTYPE html>
<html>
<head>
    <title>Form Soal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 600px;">
        <div class="card p-4">
            <h3 class="mb-3">{{ isset($question) ? 'Edit Soal' : 'Buat Soal Baru' }}</h3>
            <form action="{{ isset($question) ? route('admin.update', $question->id) : route('admin.store') }}" method="POST">
                @csrf @if(isset($question)) @method('PUT') @endif
                <div class="mb-3"><label>Pertanyaan</label><input type="text" name="q" class="form-control" value="{{ $question->q ?? '' }}" required></div>
                <div class="mb-3"><label>Pilihan A (Kiri)</label><input type="text" name="a" class="form-control" value="{{ $question->a ?? '' }}" required></div>
                <div class="mb-3"><label>Pilihan B (Kanan)</label><input type="text" name="b" class="form-control" value="{{ $question->b ?? '' }}" required></div>
                <div class="mb-3"><label>Jawaban Benar</label>
                    <select name="ans" class="form-select">
                        <option value="0" {{ (isset($question) && $question->ans == 0) ? 'selected' : '' }}>Opsi A</option>
                        <option value="1" {{ (isset($question) && $question->ans == 1) ? 'selected' : '' }}>Opsi B</option>
                    </select>
                </div>
                <div class="mb-3"><label>Penjelasan</label><textarea name="ex" class="form-control" required>{{ $question->ex ?? '' }}</textarea></div>
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('admin.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</body>
</html>