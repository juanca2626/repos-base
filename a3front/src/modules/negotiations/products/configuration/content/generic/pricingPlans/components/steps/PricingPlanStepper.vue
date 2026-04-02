<template>
  <div class="stepper-container">
    <div class="custom-stepper">
      <!-- Línea de fondo única que conecta todos los steps -->
      <div class="stepper-line-background"></div>
      <!-- Línea de progreso azul -->
      <div class="stepper-line-progress" :style="progressLineStyle"></div>

      <!-- Step 1 - Datos básicos -->
      <div
        class="stepper-step"
        :class="{ active: currentStep === 1, completed: currentStep > 1 }"
        @click="handleStepClick(1)"
      >
        <div class="step-icon-wrapper">
          <!-- Icono completado (checkmark) -->
          <svg
            v-if="currentStep > 1"
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle cx="20" cy="20" r="19" fill="#1284ED" stroke="#1284ED" stroke-width="2" />
            <path
              d="M16 20L18.5 22.5L24 17"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <!-- Icono normal -->
          <svg
            v-else
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <rect
              x="1"
              y="1"
              width="38"
              height="38"
              rx="19"
              fill="white"
              :stroke="currentStep === 1 ? '#575B5F' : '#BABCBD'"
              stroke-width="2"
            />
            <path
              d="M19.9449 18.2849L18.8439 17.6226C18.9008 17.3133 18.935 17.0001 18.935 16.6789C18.935 16.3497 18.9008 16.0325 18.8439 15.6875L19.9449 15.057C20.1079 14.9587 20.1897 14.7519 20.1303 14.5653C19.8538 13.6652 19.4134 12.9122 18.8477 12.2448C18.7717 12.1556 18.6654 12.1096 18.5553 12.1096C18.4846 12.1096 18.4134 12.1286 18.3495 12.167L17.2264 12.8448C16.7708 12.4363 16.2506 12.1175 15.6735 11.9066V10.5544C15.6735 10.3584 15.5437 10.1837 15.3599 10.1435C14.9445 10.0528 14.5154 10 14.075 10C13.6342 10 13.2063 10.0532 12.7913 10.144C12.6075 10.1843 12.4781 10.3585 12.4781 10.5544V11.9062C11.8994 12.1175 11.3777 12.4363 10.9228 12.8448L9.79934 12.1671C9.73555 12.1286 9.66417 12.1096 9.59355 12.1096C9.48371 12.1096 9.3791 12.1559 9.30301 12.2455C8.73622 12.9126 8.29456 13.6652 8.01928 14.5653C7.96002 14.7517 8.04169 14.9579 8.20469 15.057L9.3073 15.722C9.25031 16.0325 9.215 16.3497 9.215 16.6789C9.215 17.0015 9.25082 17.3149 9.30594 17.6207L8.20469 18.2849C8.04169 18.384 7.96002 18.5902 8.01928 18.7409C8.29456 19.6065 8.73272 20.394 9.29921 21.0614C9.37523 21.1875 9.48382 21.2311 9.59355 21.2311C9.66421 21.2311 9.73548 21.2121 9.79934 21.1737L10.9122 20.5055C11.3693 20.9183 11.896 21.2402 12.478 21.4528V22.7887C12.478 22.9846 12.6078 23.1567 12.7916 23.1968C13.2055 23.2852 13.6346 23.3407 14.075 23.3407C14.5158 23.3407 14.9437 23.2847 15.3587 23.194C15.5425 23.1537 15.6718 22.9821 15.6718 22.7863V21.4532C16.2543 21.2404 16.7805 20.9183 17.2377 20.5055L18.3503 21.1756C18.4141 21.2141 18.4854 21.2331 18.5561 21.2331C18.6658 21.2331 18.7704 21.184 18.8466 21.0944C19.4131 20.427 19.855 19.643 20.1303 18.777C20.1917 18.5902 20.1082 18.384 19.9449 18.2849ZM14.075 18.5823C13.0684 18.5823 12.2525 17.7301 12.2525 16.6789C12.2525 15.6277 13.0684 14.7755 14.075 14.7755C15.0815 14.7755 15.8975 15.6277 15.8975 16.6789C15.8975 17.7301 15.0812 18.5823 14.075 18.5823ZM31.8633 22.3137C31.8247 22.1217 31.6578 21.9866 31.47 21.9866H30.1756C29.9735 21.3831 29.6683 20.8374 29.2773 20.3624L29.9262 19.1886C29.963 19.122 29.9812 19.0475 29.9812 18.9737C29.9812 18.859 29.9369 18.7498 29.8511 18.6703C29.2121 18.0787 28.4588 17.6171 27.6296 17.3296C27.451 17.2677 27.253 17.353 27.1588 17.5232L26.522 18.6748C26.1908 18.614 25.887 18.5783 25.6061 18.5783C25.2971 18.5783 24.9971 18.6157 24.7043 18.6733L24.0683 17.5235C23.9742 17.3533 23.776 17.268 23.5975 17.3299C22.7687 17.6174 22.0146 18.075 21.3756 18.6666C21.2898 18.7461 21.2458 18.8592 21.2458 18.9738C21.2458 19.0476 21.264 19.1221 21.3008 19.1888L21.9433 20.351C21.548 20.8284 21.2397 21.3784 21.0362 21.9863H19.7551C19.5675 21.9863 19.3946 22.1209 19.3561 22.3128C19.2691 22.7459 19.2273 23.194 19.2273 23.654C19.2273 24.1143 19.2723 24.562 19.3592 24.9951C19.3944 25.1886 19.5691 25.3234 19.7551 25.3234H21.0343C21.238 25.9317 21.5465 26.4813 21.9417 26.9587L21.2993 28.121C21.2624 28.1876 21.2442 28.2621 21.2442 28.3359C21.2442 28.4505 21.2839 28.5636 21.3697 28.6431C22.0087 29.2347 22.7669 29.6924 23.5958 29.9798C23.7744 30.0418 23.9725 29.9565 24.0666 29.7862L24.7022 28.6363C24.9948 28.6939 25.2947 28.7296 25.6061 28.7296C25.92 28.7296 26.225 28.6926 26.5223 28.6332L27.159 29.7847C27.2532 29.955 27.4512 30.0403 27.6298 29.9784C28.459 29.6909 29.2127 29.2289 29.8517 28.6373C29.9375 28.5577 29.9814 28.4489 29.9814 28.3342C29.9814 28.2605 29.9632 28.1861 29.9263 28.1193L29.2775 26.9456C29.6682 26.4705 29.9738 25.9249 30.1758 25.3214H31.4701C31.6578 25.3214 31.8252 25.1858 31.8639 24.9939C31.9506 24.562 32 24.1139 32 23.654C32 23.194 31.9506 22.7459 31.8633 22.3137ZM25.6061 25.5573C24.5995 25.5573 23.7836 24.7052 23.7836 23.654C23.7836 22.6027 24.5995 21.7506 25.6061 21.7506C26.6126 21.7506 27.4286 22.6027 27.4286 23.654C27.4286 24.7052 26.6122 25.5573 25.6061 25.5573Z"
              :fill="currentStep === 1 ? '#575B5F' : '#BABCBD'"
            />
          </svg>
        </div>
        <span class="step-title">Datos básicos</span>
      </div>

      <!-- Step 2 - Staff e impuestos -->
      <div
        class="stepper-step"
        :class="{ active: currentStep === 2, completed: currentStep > 2 }"
        @click="handleStepClick(2)"
      >
        <div class="step-icon-wrapper">
          <!-- Icono completado (checkmark) -->
          <svg
            v-if="currentStep > 2"
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle cx="20" cy="20" r="19" fill="#1284ED" stroke="#1284ED" stroke-width="2" />
            <path
              d="M16 20L18.5 22.5L24 17"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <!-- Icono normal -->
          <svg
            v-else
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <rect
              x="1"
              y="1"
              width="38"
              height="38"
              rx="19"
              fill="white"
              :stroke="currentStep === 2 ? '#575B5F' : '#BABCBD'"
              stroke-width="2"
            />
            <g clip-path="url(#clip0_stepper_step2)">
              <path
                d="M27.2 16.25C28.8568 16.25 30.2 14.8508 30.2 13.125C30.2 11.3992 28.8575 10 27.2 10C25.5433 10 24.2 11.3992 24.2 13.125C24.2 14.8508 25.5425 16.25 27.2 16.25ZM12.8 16.25C14.4568 16.25 15.8 14.8508 15.8 13.125C15.8 11.3992 14.4575 10 12.8 10C11.1433 10 9.8 11.3992 9.8 13.125C9.8 14.8508 11.1433 16.25 12.8 16.25ZM19.9963 22.5C22.1491 22.5 23.8625 20.6812 23.8625 18.4375C23.8625 16.1937 22.1173 14.375 19.9963 14.375C17.8434 14.375 16.13 16.1937 16.13 18.4375C16.0963 20.6797 17.8438 22.5 19.9963 22.5ZM21.8 25.625C21.8 24.973 21.9274 24.357 22.1232 23.775C22.0363 23.7695 21.9575 23.75 21.8713 23.75H18.1288C15.185 23.75 12.8 26.082 12.8 28.957C12.8 29.5352 13.2762 30 13.865 30H23.8438C22.6063 28.9688 21.8 27.4023 21.8 25.625ZM14.8963 18.4375C14.8963 18.2246 14.9331 18.0223 14.9566 17.816C14.5475 17.582 14.0975 17.5 13.6213 17.5H11.303C9.479 17.5 8 19.1328 8 21.1445C8 21.5469 8.29576 21.875 8.66075 21.875H16.142C15.3763 20.9453 14.8963 19.7539 14.8963 18.4375ZM28.6963 17.5H26.3773C25.8973 17.5 25.4443 17.6186 25.0325 17.8219C25.055 18.0273 25.0925 18.2266 25.0925 18.4375C25.0925 19.2762 24.8816 20.0539 24.5431 20.7555C25.3325 20.2891 26.2288 20 27.2 20C28.793 20 30.2101 20.732 31.1975 21.875H31.3384C31.7038 21.875 32 21.5469 32 21.1445C32 19.1328 30.5225 17.5 28.6963 17.5ZM31.1787 24.3242C30.9903 23.6988 30.6691 23.1281 30.2506 22.6559C30.1878 22.5832 30.0831 22.565 29.9994 22.6159L29.2805 23.0483C28.9769 22.7793 28.628 22.5686 28.2512 22.4304V21.5273C28.2512 21.4291 28.1814 21.342 28.0906 21.3237C27.4696 21.1783 26.8344 21.182 26.2411 21.3237C26.1838 21.375 26.1163 21.4648 26.1163 21.5273V22.3961C25.7394 22.5343 25.3903 22.745 25.0869 23.0141L24.3646 22.5816C24.2844 22.5307 24.1797 22.5489 24.1135 22.6216C23.6946 23.0943 23.3736 23.665 23.1887 24.29C23.1608 24.3881 23.1992 24.4898 23.2795 24.5371L23.9984 24.9734C23.9286 25.3804 23.9286 25.8019 23.9984 26.2093L23.2795 26.6457C23.1957 26.6929 23.1608 26.7947 23.1887 26.8929C23.3737 27.5182 23.6946 28.085 24.1135 28.5612C24.1797 28.634 24.281 28.6521 24.3646 28.6012L25.0869 28.1688C25.3904 28.4379 25.7394 28.6485 26.1163 28.7868V29.6555C26.1163 29.7537 26.1826 29.8409 26.2733 29.8591C26.8981 30.0045 27.5296 30.0009 28.1228 29.8591C28.2136 29.8409 28.2833 29.7537 28.2833 29.6555V28.7868C28.6602 28.6487 29.0093 28.4379 29.3127 28.1688L30.0316 28.6012C30.1119 28.6521 30.22 28.634 30.2828 28.5612C30.7017 28.0886 31.0227 27.5182 31.2109 26.8929C31.2388 26.7947 31.2005 26.6929 31.1167 26.6457L30.3979 26.2093C30.4711 25.8023 30.4711 25.3804 30.3979 24.9734L31.1167 24.5371C31.2013 24.5234 31.2388 24.4219 31.1787 24.3242ZM27.2 26.875C26.5374 26.875 26 26.3152 26 25.625C26 24.9348 26.5374 24.375 27.2 24.375C27.8626 24.375 28.4 24.9348 28.4 25.625C28.4 26.3164 27.8638 26.875 27.2 26.875Z"
                :fill="currentStep === 2 ? '#575B5F' : '#BABCBD'"
              />
            </g>
            <defs>
              <clipPath id="clip0_stepper_step2">
                <rect width="24" height="24" fill="white" transform="translate(8 8)" />
              </clipPath>
            </defs>
          </svg>
        </div>
        <span class="step-title">Staff e impuestos</span>
      </div>

      <!-- Step 3 - Montos -->
      <div
        class="stepper-step"
        :class="{ active: currentStep === 3, completed: currentStep > 3 }"
        @click="handleStepClick(3)"
      >
        <div class="step-icon-wrapper">
          <!-- Icono completado (checkmark) -->
          <svg
            v-if="currentStep > 3"
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle cx="20" cy="20" r="19" fill="#1284ED" stroke="#1284ED" stroke-width="2" />
            <path
              d="M16 20L18.5 22.5L24 17"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <!-- Icono normal -->
          <svg
            v-else
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <rect
              x="1"
              y="1"
              width="38"
              height="38"
              rx="19"
              fill="white"
              :stroke="currentStep === 3 ? '#575B5F' : '#BABCBD'"
              stroke-width="2"
            />
            <path
              d="M20.0001 8C20.8298 8 21.5001 8.67172 21.5001 9.5V11.1725C21.5751 11.1823 21.6454 11.1931 21.7204 11.2039C22.2173 11.278 23.6986 11.5166 24.3079 11.6708C25.0673 11.8742 25.597 12.6922 25.3907 13.4937C25.1892 14.2953 24.3736 14.7828 23.5673 14.5812C23.1314 14.4266 21.8048 14.2484 21.2798 14.1734C19.7751 13.9484 18.4861 14.0609 17.6001 14.3984C16.7433 14.7266 16.3875 15.1906 16.2933 15.7109C16.2023 16.2125 16.2712 16.4984 16.3537 16.6766C16.4423 16.8641 16.6153 17.075 16.9598 17.3047C17.722 17.8062 18.8845 18.1438 20.3939 18.5516L20.5251 18.5891C21.8564 18.95 23.4876 19.3953 24.697 20.1969C25.3579 20.6375 25.9814 21.2328 26.3657 22.0578C26.7548 22.8922 26.8392 23.8297 26.6611 24.7859C26.3329 26.6094 25.0673 27.8 23.5814 28.4188C22.9439 28.6812 22.2407 28.85 21.5001 28.9344V30.5C21.5001 31.3297 20.8298 32 20.0001 32C19.1704 32 18.5001 31.3297 18.5001 30.5V28.8641C18.4814 28.8641 18.4579 28.8172 18.4392 28.8547H18.4298C17.2908 28.6766 15.4097 28.1844 14.1408 27.6219C13.3837 27.2844 13.0429 26.3984 13.3795 25.6391C13.7161 24.8844 14.6025 24.5422 15.3173 24.8797C16.3392 25.3156 17.9517 25.7422 18.8845 25.8875C20.3845 26.1125 21.6173 25.9812 22.4517 25.6437C23.2439 25.3203 23.6048 24.8516 23.7079 24.2891C23.797 23.7875 23.7314 23.5016 23.647 23.3234C23.5579 23.1359 23.3845 22.925 23.0423 22.6953C22.2782 22.1938 21.1157 21.8562 19.6064 21.4484L19.4751 21.4109C18.1439 21.05 16.5117 20.6047 15.3014 19.8031C14.64 19.3625 14.0203 18.7672 13.6354 17.9422C13.245 17.1078 13.1601 16.1703 13.3415 15.1719C13.6734 13.3484 14.9868 12.1855 16.5295 11.5967C17.1398 11.3638 17.8017 11.2105 18.5001 11.128V9.5C18.5001 8.67172 19.1704 8 20.0001 8Z"
              :fill="currentStep === 3 ? '#575B5F' : '#BABCBD'"
            />
          </svg>
        </div>
        <span class="step-title">Montos</span>
      </div>

      <!-- Step 4 - Cupos del servicio -->
      <div
        class="stepper-step"
        :class="{ active: currentStep === 4, completed: currentStep > 4 }"
        @click="handleStepClick(4)"
      >
        <div class="step-icon-wrapper">
          <!-- Icono completado (checkmark) -->
          <svg
            v-if="currentStep > 4"
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle cx="20" cy="20" r="19" fill="#1284ED" stroke="#1284ED" stroke-width="2" />
            <path
              d="M16 20L18.5 22.5L24 17"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <!-- Icono normal -->
          <svg
            v-else
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <rect
              x="1"
              y="1"
              width="38"
              height="38"
              rx="19"
              fill="white"
              :stroke="currentStep === 4 ? '#575B5F' : '#BABCBD'"
              stroke-width="2"
            />
            <g clip-path="url(#clip0_stepper_step4)">
              <path
                d="M29.8705 23.8719L29.2112 22.3719C28.9777 21.8422 28.4687 21.5 27.9107 21.5H12.0875C11.5304 21.5 11.0241 21.8422 10.7902 22.3719L10.1308 23.8719C9.9271 24.3362 9.96268 24.8783 10.225 25.3091C10.4872 25.7398 10.9415 26 11.3884 26L11.3884 31.25C11.3884 31.6642 11.7083 32 12.1027 32H13.5313C13.9258 32 14.2456 31.6642 14.2456 31.25V26H25.6741V31.25C25.6741 31.6642 25.994 32 26.3884 32H27.7768C28.1714 32 28.4911 31.6642 28.4911 31.25L28.5714 26C29.0585 26 29.5125 25.7393 29.7745 25.3086C30.0366 24.8779 30.0714 24.3359 29.8705 23.8719ZM14.9598 14C14.9598 12.6181 15.683 11.4641 16.7455 10.7717V20H18.8884L18.8888 10.25H21.0317L21.0312 20H23.1741L23.1745 10.7703C24.2768 11.4219 24.9598 12.6181 24.9598 14L24.9594 20H27.1036L27.103 14C27.103 10.6864 24.5446 8 21.3888 8H18.5312C15.4152 8 12.8563 10.6864 12.8563 14L12.857 20H14.9985L14.9598 14Z"
                :fill="currentStep === 4 ? '#575B5F' : '#BABCBD'"
              />
            </g>
            <defs>
              <clipPath id="clip0_stepper_step4">
                <rect width="20" height="24" fill="white" transform="translate(10 8)" />
              </clipPath>
            </defs>
          </svg>
        </div>
        <span class="step-title">Cupos del servicio</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  defineOptions({
    name: 'PricingPlanStepper',
  });

  interface Props {
    currentStep: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    currentStep: 1,
  });

  const emit = defineEmits<{
    (e: 'step-click', step: number): void;
  }>();

  const handleStepClick = (step: number) => {
    // Solo permitir navegar a steps anteriores o al actual
    if (step <= props.currentStep) {
      emit('step-click', step);
    }
  };

  // Computed para calcular el ancho de la línea de progreso
  import { computed } from 'vue';

  const progressLineStyle = computed(() => {
    // Calcular el porcentaje de progreso basado en el step actual
    // La línea de fondo va de left: 60px a right: 60px (total: 100% - 120px)
    // Step 1 = 0%, Step 2 = 33.33%, Step 3 = 66.66%, Step 4 = 100%
    const totalSteps = 4;
    // Si estamos en el paso 5 (Resumen), mostramos progreso completo
    const effectiveStep = props.currentStep > 4 ? 4 : props.currentStep;
    const progress = (effectiveStep - 1) / (totalSteps - 1);
    const progressPercent = progress * 100;
    const offsetPx = progress * 120; // Compensar los 60px de cada lado
    return {
      width: `calc(${progressPercent}% - ${offsetPx}px)`,
    };
  });
</script>

<style lang="scss" scoped>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  .stepper-container {
    margin-bottom: 32px;
    display: flex;
    justify-content: center;
  }

  /* Stepper Personalizado */
  .custom-stepper {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    width: 100%;
    max-width: 1000px;
    position: relative;
  }

  /* Línea de fondo única - atraviesa todo el stepper */
  .stepper-line-background {
    position: absolute;
    top: 19px; /* Centro del icono de 40px, ajustado para grosor de 3px */
    left: 60px; /* Centro del primer icono (120px min-width / 2) */
    right: 60px; /* Centro del último icono */
    height: 3px;
    background-color: #d9d9d9;
    z-index: 0;
  }

  /* Línea de progreso azul */
  .stepper-line-progress {
    position: absolute;
    top: 19px;
    left: 60px;
    height: 3px;
    background-color: #1284ed;
    z-index: 1;
    transition: width 0.3s ease;
  }

  .stepper-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 120px;
    position: relative;
    z-index: 1; /* Encima de la línea */
    cursor: pointer;

    &:hover .step-title {
      color: #575b5f;
      user-select: none;
    }
  }

  .step-icon-wrapper {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2; /* El icono tapa la línea */
  }

  .step-title {
    margin-top: 8px;
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    line-height: 20px;
    color: #8c8c8c;
    text-align: center;
    white-space: nowrap;
  }

  .stepper-step.active .step-title {
    color: #000000e0;
    font-weight: 500;
  }

  .stepper-step.completed .step-title {
    color: #1284ed;
    font-weight: 500;
  }
</style>
