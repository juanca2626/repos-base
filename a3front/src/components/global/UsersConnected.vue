<template>
  <div
    class="connected-users-floating"
    :class="{ left: position === 'left', right: position === 'right' }"
    v-if="isAuthenticatedCognito() && isAuthenticated() && socketsStore.isConnected"
  >
    <a-row
      type="flex"
      align="middle"
      style="gap: 7px"
      v-if="
        socketsStore.getConnections.filter(
          (connection) => connection.token !== socketsStore.getToken
        ).length > 0
      "
    >
      <a-col>
        <b>{{ t('global.label.users_connected') }}: </b>
      </a-col>
      <a-col class="d-flex" style="gap: 7px">
        <template
          v-for="(connection, c) in socketsStore.getConnections.filter(
            (connection) =>
              connection.token !== socketsStore.getToken && connection.userCode !== 'undefined'
          )"
        >
          <div class="user-avatar">
            <files-popover-avatar
              @onRemoveUserView="removeUserView"
              @click="
                connection.userCode !== getUserCode()
                  ? handleConnectTeams(`${connection.userEmail}`)
                  : undefined
              "
              :token="connection.token"
              :ip="connection.ip"
              :tooltip="false"
              :label="`${connection.userName} (${connection.userCode})`"
              :photo="baseURLPhoto(connection.userPhoto)"
              :active="connection.token === socketsStore.getToken"
            />
          </div>
        </template>
      </a-col>
    </a-row>
    <template v-else>
      <div class="user-avatar">
        <a-tooltip :placement="position === 'left' ? 'right' : 'left'">
          <template #title>{{ props.title }}</template>
          <font-awesome-icon :icon="['fas', 'ghost']" bounce />
        </a-tooltip>
      </div>
    </template>
  </div>
</template>

<script setup>
  import FilesPopoverAvatar from '@/components/files/edit/FilesPopoverAvatar.vue';
  import { useSocketsStore } from '@/stores/global';
  import { getUserCode, isAuthenticated, isAuthenticatedCognito } from '@/utils/auth';

  import { useI18n } from 'vue-i18n';

  const props = defineProps({
    title: {
      type: String,
      default: 'No hay más usuarios observando esta cotización',
    },
    position: {
      type: String,
      default: 'right',
    },
  });

  const { t } = useI18n({
    useScope: 'global',
  });

  const socketsStore = useSocketsStore();

  const handleConnectTeams = (email) => {
    const url = `https://teams.microsoft.com/l/chat/0/0?users=${email}`;
    window.open(url, '_blank');
  };

  const baseURLPhoto = (photo) => {
    return photo ? `${window.url_front_a2}images/users/${photo}` : false;
  };

  const removeUserView = ({ token }) => {
    socketsStore.send({
      success: true,
      type: 'user_disconnected_in_file',
      token: token,
      file_number: filesStore.getFile.fileNumber,
      user_name: getUserName(),
      user_code: getUserCode(),
      user_id: getUserId(),
    });

    setTimeout(() => {
      socketsStore.send({
        success: true,
        type: 'ping',
      });
    }, 100);
  };
</script>

<style scoped>
  .connected-users-floating {
    position: fixed;
    bottom: 20px;
    display: flex;
    gap: 8px;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.85);
    padding: 8px 12px;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  }

  .connected-users-floating.left {
    right: auto;
    left: 20px;
  }

  .connected-users-floating.right {
    right: 20px;
    left: auto;
  }

  .user-avatar {
    cursor: pointer;
    transition: transform 0.2s ease;
  }
  .user-avatar:hover {
    transform: scale(1.2);
  }
</style>
