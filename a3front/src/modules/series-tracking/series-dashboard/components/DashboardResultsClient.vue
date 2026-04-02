<template>
  <section class="peru-facile">
    <!-- HERO -->
    <div class="hero">
      <img
        class="hero-image"
        src="https://res.cloudinary.com/litodti/image/upload/v1774876050/aurora/peru_facile/Banner_PeruFacile.jpg"
        alt="Peru Facile"
      />
      <div class="hero-overlay"></div>

      <div class="hero-content">
        <h1>PERU FACILE</h1>
        <p>Programmi in italiano per scoprire il Peru.</p>
        <p class="hero-sub">Italian-language programs to discover Peru.</p>
      </div>
    </div>

    <!-- WHY CHOOSE -->
    <div class="why-section">
      <h3 class="section-subtitle">
        Perché scegliere Peru Facile /
        <span>Why choose Peru Facile:</span>
      </h3>

      <div class="why-grid">
        <div v-for="item in whyChooseItems" :key="item.title" class="why-card">
          <div></div>
          <font-awesome-icon class="why-icon" :icon="['fas', item.icon]" />
          <div>
            <div class="why-title">{{ item.title }}</div>
            <div class="why-subtitle">{{ item.subtitle }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- AVAILABLE PROGRAMS -->
    <div class="programs-section">
      <div class="programs-header">
        Programmi disponibili /
        <span>Available programs:</span>
      </div>

      <div class="programs-grid">
        <div v-for="program in programs" :key="program.id" class="program-card">
          <img :src="program.image" :alt="program.name" class="program-image" />

          <div class="program-body">
            <div class="program-top">
              <h4>{{ program.name }}</h4>
              <span class="program-days">{{ program.duration }}</span>
            </div>

            <p class="program-desc-it">{{ program.descriptionIt }}</p>
            <p class="program-desc-en">{{ program.descriptionEn }}</p>
          </div>
          <div class="program-footer">
            <a class="program-link" :href="program.link">
              <font-awesome-icon class="why-icon" :icon="['fas', 'arrow-right']" /> Vedi di più /
              View more
            </a>
          </div>
        </div>
      </div>
    </div>
    <a-alert type="warning" show-icon banner class="custom-alert">
      <template #message>
        <p class="alert-title">Importante</p>
      </template>
      <template #description>
        <span class="alert-message">
          Le partenze si chiudono a 60 gg dalla data di inizio del tour. Da quel momento in poi la
          conferma sarà soggetta a disponibilità.
        </span>
        <a
          href="https://drive.google.com/file/d/1JlCW49mn_a97NYDEDpisiu_vIXX6r77L/view?usp=sharing"
          target="_blank"
          rel="noopener noreferrer"
          class="alert-link"
        >
          Visualizza le politiche
        </a>
      </template>
    </a-alert>
    <!-- Selectors -->
    <div class="selectors-container">
      <a-select
        v-model:value="selectedSalida"
        placeholder="Seleziona un'uscita"
        style="width: 200px"
        allow-clear
        @change="selectedProgram = null"
      >
        <a-select-option
          v-for="salida in props.composable.dashboardsClient.value"
          :key="salida.id"
          :value="salida.id"
        >
          Uscita {{ salida.numero }}
        </a-select-option>
      </a-select>

      <a-select
        v-model:value="selectedProgram"
        placeholder="Seleziona un programma"
        style="width: 240px"
        allow-clear
      >
        <a-select-option
          v-for="programName in uniquePrograms"
          :key="programName"
          :value="programName"
        >
          {{ programName }}
        </a-select-option>
      </a-select>
    </div>
    <a-row justify="end">
      <a-col :span="12">
        <p class="title-table">Scopri le partenze confermate / See confirmed departures:</p>
      </a-col>
      <a-col :span="12">
        <div class="view-switcher">
          <button
            class="view-btn"
            :class="{ active: viewMode === 'table' }"
            @click="viewMode = 'table'"
          >
            <font-awesome-icon :icon="['fas', 'table-list']" />
          </button>

          <button
            class="view-btn"
            :class="{ active: viewMode === 'cards' }"
            @click="viewMode = 'cards'"
          >
            <font-awesome-icon :icon="['fas', 'boxes-stacked']" />
          </button>
        </div>
      </a-col>
    </a-row>

    <!-- ===== SPINNER ===== -->
    <div v-if="loading" class="loading-container">
      <a-spin size="large" />
      <div class="loading-text">Caricamento...</div>
    </div>

    <!-- ===== RESULTADOS ===== -->
    <div v-else-if="salidasFiltradas.length">
      <!-- VISTA TABLA -->
      <template v-if="viewMode === 'table'">
        <div class="module-header">
          <span class="module-title">Uscita</span>
          <span class="module-title"> | Passeggeri totali</span>
        </div>

        <a-card v-for="salida in salidasFiltradas" :key="salida.id">
          <div class="card-header">
            <h5>Uscita {{ salida.numero }}</h5>
            <a-tag color="blue" style="font-size: 12px">
              Passeggeri totali : <strong>{{ salida.totalPax }}</strong>
            </a-tag>
          </div>

          <div class="table-header">
            <div class="col">PROGRAMMA - DATA DI PARTENZA</div>
            <div class="col">NUMERO DI PASSEGGERI</div>
          </div>

          <div class="table-body">
            <div
              class="row"
              v-for="program in salida.programs"
              :key="program.serie_departure_program_id"
            >
              <div class="col">
                <div style="font-weight: bold">{{ program.serie_program_name }}</div>
                <div>
                  <a-tag color="default" class="ms-2">
                    {{ program.date }}
                  </a-tag>
                </div>
              </div>

              <div class="col">
                {{ program.total_qty_passengers }}
              </div>
            </div>
          </div>
        </a-card>
      </template>

      <!-- VISTA CARDS -->
      <template v-else>
        <div v-for="salida in salidasFiltradas" :key="salida.id" class="departure-block">
          <div class="departure-summary">
            <div class="departure-main">
              <div class="departure-top">
                <h2>Uscita {{ salida.numero }}</h2>
              </div>

              <div class="departure-meta">
                <a-tag color="blue">
                  <span
                    ><font-awesome-icon :icon="['fas', 'users']" /> {{ salida.totalPax }} Passeggeri
                    totali
                  </span>
                </a-tag>
              </div>
            </div>
          </div>

          <div class="program-cards-grid">
            <div
              v-for="program in salida.programs"
              :key="program.serie_departure_program_id"
              class="program-departure-card"
            >
              <div class="program-card-top">
                <div>
                  <h3>{{ program.serie_program_name }}</h3>
                </div>

                <div class="program-pax">
                  <strong>{{ program.total_qty_passengers }} <span>PASSEGGERI</span></strong>
                </div>
              </div>

              <div class="program-date">
                <font-awesome-icon :icon="['fas', 'calendar-days']" />
                {{ program.date }}
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- ===== EMPTY ===== -->
    <a-empty v-else description="Sin resultados" />
  </section>
</template>

<script setup>
  import { computed, onMounted, ref } from 'vue';

  const viewMode = ref('table');

  const whyChooseItems = ref([
    {
      icon: 'plane',
      title: 'Partenze fisse garantite',
      subtitle: 'Guaranteed fixed departures',
    },
    {
      icon: 'users',
      title: 'Guida professionale in italiano',
      subtitle: 'Italian-speaking guide',
    },
    {
      icon: 'hotel',
      title: 'Hotel selezionati con cura',
      subtitle: 'Carefully selected hotels',
    },
    {
      icon: 'map-pin',
      title: 'Comfort e posizione ideale',
      subtitle: 'Comfort and ideal locations',
    },
  ]);

  const programs = ref([
    {
      id: 1,
      name: 'Peru’ Imperial',
      duration: '6D/5N',
      descriptionIt: 'I luoghi imperdibili del Perú.',
      descriptionEn: 'Peru’s must-see highlights.',
      image:
        'https://res.cloudinary.com/litodti/image/upload/c_scale,w_250/v1774876050/aurora/peru_facile/Peru_Imperial.jpg',
      link: 'https://drive.google.com/uc?export=download&id=1CnyeqO7pYFJIAB2rDXLy6stfQeMv6F0A',
    },
    {
      id: 2,
      name: 'Peru’ Mistico',
      duration: '8D/7N',
      descriptionIt: 'Cusco, Machu Picchu e Titicaca.',
      descriptionEn: 'Cusco, Machu Picchu, and Titicaca.',
      image:
        'https://res.cloudinary.com/litodti/image/upload/c_scale,w_250/v1774876050/aurora/peru_facile/Peru_Mistico.jpg',
      link: 'https://drive.google.com/uc?export=download&id=16IIKmuRPfzeW2FRAgWwDlFXDFjLZKmBf',
    },
    {
      id: 3,
      name: 'Peru’ Express',
      duration: '9D/8N',
      descriptionIt: 'Più destinazioni, meno tempo.',
      descriptionEn: 'More destinations, less time.',
      image:
        'https://res.cloudinary.com/litodti/image/upload/c_scale,w_250/v1774876050/aurora/peru_facile/Peru_Express.jpg',
      link: 'https://drive.google.com/uc?export=download&id=1isbygSc7uzW98t_p76jNfJhesP7GVwde',
    },
    {
      id: 4,
      name: 'Peru’ Classico',
      duration: '10D/9N',
      descriptionIt: 'Le icone del Perú in un solo viaggio.',
      descriptionEn: "Peru's icons in one journey.",
      image:
        'https://res.cloudinary.com/litodti/image/upload/c_scale,w_250/v1774876050/aurora/peru_facile/Peru_Classico.jpg',
      link: 'https://drive.google.com/uc?export=download&id=1WeQmwKAf4hC5TyagCgKD1wZSDZb2yh1_',
    },
    {
      id: 5,
      name: 'Peru’ Misterioso',
      duration: '13D/12N',
      descriptionIt: 'Costa e Ande in un unico itinerario.',
      descriptionEn: 'Coast and Andes in one itinerary.',
      image:
        'https://res.cloudinary.com/litodti/image/upload/c_scale,q_100,w_250/v1774876050/aurora/peru_facile/Peru_Misterioso.jpg',
      link: 'https://drive.google.com/uc?export=download&id=1rYdkbu_wUVgClPc3yN_uOlOQnfnSDWXH',
    },
  ]);
  const props = defineProps({
    composable: {
      type: Object,
      required: true,
    },
  });

  const selectedSalida = ref(null);
  const selectedProgram = ref(null);

  const uniquePrograms = computed(() => {
    const programNames = new Set();
    const sourceSalidas = selectedSalida.value
      ? props.composable.dashboardsClient.value.filter((s) => s.id === selectedSalida.value)
      : props.composable.dashboardsClient.value;

    if (sourceSalidas) {
      sourceSalidas.forEach((salida) => {
        if (salida.programs) {
          salida.programs.forEach((program) => {
            programNames.add(program.serie_program_name);
          });
        }
      });
    }
    return Array.from(programNames);
  });

  const salidasFiltradas = computed(() => {
    let salidas = props.composable.dashboardsClient.value;

    if (!salidas) return [];

    if (selectedSalida.value) {
      salidas = salidas.filter((salida) => salida.id === selectedSalida.value);
    }

    if (selectedProgram.value) {
      salidas = salidas
        .map((salida) => {
          const filteredPrograms = (salida.programs || []).filter(
            (program) => program.serie_program_name === selectedProgram.value
          );
          return { ...salida, programs: filteredPrograms };
        })
        .filter((salida) => salida.programs.length > 0);
    }

    return salidas;
  });

  const loading = computed(() => props.composable.loading.value);

  onMounted(() => {
    props.composable.fetchTrackingClientControls();
  });
</script>

<style scoped>
  .selectors-container {
    display: flex;
    gap: 16px;
    margin-top: 30px;
    margin-bottom: 30px;
  }

  .ant-alert {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    border: 1px solid #ffcc00 !important;
  }

  .custom-alert :deep(.ant-alert-description a) {
    font-weight: 400;
    text-decoration: underline;
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }

  .table-header,
  .row {
    display: grid;
    grid-template-columns: 1fr 150px;
    align-items: center;
  }

  .table-header {
    background: #f5f7fa;
    padding: 12px 0;
    font-weight: 700;
    font-size: 12px;
    color: #7b8794;
  }

  .row {
    padding: 14px 0;
    border-bottom: 1px solid #eee;
    font-size: 13px;
  }

  .col {
    padding: 0 16px;
    text-align: center;
  }

  .module-header {
    background: #1f2937;
    color: white;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px 8px 0 0;
  }

  .module-title {
    font-size: 16px;
    font-weight: 600;
  }

  .module-header .module-title:first-child {
    margin-left: 0.5cm;
  }

  .module-header .module-title:last-child {
    margin-right: 1.5cm;
  }
  .loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 300px;
  }

  .loading-text {
    margin-top: 12px;
    font-weight: 600;
    color: #7b8794;
  }

  .peru-facile {
    padding: 20px;
    background: #f6f6f6;
    font-family: Montserrat, sans-serif;
  }

  .peru-facile,
  .peru-facile * {
    font-family: Montserrat, sans-serif;
  }

  /* HERO */
  .hero {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    min-height: 210px;
    margin-bottom: 28px;
  }

  .hero-image {
    width: 100%;
    height: 210px;
    object-fit: cover;
    display: block;
  }

  .hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
      90deg,
      rgba(0, 0, 0, 0.55) 0%,
      rgba(0, 0, 0, 0.2) 45%,
      rgba(0, 0, 0, 0.15) 100%
    );
  }

  .hero-content {
    position: absolute;
    top: 50%;
    left: 36px;
    transform: translateY(-50%);
    color: white;
    z-index: 2;
  }

  .hero-content h1 {
    font-size: 52px;
    font-weight: 800;
    line-height: 1;
    margin: 0 0 12px;
  }

  .hero-content p {
    font-size: 18px;
    margin: 0;
  }

  .hero-sub {
    font-style: italic;
    opacity: 0.95;
  }

  /* WHY */
  .why-section {
    margin-bottom: 24px;
  }

  .section-subtitle {
    font-size: 14px !important;
    font-weight: 500;
    color: #575757;
    margin-bottom: 18px;
  }

  .section-subtitle span {
    font-style: italic;
    color: #6a6a6a;
  }

  .why-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
  }

  .why-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #f8dede;
    border-radius: 12px;
    padding: 18px 20px;
  }

  .why-icon {
    font-size: 18px;
    color: #eb5757;
  }

  .why-title {
    font-size: 14px;
    color: #575757;
    font-weight: 500;
  }

  .why-subtitle {
    font-size: 14px;
    color: #777;
    font-style: italic;
    margin-top: 4px;
  }

  /* PROGRAMS */
  .programs-section {
    border: 1px solid #e9e9e9;
    border-radius: 8px;
    background: white;
    padding: 18px;
    margin-bottom: 25px;
  }

  .programs-header {
    font-size: 18px;
    margin-bottom: 20px;
    color: #575757;
    font-weight: 600;
  }

  .programs-header span {
    font-style: italic;
  }

  .programs-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
  }

  .program-card {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
  }

  .program-image {
    width: 100%;
    height: 112px;
    object-fit: cover;
    border-radius: 8px;
  }

  .program-body {
    height: 100px;
    padding: 12px 6px 8px;
  }

  .program-footer {
    font-size: 12px;
    text-align: right;
  }

  .program-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
  }

  .program-top h4 {
    margin: 0;
    font-size: 16px !important;
    color: #575757;
    font-weight: 700;
  }

  .program-days {
    font-size: 14px;
    color: #575757;
    white-space: nowrap;
    font-weight: 500;
  }

  .program-desc-it {
    margin: 12px 0 4px;
    font-size: 12px;
    color: #575757;
    font-weight: 400;
  }

  .program-desc-en {
    margin: 0 0 12px;
    font-size: 12px;
    color: #575757;
    font-style: italic;
    font-weight: 400;
  }

  .program-link {
    color: #eb5757;
    text-decoration: underline;
    font-weight: 500;
    font-size: 14px;
  }

  .program-link svg {
    font-size: 14px;
  }

  .program-link:hover {
    text-decoration: underline;
  }

  /* RESPONSIVE */
  @media (max-width: 1200px) {
    .programs-grid {
      grid-template-columns: repeat(3, 1fr);
    }

    .why-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .hero-content h1 {
      font-size: 34px;
    }

    .why-grid,
    .programs-grid {
      grid-template-columns: 1fr;
    }
  }

  .alert-title {
    color: #e4b804;
    font-weight: 700;
    font-size: 16px;
    font-family: Montserrat, sans-serif;
    margin-bottom: 0;
  }

  .alert-message {
    color: #e4b804;
    font-weight: 400;
    font-size: 14px;
    font-family: Montserrat, sans-serif;
  }

  .alert-link {
    color: #55a3ff !important;
    font-weight: 400;
    font-size: 14px;
    font-family: Montserrat, sans-serif;
  }

  .title-table {
    font-size: 18px;
    font-weight: 600;
    font-style: italic;
    margin-top: 10px;
    margin-bottom: 10px;
  }

  .view-switcher {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    justify-content: flex-end;
  }

  .view-btn {
    border: 1px solid #d9d9d9;
    background: #fff;
    color: #0f2742;
    border-radius: 8px;
    padding: 8px 16px;
    cursor: pointer;
    font-family: Montserrat, sans-serif;
    font-weight: 600;
  }

  .view-btn.active {
    background: #0f2742;
    color: #fff;
    border-color: #0f2742;
  }

  .departure-block {
    margin-bottom: 32px;
  }

  .departure-summary {
    display: flex;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 20px;
  }

  .departure-id-box {
    width: 62px;
    min-width: 62px;
    height: 62px;
    border-radius: 12px;
    background: #f8dede;
    color: #dd5f5f;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  .departure-id-label {
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
  }

  .departure-id-value {
    font-size: 18px;
    font-weight: 800;
    margin-top: 4px;
  }

  .departure-main {
    flex: 1;
  }

  .departure-top {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 8px;
  }

  .departure-top h2 {
    margin: 0;
    font-size: 24px !important;
    font-weight: 800;
    color: #575757;
  }

  .departure-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    color: #394b63;
    font-size: 15px;
    font-weight: 500;
  }

  .program-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
  }

  .program-departure-card {
    background: #fff;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 2px 10px rgba(15, 39, 66, 0.06);
  }

  .program-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
  }

  .program-card-top h3 {
    margin: 0 0 10px;
    font-size: 22px !important;
    font-weight: 800;
    color: #575757;
  }

  .program-code {
    display: inline-block;
    background: #eef1f5;
    color: #70839a;
    font-size: 14px;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 6px;
  }

  .program-pax {
    text-align: right;
    color: #8d9cb0;
  }

  .program-pax strong {
    display: block;
    font-size: 26px;
    line-height: 1;
    color: #575757;
    font-weight: 800;
  }

  .program-pax span {
    font-size: 14px;
    font-weight: 700;
  }

  .program-date {
    color: #2f4259;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 18px;
  }

  .program-progress {
    width: 100%;
    height: 4px;
    background: #e8edf2;
    border-radius: 999px;
    overflow: hidden;
  }

  .program-progress-bar {
    height: 100%;
    background: #575757;
    border-radius: 999px;
  }

  @media (max-width: 992px) {
    .program-cards-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .departure-summary {
      flex-direction: column;
    }

    .departure-top h2 {
      font-size: 20px;
    }
  }
</style>
