<template>
  <div class="follow-up-container">
    <h5 class="title">Historial de seguimientos</h5>
    <div v-if="followUps && followUps.length > 0" style="max-height: 200px; overflow-y: auto">
      <div v-for="(item, index) in followUps" :key="index" class="follow-up-card">
        <div class="follow-up-header">
          <div class="user-info">
            <div class="a">
              <span class="user-name">
                {{ item.user.fullname || 'Desconocido' }}
              </span>
              <div class="status-badge">
                <span class="status-text">{{ item.status || 'Enviado' }}</span>
              </div>
            </div>

            <div class="follow-up-label" style="display: block">
              {{ item.template.name }}
            </div>
          </div>
          <div class="date-info">
            <div class="date">{{ formatDate(item.createdAt) }}</div>
            <div class="time">{{ formatTime(item.createdAt) }}</div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="no-follow-ups">No hay seguimientos para mostrar.</div>
  </div>
</template>

<script setup>
  // Propiedades
  defineProps({
    followUps: {
      type: Array,
      required: true,
      default: () => [],
    },
  });

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('es-PE', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false,
    });
  };
</script>

<style scoped lang="scss">
  .a {
    display: flex;
    align-items: flex-start; /* Alinea verticalmente los elementos */
    gap: 4px; /* Espacio entre nombre y badge */
    .user-name {
      max-width: 150px;
    }
  }
  .follow-up-container {
    width: 100%;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
  .title {
    padding: 0;
    margin: 0;
    font-weight: 700;
    font-size: 16px;
    color: #3d3d3d;
  }
  .no-follow-ups {
    text-align: center;
    color: #888;
    padding: 20px 0;
  }
  .follow-up-card {
    width: 350px;
    background: #fafafa;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e9e9e9;
    margin: 10px 5px;
  }
  .follow-up-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }
  .user-info {
    display: flex;
    flex-direction: column; /* 👈 hace que los elementos hijos se apilen verticalmente */
    gap: 6px;

    color: #979797;
    font-size: 12px;
    font-family: Montserrat;
    font-weight: 500;
    line-height: 19px;
    letter-spacing: 0.18px;
    word-wrap: break-word;
  }
  .status-badge {
    background: #dfffe9;
    border-radius: 6px;
    border: 1px solid #1ed790;
    padding: 1px 8px;
  }
  .status-text {
    color: #44b089;
    font-size: 10px;
    font-weight: 500;
  }
  .date-info {
    text-align: right;
    font-size: 12px;
    .date {
      color: #eb5757;
      font-weight: 700;
    }
    .time {
      color: #979797;
      font-size: 10px;
    }
  }
  .follow-up-label {
    margin: 0;
    font-size: 16px;
    color: #737373;
    font-weight: 500;
    color: #737373;
    font-size: 16px;
    font-family: Montserrat;
    font-weight: 500;
    line-height: 23px;
    word-wrap: break-word;
  }
</style>
