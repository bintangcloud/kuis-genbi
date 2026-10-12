<!DOCTYPE html>
<html>
<head>
    <title>Kelola Soal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container">
        <h2 class="mb-4">Kelola Soal Kuis GenBI</h2>
        <a href="{{ route('admin.create') }}" class="btn btn-primary mb-3">+ Tambah Soal</a>
        <a href="{{ route('game') }}" class="btn btn-success mb-3" target="_blank">Lihat Game 🎮</a>
        <form action="{{ route('logout') }}" method="POST" class="d-inline float-end">
            @csrf <button class="btn btn-danger">Logout</button>
        </form>
        
        <table class="table table-bordered bg-white mt-3">
            <thead class="table-dark"><tr><th>Soal</th><th>A</th><th>B</th><th>Jawaban</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($questions as $item)
                <tr>
                    <td>{{ $item->q }}</td><td>{{ $item->a }}</td><td>{{ $item->b }}</td>
                    <td><span class="badge bg-info">{{ $item->ans == 0 ? 'A' : 'B' }}</span></td>
                    <td>
                        <a href="{{ route('admin.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.destroy', $item->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE') <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>