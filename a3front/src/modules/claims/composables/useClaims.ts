import { ref, computed, onMounted } from 'vue';
import { useClaimsStore } from '../stores/claims-store';
//import { claimsApi } from '../services/claims';

export function useClaims() {
  const store = useClaimsStore();
  const { fetchClaims, saveClaim } = store;
  const selectedClaim = ref(null);
  const showTable = ref(false);
  const searchQuery = ref('');

  const filteredClaims = computed(() =>
    store.claims.filter((claim) =>
      claim.cliente.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  );

  const selectClaim = (claim) => {
    selectedClaim.value = { ...claim };
    showTable.value = false; // Vuelve al formulario para edición
  };

  const createNewClaim = () => {
    selectedClaim.value = {
      nroFile: '',
      especialista: '',
      nombreReserva: '',
      cliente: '',
      area: '',
      comentario: '',
      respuesta: '',
      infundado: false,
      cerrado: false,
      fechaLlegada: '',
      montoDevolucion: 0,
      montoCompensacion: 0,
      obsCompensacion: '',
      montoReembolso: 0,
      obsReembolso: '',
    };
    showTable.value = false;
  };

  onMounted(fetchClaims);

  return {
    claims: store.claims,
    fetchClaims,
    saveClaim,
    selectedClaim,
    showTable,
    searchQuery,
    filteredClaims,
    selectClaim,
    createNewClaim,
  };
}
