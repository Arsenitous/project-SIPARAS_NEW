<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">404 - Halaman Tidak Ditemukan</h1>
    <div class="bg-gray-50 text-gray-700 p-6 rounded shadow border border-gray-200">
      <p>Halaman yang Anda cari tidak tersedia di SIPARAS.</p>
      <button @click="$router.push('/')" class="mt-4 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Kembali ke Beranda</button>
    </div>
  </div>
</template>
