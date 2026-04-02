<template>
  <a-tooltip placement="bottom">
    <template
      #title
      v-if="
        tooltip &&
        label &&
        !active &&
        label.indexOf(getUserCode()) === -1 &&
        label.indexOf('undefined') === -1
      "
    >
      <small>{{ t('global.label.connect_for') }} TEAMS</small>
      <font-awesome-icon :icon="['fas', 'square-arrow-up-right']" class="ms-2" />
    </template>
    <component :is="label ? BasePopover : 'p'" placement="topRight" v-if="!flag_removed">
      <a-avatar
        :size="size"
        :style="active ? 'background-color: #eb5757' : 'background-color:gray'"
        :src="photo"
      >
        <template #icon>
          <template v-if="label.indexOf('undefined') > -1">
            <font-awesome-icon :icon="['fas', 'user-secret']" />
          </template>
          <template v-else>
            <font-awesome-icon icon="fa-regular fa-user" />
          </template>
        </template>
      </a-avatar>
      <template #content>
        <template v-if="label.indexOf('undefined') > -1"> Usuario Incógnito </template>
        <template v-else>
          {{ label }}
        </template>
        <a-row type="flex" justify="space-between" align="middle" style="gap: 5px" v-if="ip">
          <a-col>
            <b
              ><small :class="['d-block', active ? 'text-danger' : '']">IP: {{ ip }}</small></b
            >
          </a-col>
          <a-col v-if="!active && label.indexOf('undefined') === -1">
            <span class="cursor-pointer text-danger" @click="removeUserView">
              <a-tooltip placement="right">
                <template #title>
                  <small class="text-uppercase">{{ t('global.label.kick_user') }}</small>
                </template>
                <font-awesome-icon :icon="['fas', 'person-walking-arrow-right']" />
              </a-tooltip>
            </span>
          </a-col>
        </a-row>
      </template>
    </component>
  </a-tooltip>
</template>

<script setup>
  import { ref } from 'vue';
  import BasePopover from '@/components/files/reusables/BasePopover.vue';
  import { getUserCode } from '@/utils/auth';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onRemoveUserView']);

  const flag_removed = ref(false);

  const props = defineProps({
    size: {
      type: String,
      default: 'small',
    },
    photo: {
      type: String,
      default: false,
    },
    active: {
      type: Boolean,
      default: false,
    },
    ip: {
      type: String,
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    token: {
      type: String,
      default: '',
    },
    tooltip: {
      type: Boolean,
      default: true,
    },
  });

  const removeUserView = () => {
    flag_removed.value = true;
    emit('onRemoveUserView', {
      token: props.token,
    });
  };
</script>
