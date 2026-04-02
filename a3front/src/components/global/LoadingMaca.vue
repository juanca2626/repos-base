<template>
  <a-tooltip>
    <template #title>
      <small class="text-uppercase">{{ t('files.message.loading') }}...</small>
    </template>
    <div :class="['maca-loader', size === 'small' ? 'is-small' : 'is-default']"></div>
  </a-tooltip>
</template>

<script setup>
  import { onMounted, onUnmounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useMacaAudio } from './useMacaAudio.js';

  const { t } = useI18n({ useScope: 'global' });
  const { startAudio, stopAudio } = useMacaAudio();

  const props = defineProps({
    size: {
      type: String,
      default: 'default',
    },
    muted: {
      type: Boolean,
      default: true,
    },
  });

  onMounted(() => {
    if (!props.muted) {
      console.error('startAudio');
      startAudio();
    }
  });

  onUnmounted(() => {
    if (!props.muted) {
      console.error('stopAudio');
      stopAudio();
    }
  });
</script>

<style scoped>
  .maca-loader {
    display: block;
    background-repeat: no-repeat;
    animation-duration: 1.56s;
    animation-timing-function: steps(1);
    animation-iteration-count: infinite;
  }

  .is-small {
    width: 31px;
    height: 31px;
    background-image: url('https://res.cloudinary.com/litodti/image/upload/aurora/maca/MACA-30x30.png');
    background-size: 278px 34px;
    animation-name: anim-maca-small;
  }

  .is-default {
    width: 68px;
    height: 68px;
    background-image: url('https://res.cloudinary.com/litodti/image/upload/aurora/maca/MACA-60x60.png');
    background-size: 612px 68px;
    animation-name: anim-maca-default;
  }

  /* --- SMALL (34px) --- */
  @keyframes anim-maca-small {
    0% {
      background-position: 0 0;
    }
    11.54% {
      background-position: -30.89px 0;
    }
    23.08% {
      background-position: -61.78px 0;
    }
    34.62% {
      background-position: -92.67px 0;
    }
    46.15% {
      background-position: -123.56px 0;
    }
    57.69% {
      background-position: -154.45px 0;
    }
    69.23% {
      background-position: -185.34px 0;
    }
    88.46% {
      background-position: -185.34px 0;
    }
    88.47% {
      background-position: -216.23px 0;
    }
    100% {
      background-position: -216.23px 0;
    }
  }

  /* --- DEFAULT (68px) --- */
  @keyframes anim-maca-default {
    0% {
      background-position: 0 0;
    }
    11.54% {
      background-position: -68px 0;
    }
    23.08% {
      background-position: -136px 0;
    }
    34.62% {
      background-position: -204px 0;
    }
    46.15% {
      background-position: -272px 0;
    }
    57.69% {
      background-position: -340px 0;
    }
    69.23% {
      background-position: -408px 0;
    }
    88.46% {
      background-position: -408px 0;
    }
    88.47% {
      background-position: -476px 0;
    }
    100% {
      background-position: -476px 0;
    }
  }
</style>
