import { ref, computed } from 'vue';

export function useCalendar() {
  const viewYear = ref(new Date().getFullYear());
  const viewMonth = ref(new Date().getMonth());
  const monthDays = ref<any[]>([]);

  const weekDays = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

  function toLocalIso(date: Date) {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  function getDayNumber(iso?: string) {
    if (!iso) return '';
    return Number(iso.split('-')[2]);
  }

  function buildCalendar(days: any[]) {
    const firstDay = new Date(viewYear.value, viewMonth.value, 1);
    let startDay = firstDay.getDay();
    startDay = startDay === 0 ? 6 : startDay - 1;

    const padded: any[] = [];
    const prevMonthLast = new Date(viewYear.value, viewMonth.value, 0).getDate();

    for (let i = startDay - 1; i >= 0; i--) {
      const date = new Date(viewYear.value, viewMonth.value - 1, prevMonthLast - i);
      padded.push({ date: toLocalIso(date), currentMonth: false, horarios: [] });
    }

    days.forEach((d) => {
      padded.push({ date: d.date, currentMonth: true, horarios: d.horarios });
    });

    const totalCells = 42;
    let nextDayCounter = 1;

    while (padded.length < totalCells) {
      const date = new Date(viewYear.value, viewMonth.value + 1, nextDayCounter++);
      padded.push({ date: toLocalIso(date), currentMonth: false, horarios: [] });
    }

    monthDays.value = padded;
  }

  function clearCalendar() {
    monthDays.value = [];
  }

  const monthTitle = computed(() => {
    const d = new Date(viewYear.value, viewMonth.value, 1);
    return d.toLocaleString('es-PE', { month: 'long', year: 'numeric' });
  });

  return {
    viewYear,
    viewMonth,
    monthDays,
    weekDays,
    getDayNumber,
    buildCalendar,
    clearCalendar,
    monthTitle,
  };
}
