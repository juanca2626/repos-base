import { ref } from 'vue';
import { routesApi } from '@/modules/routes/api/routesApi';

type RouteItem = {
  id: string;
  nombre: string;
  temporadaAlta?: boolean;
};

type DayAvailability = {
  date: string; // 'YYYY-MM-DD'
  horarios: { time: string; slots: number }[];
};

export function useRoutes() {
  const circuitos = ref([]);

  async function fetchCircuitos() {
    try {
      const { data } = await routesApi.get('api/ticket-circuits');
      circuitos.value = data;
    } catch (error) {
      console.error('Error fetching circuitos:', error);
    }
  }

  // --- RUTAS POR CADA CIRCUITO ---
  const rutasMap = ref<Record<string, RouteItem[]>>({});

  async function fetchRoutesByCircuit(circuitId: string) {
    try {
      const { data } = await routesApi.get(`api/ticket-circuits/${circuitId}/routes`);
      rutasMap.value[circuitId] = data;
      return data;
    } catch (error) {
      console.error('Error fetching routes:', error);
      return [];
    }
  }

  async function getAvailabilityByFilters(
    circuitId: string,
    routeId: string,
    month: number,
    year: number
  ): Promise<DayAvailability[]> {
    try {
      const { data } = await routesApi.get(
        `api/available_routes/${circuitId}/${routeId}/${month}/${year}`
      );

      const apiMap: Record<string, DayAvailability> = {};
      data.forEach((item: any) => {
        apiMap[item.date] = {
          date: item.date,
          horarios: item.times.map((t: any) => ({
            time: t.time,
            slots: t.ticket_quantity,
          })),
        };
      });

      const fullDays: any[] = [];
      const d = new Date(year, month - 1, 1);

      while (d.getMonth() === month - 1) {
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        const iso = `${yyyy}-${mm}-${dd}`;

        fullDays.push({
          date: iso,
          horarios: apiMap[iso]?.horarios || [],
          currentMonth: true,
        });

        d.setDate(d.getDate() + 1);
      }

      return fullDays;
    } catch (error) {
      console.error('Error en API filtrada:', error);
      return [];
    }
  }

  return {
    circuitos,
    fetchCircuitos,
    fetchRoutesByCircuit,
    getAvailabilityByFilters,
  };
}
