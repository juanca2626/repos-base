<template>
  <div
    class="quotes-users-connected"
    v-if="isAuthenticatedCognito() && isAuthenticated() && socketsStore.isConnected"
  >
    <div class="users-list" v-if="filteredConnections.length > 0">
      <a-tooltip
        v-for="connection in filteredConnections"
        :key="connection.token"
        placement="bottom"
      >
        <template #title> {{ connection.userName }} ({{ connection.userCode }}) </template>
        <a-avatar
          :src="baseURLPhoto(connection.userPhoto)"
          :style="{ backgroundColor: getAvatarColor(connection.userCode) }"
          :size="32"
          class="user-avatar"
          @click="
            connection.userCode !== getUserCode()
              ? handleConnectTeams(connection.userEmail)
              : undefined
          "
        >
          {{ getInitials(connection.userName) }}
        </a-avatar>
      </a-tooltip>
    </div>
    <template v-else>
      <a-tooltip placement="right">
        <template #title>No hay más usuarios viendo esta cotización</template>
        <font-awesome-icon :icon="['fas', 'ghost']" bounce size="lg" style="color: #eb5757" />
      </a-tooltip>
    </template>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { useSocketsStore } from '@/stores/global';
  import { getUserCode, isAuthenticated, isAuthenticatedCognito } from '@/utils/auth';

  const socketsStore = useSocketsStore();

  const filteredConnections = computed(() => {
    return socketsStore.getConnections.filter(
      (connection) =>
        connection.token !== socketsStore.getToken && connection.userCode !== 'undefined'
    );
  });

  const handleConnectTeams = (email: string) => {
    const url = `https://teams.microsoft.com/l/chat/0/0?users=${email}`;
    window.open(url, '_blank');
  };

  const baseURLPhoto = (photo: string) => {
    return photo ? `${(window as any).url_front_a2}images/users/${photo}` : '';
  };

  const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
      return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  };

  const getAvatarColor = (code: string) => {
    const colors = [
      '#f56a00',
      '#7265e6',
      '#ffbf00',
      '#00a2ae',
      '#eb5757',
      '#52c41a',
      '#1890ff',
      '#722ed1',
      '#fa541c',
      '#13c2c2',
    ];
    const index = code ? code.charCodeAt(0) % colors.length : 0;
    return colors[index];
  };
</script>

<style scoped lang="scss">
  .quotes-users-connected {
    position: fixed;
    bottom: 20px;
    left: 20px;
    display: flex;
    align-items: center;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.95);
    padding: 10px 12px;
    border-radius: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 1px solid #e9e9e9;

    .users-list {
      display: flex;
      gap: 6px;
      align-items: center;
    }

    .user-avatar {
      cursor: pointer;
      transition: all 0.2s ease;
      border: 2px solid #fff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

      &:hover {
        transform: scale(1.15);
        z-index: 10;
      }
    }
  }
</style>
