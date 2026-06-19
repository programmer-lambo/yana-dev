<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <a href="/" class="text-xl font-bold text-blue-600">Yana.dev</a>

    <!-- Tambahkan nav links -->
    <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
        <a href="/home" class="hover:text-blue-600 transition">Home</a>
        <a href="/categories" class="hover:text-blue-600 transition">Kategori</a>
         <a href="/dashboard/notes" class="hover:text-blue-600 font-semibold">Notes Saya</a>
    </div>

    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-600" x-text="'Halo, ' + user.name"></span>
        <button
            @click="logout"
            class="text-sm bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg transition"
        >
            Logout
        </button>
    </div>
</nav>