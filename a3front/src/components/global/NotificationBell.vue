<template>
  <a-dropdown placement="bottomRight" trigger="click" :overlayStyle="{ width: '320px' }">
    <a-badge :count="unreadCount" offset="[0, 5]">
      <a-button>
        <template #icon><BellOutlined /></template>
      </a-button>
    </a-badge>
    <template #overlay>
      <div class="notification-dropdown">
        <div class="header">
          <strong>Notificaciones</strong>
        </div>
        <a-divider style="margin: 8px 0" />
        <div class="list">
          <template v-if="notifications.length">
            <div
              v-for="(notification, index) in notifications"
              :key="index"
              class="notification-item"
              :class="{ unread: !notification.read }"
              @click="handleConfirmation(notification, index)"
            >
              <div class="message">
                {{ notification.body }}
              </div>
              <div v-if="notification.module" class="module">Módulo: {{ notification.module }}</div>
              <div v-if="notification.actions?.length" class="actions">
                <a-button
                  v-for="(action, i) in notification.actions"
                  :key="i"
                  size="small"
                  type="default"
                  style="margin-right: 6px"
                >
                  {{ action }}
                </a-button>
              </div>
            </div>
          </template>
          <div v-else class="empty">No hay notificaciones</div>
        </div>
      </div>
    </template>
  </a-dropdown>
</template>

<script lang="ts" setup>
  import { BellOutlined } from '@ant-design/icons-vue';
  import { ref, computed, watch } from 'vue';

  interface NotificationItem {
    body: string;
    module?: string;
    actions?: string[];
    confirmation?: () => void;
    read?: boolean;
  }

  const props = defineProps<{
    notifications: NotificationItem[];
  }>();

  const localNotifications = ref<NotificationItem[]>([]);

  watch(
    () => props.notifications,
    (newVal) => {
      localNotifications.value = newVal.map((n) => ({
        ...n,
        read: n.read ?? false,
      }));
    },
    { immediate: true, deep: true }
  );

  const unreadCount = computed(() => localNotifications.value.filter((n) => !n.read).length);

  const handleConfirmation = (notification: NotificationItem, index: number) => {
    if (!localNotifications.value[index].read) {
      localNotifications.value[index].read = true;
    }
    notification.confirmation?.();
  };
</script>

<style scoped>
  .notification-dropdown {
    background: white;
    border-radius: 8px;
    padding: 8px 12px;
    max-height: 360px;
    overflow-y: auto;
  }

  .notification-item {
    padding: 12px;
    border-radius: 8px;
    transition: background 0.2s;
    cursor: pointer;
    margin-bottom: 6px;
  }

  .notification-item.unread {
    background-color: #e6f7ff;
  }

  .notification-item:hover {
    background-color: #f5f5f5;
  }

  .message {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 4px;
  }

  .module {
    font-size: 12px;
    color: #888;
    margin-bottom: 6px;
  }

  .actions {
    display: flex;
  }
  .empty {
    padding: 16px;
    text-align: center;
    color: #999;
  }
  .header {
    padding-bottom: 4px;
    font-size: 16px;
  }
</style>
