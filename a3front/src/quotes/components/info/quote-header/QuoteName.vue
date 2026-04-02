<script lang="ts" setup>
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { computed, ref } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const name = ref<string>('');
  const editName = ref<boolean>(false);
  const titleEdit = () => {
    if (processing.value) return;
    editName.value = !editName.value;

    if (!editName.value && name.value !== quote.value.name) {
      updateName();
      getQuote();
    } else {
      name.value = quote.value.name;
    }
  };

  const titleEditCancel = () => {
    if (processing.value) return;
    quote.value.name = name.value;
    editName.value = !editName.value;
  };

  const { quote, quoteId, updateName, getQuote, processing } = useQuote();

  const quoteDraftId = computed(() => {
    let id: number | null = null;
    if (quote.value.logs) {
      quote.value.logs.forEach((log) => {
        if (log.type == 'editing_quote') {
          id = log.quote_id;
        }
      });
    }

    return id;
  });
</script>

<template>
  <div class="text">
    <div class="num-coti">
      <template v-if="quoteId">
        {{ t('quote.label.quote_num') }}: {{ quoteId }}
        <span style="display: none">{{ t('quote.label.drag') }} : {{ quoteDraftId }}</span>
      </template>
    </div>
    <span v-if="quote.name && !editName" class="title" @click="titleEdit">
      {{ quote.name }}
    </span>
    <input
      v-if="editName"
      v-model="quote.name"
      name="title-title"
      type="text"
      @keydown.enter="titleEdit"
      @keydown.esc="titleEdit"
    />
    &nbsp;
    <span v-if="editName" class="btn-edit">
      <svg
        fill="none"
        height="33"
        viewBox="0 0 32 33"
        width="32"
        xmlns="http://www.w3.org/2000/svg"
        @click="titleEdit"
      >
        <path
          d="M21.9706 11.8815L21.9706 11.8815L23.1822 13.1029C23.1822 13.1029 23.1822 13.1029 23.1822 13.1029C23.2387 13.1598 23.2383 13.2517 23.1815 13.3082C23.1814 13.3082 23.1814 13.3082 23.1814 13.3083L13.9025 22.5127L13.9025 22.5127C13.8456 22.5692 13.7537 22.5688 13.6972 22.5119L13.3423 22.864L13.6972 22.5119L8.81654 17.5917C8.81653 17.5917 8.81653 17.5917 8.81653 17.5917C8.76002 17.5347 8.76044 17.4428 8.81733 17.3864L8.46521 17.0314L8.81733 17.3864L10.0388 16.1748C10.0957 16.1183 10.1876 16.1187 10.244 16.1756L10.599 15.8235L10.244 16.1756L13.4587 19.4163L13.8108 19.7713L14.1658 19.4192L21.7653 11.8807L21.7654 11.8806C21.8223 11.8242 21.9142 11.8246 21.9706 11.8815ZM3.16602 16.4998C3.16602 9.41216 8.91167 3.6665 15.9993 3.6665C23.087 3.6665 28.8327 9.41216 28.8327 16.4998C28.8327 23.5875 23.087 29.3332 15.9993 29.3332C8.91167 29.3332 3.16602 23.5875 3.16602 16.4998ZM27.252 16.4998C27.252 10.2802 22.218 5.24715 15.9993 5.24715C9.77968 5.24715 4.74666 10.2811 4.74666 16.4998C4.74666 22.7195 9.78065 27.7525 15.9993 27.7525C22.219 27.7525 27.252 22.7185 27.252 16.4998Z"
          fill="#1ED790"
          stroke="#1ED790"
        />
      </svg>

      <svg
        fill="none"
        height="33"
        viewBox="0 0 32 33"
        width="32"
        xmlns="http://www.w3.org/2000/svg"
        @click="titleEditCancel"
      >
        <path
          d="M15.9993 3.1665C8.63376 3.1665 2.66602 9.13425 2.66602 16.4998C2.66602 23.8654 8.63376 29.8332 15.9993 29.8332C23.3649 29.8332 29.3327 23.8654 29.3327 16.4998C29.3327 9.13425 23.3649 3.1665 15.9993 3.1665ZM15.9993 27.2525C10.0585 27.2525 5.24666 22.4407 5.24666 16.4998C5.24666 10.559 10.0585 5.74715 15.9993 5.74715C21.9402 5.74715 26.752 10.559 26.752 16.4998C26.752 22.4407 21.9402 27.2525 15.9993 27.2525ZM21.4725 13.1558L18.1284 16.4998L21.4725 19.8439C21.7252 20.0966 21.7252 20.5052 21.4725 20.7579L20.2574 21.973C20.0047 22.2256 19.5961 22.2256 19.3434 21.973L15.9993 18.6289L12.6553 21.973C12.4026 22.2256 11.994 22.2256 11.7413 21.973L10.5262 20.7579C10.2735 20.5052 10.2735 20.0966 10.5262 19.8439L13.8703 16.4998L10.5262 13.1558C10.2735 12.9031 10.2735 12.4945 10.5262 12.2418L11.7413 11.0267C11.994 10.774 12.4026 10.774 12.6553 11.0267L15.9993 14.3708L19.3434 11.0267C19.5961 10.774 20.0047 10.774 20.2574 11.0267L21.4725 12.2418C21.7252 12.4945 21.7252 12.9031 21.4725 13.1558Z"
          fill="#EB5757"
        />
      </svg>
    </span>
    <span
      >{{ quote.nights + 1 }} {{ t('quote.label.days', quote.nights + 1) }} / {{ quote.nights }}
      {{ t('quote.label.nights', quote.nights + 1) }}</span
    >
  </div>
  <div v-if="!editName" class="edit" @click="titleEdit">
    <font-awesome-icon :style="{ color: '#EB5757', fontSize: '22px' }" icon="pen-to-square" />
    <span class="edit-text"> {{ t('quote.label.edit_name') }}</span>
  </div>
</template>

<style lang="scss" scoped></style>
