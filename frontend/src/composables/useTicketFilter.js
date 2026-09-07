// composables/useTicketFilter.js
// Manages ticket filter state with URL query param sync
import { ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export function useTicketFilter() {
  const route  = useRoute();
  const router = useRouter();

  // Initialize from URL query params
  const filters = ref({
    search:       route.query.search        || '',
    status:       route.query.status        || '',
    procedure_type: route.query.procedure_type || '',
    urgency_level:  route.query.urgency_level  || '',
    date_from:    route.query.date_from     || '',
    date_to:      route.query.date_to       || '',
    sort_by:      route.query.sort_by       || 'created_at',
    sort_dir:     route.query.sort_dir      || 'desc',
  });

  const isFiltered = computed(() =>
    Object.entries(filters.value).some(([k, v]) =>
      !['sort_by', 'sort_dir'].includes(k) && v !== ''
    )
  );

  // Sync filters → URL
  watch(filters, (val) => {
    const query = {};
    Object.entries(val).forEach(([k, v]) => { if (v) query[k] = v; });
    router.replace({ query });
  }, { deep: true });

  function resetFilters() {
    filters.value = {
      search: '', status: '', procedure_type: '', urgency_level: '',
      date_from: '', date_to: '', sort_by: 'created_at', sort_dir: 'desc',
    };
  }

  function toggleSort(field) {
    if (filters.value.sort_by === field) {
      filters.value.sort_dir = filters.value.sort_dir === 'asc' ? 'desc' : 'asc';
    } else {
      filters.value.sort_by  = field;
      filters.value.sort_dir = 'desc';
    }
  }

  function getSortIcon(field) {
    if (filters.value.sort_by !== field) return '↕';
    return filters.value.sort_dir === 'asc' ? '↑' : '↓';
  }

  return { filters, isFiltered, resetFilters, toggleSort, getSortIcon };
}
