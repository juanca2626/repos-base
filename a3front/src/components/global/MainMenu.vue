<template>
  <a-menu class="main-menu" mode="horizontal">
    <a-menu-item key="packages" v-if="validatePermission('mfpackages', 'read')">
      <a :href="url + 'packages'">
        {{ t('global.label.packages') }}
      </a>
    </a-menu-item>
    <a-menu-item key="hotels" v-if="validatePermission('mfhotels', 'read')">
      <a :href="url + 'hotels'">
        {{ t('global.label.hotels') }}
      </a>
    </a-menu-item>
    <a-menu-item key="services" v-if="validatePermission('mfservices', 'read')">
      <a :href="url + 'services'">
        {{ t('global.label.services') }}
      </a>
    </a-menu-item>
    <a-menu-item key="quote" v-if="validatePermission('mfquotationboard', 'read')">
      <a :href="'/quotes'"> {{ t('global.label.quotation_board') }} </a>
    </a-menu-item>
    <a-sub-menu
      key="files"
      v-if="
        validatePermission('mffilesa3query', 'read') ||
        validatePermission('mffilesquery', 'read') ||
        (client_id != '' && user_type_id == 3) ||
        validatePermission('mfquadfiles', 'read') ||
        validatePermission('mfservicetracking', 'read') ||
        validatePermission('mfproductnoconforming', 'read') ||
        validatePermission('mfclaim', 'read') ||
        validatePermission('mfcongratulation', 'read') ||
        validatePermission('mfexecutiveboard', 'read') ||
        validatePermission('mfreport', 'read') ||
        validatePermission('mfstadisticcharts', 'read')
      "
    >
      <template #title>{{ t('global.label.files') }}</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item
        key="files_a3_query"
        v-if="
          validatePermission('mffilesa3query', 'read') || (client_id != '' && user_type_id == 3)
        "
      >
        <a :href="'/files'"> {{ t('global.label.files_query') }} - A3 </a>
      </a-menu-item>
      <a-menu-item
        key="files_query"
        v-if="validatePermission('mffilesquery', 'read') || (client_id != '' && user_type_id == 3)"
      >
        <a :href="url + 'consulta_files'">
          {{ t('global.label.files_query') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="report/files"
        v-if="validatePermission('mfquadfiles', 'read') && user_type_id == 3"
      >
        <a :href="url + 'reports/files'">
          {{ t('global.label.file_quad') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="files_query"
        v-if="
          validatePermission('mfservicetracking', 'read') && client_id != '' && user_type_id == 3
        "
      >
        <a
          :href="
            'https://extranet.litoapps.com/migration/monitoreo.php?u=' + code + '&t=u&l=' + lang
          "
        >
          {{ t('global.label.files_query') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="product_no_conforming"
        v-if="validatePermission('mfproductnoconforming', 'read') && user_type_id == 3"
      >
        <a
          :href="
            'https://extranet.litoapps.com/migration/producto-no-conforme.php?u=' +
            code +
            '&t=u&l=' +
            lang
          "
        >
          {{ t('global.label.product_no_conforming') }}
        </a>
      </a-menu-item>
      <a-menu-item key="claim" v-if="validatePermission('mfclaim', 'read') && user_type_id == 3">
        <a
          :href="'https://extranet.litoapps.com/migration/reclamo.php?u=' + code + '&t=u&l=' + lang"
        >
          {{ t('global.label.claim') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="congratulation"
        v-if="validatePermission('mfcongratulation', 'read') && user_type_id == 3"
      >
        <a
          :href="
            'https://extranet.litoapps.com/migration/felicitacion.php?u=' + code + '&t=u&l=' + lang
          "
        >
          {{ t('global.label.congratulation') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="executive_board"
        v-if="validatePermission('mfexecutiveboard', 'read') && user_type_id == 3"
      >
        <a :href="url + 'board'">
          {{ t('global.label.executive_board') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="reports"
        v-if="validatePermission('mfreport', 'read') || (client_id != '' && user_type_id == 3)"
      >
        <a :href="url + 'reportes-reservas'">
          {{ t('global.label.reports') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="reports"
        v-if="validatePermission('mfstadisticcharts', 'read') || user_type_id == 3"
      >
        <a :href="url + 'reports'">
          {{ t('global.label.stadistic_charts') }}
        </a>
      </a-menu-item>
    </a-sub-menu>
    <a-menu-item
      key="multimedia"
      v-if="validatePermission('mfphotos', 'read') || validatePermission('mfvideos', 'read')"
    >
      <a :href="url + 'multimedia'">
        {{ t('global.label.multimedia') }}
      </a>
    </a-menu-item>

    <a-menu-item key="master_sheets" v-if="validatePermission('mfseriesfacile', 'read')">
      <a :href="'/series/series-dashboards'"> Perú Facile </a>
    </a-menu-item>
    <a-sub-menu
      key="users"
      v-if="
        validatePermission('mfusuariostom', 'read') ||
        validatePermission('mforderreports', 'read') ||
        validatePermission('mfcustomercard', 'read') ||
        validatePermission('incacalendar', 'read')
      "
    >
      <template #title>{{ t('global.label.users') }}</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item key="usersTOM" v-if="validatePermission('mfusuariostom', 'read')">
        <a :href="url + 'users'">
          {{ t('global.label.usersTOM') }}
        </a>
      </a-menu-item>
      <a-menu-item key="order_reports" v-if="validatePermission('mforderreports', 'read')">
        <a :href="url + 'report_orders'">
          {{ t('global.label.order_reports') }}
        </a>
      </a-menu-item>
      <a-menu-item key="customer_card" v-if="validatePermission('mfcustomercard', 'read')">
        <a :href="url + 'customers/card'">
          {{ t('global.label.customer_card') }}
        </a>
      </a-menu-item>
      <a-menu-item key="calendario_inca" v-if="validatePermission('incacalendar', 'read')">
        <a :href="url + 'calendario_inca'"> Calendario INCA </a>
      </a-menu-item>
    </a-sub-menu>
    <a-sub-menu
      key="order_center"
      v-if="
        validatePermission('mfmyorders', 'read') ||
        validatePermission('mfdashboard', 'read') ||
        validatePermission('mfreports', 'read')
      "
    >
      <template #title>{{ t('global.label.order_center') }}</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item key="my_orders" v-if="validatePermission('mfmyorders', 'read')">
        <a :href="url + 'orders'">
          {{ t('global.label.my_orders') }}
        </a>
      </a-menu-item>
      <a-menu-item key="dashboard" v-if="validatePermission('mfdashboard', 'read')">
        <a :href="url + 'dashboard'">
          {{ t('global.label.dashboard') }}
        </a>
      </a-menu-item>
      <a-menu-item key="reports_orders" v-if="validatePermission('mfreports', 'read')">
        <a :href="url + 'reports/orders'">
          {{ t('global.label.reports_orders') }}
        </a>
      </a-menu-item>
    </a-sub-menu>
    <a-sub-menu
      key="billings"
      v-if="
        validatePermission('mfbillingreport', 'read') ||
        validatePermission('mfproductivityreport', 'read')
      "
    >
      <template #title>{{ t('global.label.billings') }}</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item key="billing_report" v-if="validatePermission('mfbillingreport', 'read')">
        <a :href="url + 'billing_report'">
          {{ t('global.label.billing_report') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="productivity_report"
        v-if="validatePermission('mfproductivityreport', 'read')"
      >
        <a :href="url + 'productivity_report'">
          {{ t('global.label.productivity_report') }}
        </a>
      </a-menu-item>
    </a-sub-menu>
    <a-sub-menu key="OTS" v-if="validatePermission('mfcentralots', 'read')">
      <template #title>OTS</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item key="central" v-if="validatePermission('mfcentralots', 'read')">
        <a :href="url + 'central_bookings/tourcms'"> {{ t('global.label.central') }} OTS </a>
      </a-menu-item>
    </a-sub-menu>
    <a-sub-menu
      key="facile"
      v-if="
        (validatePermission('mfprogramation', 'read') ||
          validatePermission('mfconfirmationlist', 'read')) &&
        user_type_id == 3
      "
    >
      <template #title>Facile</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item key="programation" v-if="validatePermission('mfprogramation', 'read')">
        <a :href="url + 'programacion'">
          {{ t('global.label.programation') }}
        </a>
      </a-menu-item>
      <a-menu-item key="confirmation_list" v-if="validatePermission('mfconfirmationlist', 'read')">
        <a :href="url + 'lista_confirmacion'">
          {{ t('global.label.confirmation_list') }}
        </a>
      </a-menu-item>
    </a-sub-menu>
    <a-sub-menu
      key="MASI"
      v-if="
        (validatePermission('mfconfigurationmasi', 'read') ||
          validatePermission('mfconfirmationlist', 'read')) &&
        user_type_id == 3
      "
    >
      <template #title>MASI</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item key="masi_mailing" v-if="validatePermission('mfconfigurationmasi', 'read')">
        <a :href="url + 'masi_mailing'"> Configuración de correos y horarios </a>
      </a-menu-item>
      <a-menu-item key="masi_statistics" v-if="validatePermission('masistatistics', 'read')">
        <a :href="url + 'masi_statistics'"> Estadísticas </a>
      </a-menu-item>
      <a-menu-item key="masi_logs" v-if="validatePermission('masilogs', 'read')">
        <a :href="url + 'masi_logs'"> Correos de Prueba - Logs </a>
      </a-menu-item>
      <a-menu-item key="chatbot" v-if="validatePermission('masistatistics', 'read')">
        <a :href="'https://masi.pe/login?token=' + access_token"> Configuración Chatbot </a>
      </a-menu-item>
    </a-sub-menu>
    <a-menu-item key="helpdesk" v-if="user_type_id == 3 || user_type_id == 4">
      <a :href="sdesk_link">
        {{ t('global.label.helpdesk') }}
      </a>
    </a-menu-item>
    <a-sub-menu
      key="cosig_reports"
      v-if="
        validatePermission('mfstatclients', 'read') || validatePermission('mfreportcosig', 'read')
      "
    >
      <template #title>{{ t('global.label.cosig_reports') }}</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item key="stat_clients" v-if="validatePermission('mfstatclients', 'read')">
        <a :href="url + 'stats'">
          {{ t('global.label.stat_clients') }}
        </a>
      </a-menu-item>
      <a-menu-item key="cosig_reports" v-if="validatePermission('mfreportcosig', 'read')">
        <a :href="url + 'reports/cosig'">
          {{ t('global.label.cosig_reports') }}
        </a>
      </a-menu-item>
      <a-menu-item key="stats_login" v-if="validatePermission('mfreportcosig', 'read')">
        <a :href="url + 'stats/login'"> Accesos A2 </a>
      </a-menu-item>
    </a-sub-menu>
    <a-sub-menu
      key="stella"
      v-if="
        validatePermission('mffilesmanagementstela', 'read') ||
        validatePermission('mftrackingstela', 'read') ||
        validatePermission('mfcheckpaymentsupplier', 'read') ||
        validatePermission('mfobservedaccountingdocumentsstela', 'read') ||
        validatePermission('mfunlockfilestela', 'read') ||
        validatePermission('mfadminsalesestela', 'read')
      "
    >
      <template #title>STELLA</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item
        key="files_management"
        v-if="validatePermission('mffilesmanagementstela', 'read')"
      >
        <a
          :href="
            'http://192.168.250.20:8200/wa/r/litt0160?Arg=' +
            code +
            '&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa bkcjbaskub873y82y8y81hh88r83i'
          "
        >
          {{ t('global.label.files_management') }}
        </a>
      </a-menu-item>
      <a-menu-item key="tracking_programation" v-if="validatePermission('mftrackingstela', 'read')">
        <a
          :href="
            'http://192.168.250.20:8200/wa/r/litt1030?Arg=' +
            code +
            '&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'
          "
        >
          {{ t('global.label.tracking_programation') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="check_payments_supplier"
        v-if="validatePermission('mfcheckpaymentsupplier', 'read')"
      >
        <a
          :href="
            'http://192.168.250.20:8200/wa/r/litt1530?Arg=' +
            code +
            '&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'
          "
        >
          {{ t('global.label.check_payments_supplier') }}
        </a>
      </a-menu-item>
      <a-menu-item
        key="observed_accounting_documents"
        v-if="validatePermission('mfobservedaccountingdocumentsstela', 'read')"
      >
        <a
          :href="
            'http://192.168.250.20:8200/wa/r/litt1570?Arg=' +
            code +
            '&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'
          "
        >
          {{ t('global.label.observed_accounting_documents') }}
        </a>
      </a-menu-item>
      <a-menu-item key="unlockfile" v-if="validatePermission('mfunlockfilestela', 'read')">
        <a
          :href="
            'http://192.168.250.20:8200/wa/r/turdesb?Arg=' +
            code +
            '&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'
          "
        >
          {{ t('global.label.unlockfile') }}
        </a>
      </a-menu-item>
      <a-menu-item key="adminsales" v-if="validatePermission('mfadminsalesestela', 'read')">
        <a
          :href="
            'http://192.168.250.20:8200/wa/r/litt0150?Arg=' +
            code +
            '&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'
          "
        >
          {{ t('global.label.adminsales') }}
        </a>
      </a-menu-item>
    </a-sub-menu>
    <a-sub-menu key="rates">
      <template #title>{{ t('global.label.download_rates') }}</template>
      <template #icon>
        <caret-down-outlined />
      </template>
      <a-menu-item key="setting:1">{{ t('global.label.download_hotels_rates') }} 2025</a-menu-item>
      <a-menu-item key="setting:2">{{ t('global.label.download_rate_services') }} 2025</a-menu-item>
    </a-sub-menu>
  </a-menu>
</template>

<script setup>
  import { onMounted, ref } from 'vue';
  import { CaretDownOutlined } from '@ant-design/icons-vue';
  import { useI18n } from 'vue-i18n';
  import { useLanguagesStore } from '@/stores/global';
  import {
    getUrlAuroraFront,
    getUserClientId,
    getUserCode,
    getUserEmail,
    getUserType,
  } from '@/utils/auth';
  import { useModulePermission } from '@/composables/useModulePermission';

  const url = ref('');
  const code = ref('');
  const client_id = ref('');
  const user_type_id = ref('');
  const lang = ref('');
  const sdesk_link = ref('');
  const loading = ref(true);

  defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });

  const { t } = useI18n({
    useScope: 'global',
  });

  const { canRaw } = useModulePermission();

  const languagesStore = useLanguagesStore();

  onMounted(() => {
    let user_email_ = getUserEmail();

    if (user_email_ != '' && user_email_ != null) {
      let email_64 = new Buffer(user_email_);
      email_64 = email_64.toString('base64');

      sdesk_link.value =
        'https://solutionsdesk.net/customers/ticket/new' +
        '?organization=92198113-85da-37f4-8f2f-9874e8c4387c&access_token=' +
        'a079f04cc48dea32751881ccbdf996b4&client_email=' +
        email_64;
    }

    // --- SETEO VALUES -- //
    url.value = getUrlAuroraFront();
    code.value = getUserCode();
    client_id.value = getUserClientId();
    user_type_id.value = getUserType();

    lang.value = languagesStore.getLanguage;

    setTimeout(function () {
      loading.value = false;
    }, 1000);
  });

  const validatePermission = (subject, action) => {
    return canRaw(action, subject);
  };
</script>
