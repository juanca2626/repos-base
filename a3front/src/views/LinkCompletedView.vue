<template>
  <a-layout class="completed-layout">
    <a-result>
      <template #icon>
        <span class="emoji">✅</span>
      </template>
      <template #title>
        <p class="title">
          <strong>{{ t('global.label.title_completed') }}</strong>
        </p>
      </template>
      <template #subTitle>
        <div class="message">
          <p>{{ t('global.label.subtitle_completed') }}:</p>
          <p class="secure-msg">
            <a-icon type="check-circle" class="check-icon" />
            <strong> {{ t('global.label.secured_data') }}</strong>
            {{ t('global.label.aurora_account') }}.
          </p>

          <p>
            <strong>{{ t('global.label.help_completed') }}</strong>
          </p>
          <ul class="actions">
            <li>
              🔹 {{ t('global.label.access_to') }} <strong>Aurora</strong>
              {{ t('global.label.query_completed') }}.
            </li>
            <li>
              🔹 {{ t('global.label.need_help') }} <em>{{ t('global.label.contact_us') }}</em> –
              {{ t('global.label.executive_ready_to_help_you') }}
            </li>
          </ul>

          <p class="footer-msg">
            <em>{{ t('global.label.thanks_completed') }} ✈️</em>
          </p>
        </div>
      </template>
      <template #extra>
        <a-button type="primary" v-on:click="returnToHome()">
          <font-awesome-icon :icon="['fas', 'house']" size="lg" />
        </a-button>
      </template>
    </a-result>
  </a-layout>
</template>

<script setup>
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const returnToHome = () => {
    window.location.href = window.url_app;
  };
</script>

<style scoped lang="scss">
  .completed-layout {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #1f1f1f;
    color: white;
  }

  .a-result {
    max-width: 600px;
  }

  .message {
    color: #f0f0f0;
    font-size: 15px;
    line-height: 1.6;
  }

  .title {
    font-size: 18px;
    color: white;
  }

  .secure-msg {
    background: #135200;
    color: #b7eb8f;
    padding: 10px;
    border-radius: 6px;
    margin-top: 10px;
    margin-bottom: 20px;
  }

  .actions {
    list-style: none;
    padding-left: 0;
    margin-top: 10px;
    li {
      margin-bottom: 8px;
    }
  }

  .footer-msg {
    margin-top: 20px;
    font-style: italic;
    color: #cfcfcf;
  }

  .emoji {
    font-size: 32px;
  }
</style>
