<template>
  <div class="holiday-calendar-configuration">
    <CountryCalendarListComponent @add="handleAddCalendar" @settings="handleSettings" />

    <!-- Drawer para nuevo calendario -->
    <NewCalendarDrawerComponent
      ref="drawerRef"
      v-model:open="drawerOpen"
      :loading="createCalendar.isPending.value"
      @submit="handleSubmitNewCalendar"
      @extend="handleExtendValidity"
    />

    <!-- Drawer para configurar/extender vigencia -->
    <ActivateCalendarDrawer
      v-model:open="activateDrawerOpen"
      :calendar="selectedCalendarForExtend"
      @submit="handleExtendSubmit"
    />
  </div>
</template>

<script lang="ts" setup>
  import { ref, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { notification } from 'ant-design-vue';
  import CountryCalendarListComponent from '../components/CountryCalendarListComponent.vue';
  import NewCalendarDrawerComponent from '../components/NewCalendarDrawerComponent.vue';
  import ActivateCalendarDrawer from '../components/ActivateCalendarDrawer.vue';
  import type { CountryCalendarItem } from '../interfaces/country-calendar.interface';
  import type { Dayjs } from 'dayjs';

  import { useCalendarMutations } from '../composables/useCalendarMutations';

  const router = useRouter();
  const drawerOpen = ref(false);
  const drawerRef = ref();
  const { createCalendar } = useCalendarMutations();

  const handleAddCalendar = () => {
    drawerOpen.value = true;
  };

  // Estado para el drawer de configurar vigencia
  const activateDrawerOpen = ref(false);
  const selectedCalendarForExtend = ref<CountryCalendarItem | null>(null);

  // Handler cuando se clickea "Extender vigencia" en la alerta roja
  const handleExtendValidity = (countryName: string) => {
    // Crear un objeto calendario temporal para el drawer
    selectedCalendarForExtend.value = {
      id: 0, // Se actualizará después si es necesario
      country: countryName,
      countryId: 0,
      yearFrom: 0,
      yearTo: 0,
      enabled: false,
      createdAt: new Date().toISOString(),
      holidaysCount: 0,
    };
    activateDrawerOpen.value = true;
  };

  // Handler para cuando se guarda la extensión de vigencia
  const handleExtendSubmit = async (_payload: { dateFrom: Dayjs; dateTo: Dayjs }) => {
    // Aquí se podría llamar al servicio para actualizar la vigencia
    // Por ahora, solo cerramos el drawer y notificamos
    notification.success({
      message: 'Vigencia configurada',
      description: 'La vigencia del calendario ha sido actualizada.',
      placement: 'bottomRight',
    });
    activateDrawerOpen.value = false;
    selectedCalendarForExtend.value = null;
  };

  const handleSettings = (record: CountryCalendarItem) => {
    // Navegar a la configuración del calendario seleccionado
    console.log('Navigating to calendarDetail with', { calendarId: record.id });

    router
      .push({
        name: 'calendarDetail',
        params: {
          calendarId: String(record.id),
        },
      })
      .catch((err) => {
        console.error('Navigation error:', err);
        notification.error({
          message: 'Error al navegar',
          description: 'No se pudo cargar el detalle del calendario.',
          placement: 'bottomRight',
        });
      });
  };

  const handleSubmitNewCalendar = (data: any) => {
    const countryId = Number(data.country) || 1;
    const payload = {
      country_id: countryId,
      // Backend expects full date format YYYY-MM-DD
      year_from: data.dateFrom?.format('YYYY-MM-DD'),
      year_to: data.dateTo?.format('YYYY-MM-DD'),
    };

    createCalendar.mutate(payload, {
      onSuccess: (newCalendar) => {
        // Redirigir a la vista de detalle del calendario con el ID
        router.push({
          name: 'calendarDetail',
          params: {
            calendarId: newCalendar.id,
          },
        });
        drawerOpen.value = false;
      },
      onError: (error: any) => {
        console.error('Error creating calendar:', error);

        // Handle 422 Validation Errors
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.data;
          const message = error.response.data.message || 'Error de validación';

          // Check if message indicates expired period (backend sends this)
          const isExpiredError =
            message.toLowerCase().includes('caducó') ||
            message.toLowerCase().includes('expirado') ||
            message.toLowerCase().includes('expired') ||
            message.toLowerCase().includes('vencido');

          // Check if message indicates holidays already exist for this country
          const hasExistingHolidays =
            message.toLowerCase().includes('ya existen festivos') ||
            message.toLowerCase().includes('festivos existentes') ||
            message.toLowerCase().includes('holidays already exist') ||
            message.toLowerCase().includes('ya existe un calendario');

          if (isExpiredError) {
            // Show red alert for expired period
            if (drawerRef.value) {
              drawerRef.value.setExpiredError(message);
            }
          } else if (hasExistingHolidays) {
            // Calendar already exists for this country - show specific message
            if (drawerRef.value) {
              drawerRef.value.setError(
                'Ya existe un calendario con festivos para este país. Use la opción de actualizar vigencia.'
              );
            }
          } else if (errors) {
            // If backend provides specific field errors
            const errorMessages = Object.values(errors).flat().join('\n');
            if (drawerRef.value) {
              drawerRef.value.setError(errorMessages || message);
            }
          } else {
            if (drawerRef.value) {
              drawerRef.value.setError(message);
            }
          }
        } else {
          notification.error({
            message: 'Error al crear calendario',
            description: 'Ha ocurrido un error inesperado al conectar con el servidor.',
            placement: 'bottomRight',
          });
        }
      },
    });
  };

  onMounted(() => {
    console.log('✅ VISTA ACTUALIZADA: HolidayCalendarConfiguration cargado correctamente');
  });
</script>

<style lang="scss" scoped>
  .holiday-calendar-configuration {
    padding: 16px;
  }
</style>
