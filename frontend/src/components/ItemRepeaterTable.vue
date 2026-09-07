<template>
  <div class="space-y-4 p-4 border rounded-md bg-white">
    <h3 class="font-medium text-lg border-b pb-2">Rincian Barang / Jasa</h3>
    
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gray-100 text-gray-700 uppercase">
          <tr>
            <th class="px-4 py-2 w-10">No</th>
            <th class="px-4 py-2">Nama Barang & Spesifikasi</th>
            <th class="px-4 py-2 w-24">Jumlah</th>
            <th class="px-4 py-2 w-32">Satuan</th>
            <th class="px-4 py-2 w-48 text-right">Harga Satuan (Rp)</th>
            <th class="px-4 py-2 w-48 text-right">Subtotal (Rp)</th>
            <th class="px-4 py-2 w-16">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in items" :key="index" class="border-b">
            <td class="px-4 py-2 text-center">{{ index + 1 }}</td>
            <td class="px-4 py-2">
              <input type="text" v-model="item.item_description" class="w-full border rounded px-2 py-1" placeholder="Contoh: Meja Kayu 120x60" required />
            </td>
            <td class="px-4 py-2">
              <input type="number" min="1" v-model.number="item.quantity" class="w-full border rounded px-2 py-1 text-center" @input="calculateSubtotal(index)" required />
            </td>
            <td class="px-4 py-2">
              <select v-model="item.unit_metric" class="w-full border rounded px-2 py-1">
                <option value="Unit">Unit</option>
                <option value="Pcs">Pcs</option>
                <option value="Set">Set</option>
                <option value="Batang">Batang</option>
                <option value="Roll">Roll</option>
                <option value="Paket">Paket</option>
              </select>
            </td>
            <td class="px-4 py-2 text-right">
              <input type="number" min="0" v-model.number="item.estimated_unit_price" class="w-full border rounded px-2 py-1 text-right" @input="calculateSubtotal(index)" required />
            </td>
            <td class="px-4 py-2 text-right bg-gray-50 font-medium">
              {{ formatCurrency(item.estimated_subtotal) }}
            </td>
            <td class="px-4 py-2 text-center">
              <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800" :disabled="items.length === 1">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              </button>
            </td>
          </tr>
          <!-- Grand Total Row -->
          <tr class="bg-gray-100 font-bold">
            <td colspan="5" class="px-4 py-3 text-right">GRAND TOTAL ESTIMASI</td>
            <td class="px-4 py-3 text-right text-lg text-blue-700">{{ formatCurrency(grandTotal) }}</td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>

    <button type="button" @click="addItem" class="mt-2 text-sm text-blue-600 hover:underline font-medium flex items-center">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
      Tambah Baris Item
    </button>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'total-changed']);

const items = ref(props.modelValue.length ? props.modelValue : [
  { item_description: '', quantity: 1, unit_metric: 'Unit', estimated_unit_price: 0, estimated_subtotal: 0 }
]);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const calculateSubtotal = (index) => {
  const item = items.value[index];
  item.estimated_subtotal = (item.quantity || 0) * (item.estimated_unit_price || 0);
  emitChanges();
};

const addItem = () => {
  items.value.push({ item_description: '', quantity: 1, unit_metric: 'Unit', estimated_unit_price: 0, estimated_subtotal: 0 });
  emitChanges();
};

const removeItem = (index) => {
  if (items.value.length > 1) {
    items.value.splice(index, 1);
    emitChanges();
  }
};

const grandTotal = computed(() => {
  return items.value.reduce((total, item) => total + (item.estimated_subtotal || 0), 0);
});

const emitChanges = () => {
  emit('update:modelValue', items.value);
  emit('total-changed', grandTotal.value);
};

watch(items, emitChanges, { deep: true });
</script>
