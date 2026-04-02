<template>
  <files-edit-page-content />
</template>

<script setup>
  import { onMounted } from 'vue';
  import FilesEditPageContent from '@/components/files/edit/FilesEditPageContent.vue';
  import { useRouter, useRoute } from 'vue-router';
  import { getUserCode, getUserName } from '@/utils/auth';
  import { processLoadingItineraries } from '@/utils/files';
  import { useSocketsStore } from '@/stores/global';
  import { useFilesStore, useItineraryStore } from '@store/files';

  const socketsStore = useSocketsStore();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();

  const router = useRouter();
  const route = useRoute();

  const showNotification = () => {
    socketsStore.send({
      success: true,
      type: 'user_connected_in_file',
      message: 'Usuario Conectado',
      code: getUserCode(),
      name: getUserName(),
      file_id: filesStore.getFile.id,
      description: `${getUserName()} (${getUserCode()}) acaba de conectarse al FILE N° ${filesStore.getFile.fileNumber}`,
    });

    setTimeout(() => {
      filesStore.finished();
    }, 1000);
  };

  onMounted(async () => {
    const { id } = route.params;
    filesStore.inited();

    if (id != filesStore.getFile.id) {
      filesStore.clearFile(); // Limpiar el store del file visualizado..
      await filesStore.fetchReasons();
    }

    await filesStore.fetchStatementReasons();
    // filesStore.getStatementModificationReasons();

    if ((id && typeof filesStore.getFile.id === 'undefined') || !filesStore.isLoaded) {
      if (!(filesStore.getFile?.id === id)) {
        await filesStore.getById({ id, loading: true });

        if (filesStore.getFile?.id !== parseInt(id)) {
          router.push({ name: 'error_404' });
        }
      }

      await filesStore.getPassengersById({ fileId: id });
      filesStore.finished();
      filesStore.changeLoaded(true);

      showNotification();

      itineraryStore.initedAsync();
      await processLoadingItineraries();

      const codes = filesStore.getFileItineraries
        .filter((itinerary) => itinerary.entity === 'service')
        .map((itinerary) => {
          return itinerary?.services.map((service) => service.code_ifx);
        });

      const unique_codes = [...new Set(codes.flat())];

      await Promise.all([
        filesStore.searchServicesGroups({ codes: unique_codes, loading: false }),
        filesStore.searchServicesFrequences({ codes: unique_codes, loading: false }),
        // filesStore.searchServiceSchedules({ codes: unique_codes, loading: false }),
      ]);

      itineraryStore.finished();

      await filesStore.fetchFileErrors({ fileNumber: filesStore.getFile.fileNumber });
    } else {
      showNotification();
    }
  });
</script>
