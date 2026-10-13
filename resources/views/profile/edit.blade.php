<x-layouts.app>

@section('title', 'Edit Profil — MH Scarf Store')


<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Edit Profil</h1>
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded mb-4" role="alert">
            <p class="font-semibold mb-1">Periksa kembali:</p>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-deep focus:border-pink-deep" required>
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-deep focus:border-pink-deep" required>
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Nomor Telepon / WhatsApp (opsional)</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-deep focus:border-pink-deep">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password Baru (kosongkan bila tidak diganti)</label>
            <input type="password" name="password" id="password"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-deep focus:border-pink-deep">
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-deep focus:border-pink-deep">
        </div>
        <div class="flex items-center justify-between">
            <button type="submit"
                class="inline-flex items-center justify-center px-4 py-2 bg-pink-deep hover:bg-pink-mauve text-white font-medium rounded-lg transition">
                Simpan Perubahan
            </button>
            <a href="{{ route('profile') }}" class="text-sm text-pink-deep hover:underline">Kembali ke Profil</a>
        </div>
    </form>
</div>
</x-layouts.app>
