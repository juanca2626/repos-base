<template>
  <div class="replies-tab-content">
    <!-- Add New Reply Section -->
    <div class="add-reply-section">
      <h4 class="section-title">Agregar Nueva Réplica</h4>
      <a-form layout="vertical">
        <a-form-item label="Comentario">
          <a-textarea
            v-model:value="newReply"
            :rows="4"
            placeholder="Escriba su réplica aquí..."
            :disabled="saving"
          />
        </a-form-item>
        <a-button
          type="primary"
          @click="handleAddReply"
          :loading="saving"
          :disabled="!newReply.trim()"
        >
          <template #icon><PlusOutlined /></template>
          Agregar Réplica
        </a-button>
      </a-form>
    </div>

    <a-divider />

    <!-- Existing Replies List -->
    <div class="replies-list-section">
      <h4 class="section-title">Réplicas Existentes ({{ replies.length }})</h4>

      <a-spin :spinning="loading">
        <div v-if="replies.length === 0" class="empty-state">
          <InboxOutlined style="font-size: 48px; color: #d9d9d9" />
          <p>No hay réplicas registradas</p>
        </div>

        <div v-else class="replies-list">
          <div v-for="reply in replies" :key="reply.nrolin" class="reply-item">
            <div class="reply-header">
              <span class="reply-number">#{{ reply.nrolin }}</span>
              <span class="reply-date">
                {{ formatDateTime(reply.fecha, reply.hora) }}
              </span>
              <a-popconfirm
                title="¿Está seguro de eliminar esta réplica?"
                @confirm="handleRemoveReply(reply.nrolin)"
                ok-text="Sí"
                cancel-text="No"
              >
                <a-button type="link" danger size="small">
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-popconfirm>
            </div>
            <div class="reply-content">
              {{ reply.texto }}
            </div>
          </div>
        </div>
      </a-spin>
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue';
  import { PlusOutlined, DeleteOutlined, InboxOutlined } from '@ant-design/icons-vue';
  import { useClaimsApi } from '../composables/claimsComposable';
  import dayjs from 'dayjs';

  const props = defineProps({
    codref: {
      type: Number,
      required: true,
    },
    nrocom: {
      type: String,
      required: true,
    },
    replies: {
      type: Array,
      default: () => [],
    },
  });

  const emit = defineEmits(['refresh']);

  const newReply = ref('');

  const { loading, saving, addReply, removeReply } = useClaimsApi();

  const handleAddReply = async () => {
    if (!newReply.value.trim()) return;

    const nextLine =
      props.replies.length > 0 ? Math.max(...props.replies.map((r) => r.NROLIN)) + 1 : 1;

    const success = await addReply({
      codref: props.codref,
      comment: newReply.value,
      nrolin: nextLine,
    });

    if (success) {
      newReply.value = '';
      emit('refresh');
    }
  };

  const handleRemoveReply = async (nrolin) => {
    const success = await removeReply(props.nrocom, nrolin);
    if (success) {
      emit('refresh');
    }
  };

  const formatDateTime = (fecha, hora) => {
    if (!fecha) return '';
    try {
      const dateStr = `${fecha} ${hora || '00:00:00'}`;
      return dayjs(dateStr, 'YYYY-MM-DD HH:mm:ss').format('DD/MM/YYYY HH:mm');
    } catch {
      return fecha;
    }
  };
</script>

<style scoped lang="scss">
  .replies-tab-content {
    padding: 20px 0;

    .add-reply-section {
      margin-bottom: 24px;

      .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #262626;
        margin-bottom: 16px;
      }
    }

    .replies-list-section {
      .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #262626;
        margin-bottom: 16px;
      }

      .empty-state {
        text-align: center;
        padding: 48px 0;
        color: #8c8c8c;

        p {
          margin-top: 16px;
          font-size: 14px;
        }
      }

      .replies-list {
        max-height: 400px;
        overflow-y: auto;

        .reply-item {
          background: #fafafa;
          border: 1px solid #f0f0f0;
          border-radius: 8px;
          padding: 16px;
          margin-bottom: 12px;
          transition: all 0.3s ease;

          &:hover {
            border-color: #d9d9d9;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
          }

          .reply-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;

            .reply-number {
              font-weight: 600;
              color: #1890ff;
              font-size: 13px;
            }

            .reply-date {
              flex: 1;
              font-size: 12px;
              color: #8c8c8c;
            }
          }

          .reply-content {
            color: #262626;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
          }
        }
      }
    }
  }
</style>
