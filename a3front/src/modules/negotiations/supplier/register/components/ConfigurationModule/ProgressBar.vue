<template>
  <div class="progress-container-module-config">
    <div class="progress-grid">
      <div class="icon-container">
        <font-awesome-icon :icon="['fas', 'business-time']" />
      </div>
      <div class="progress-info">
        <div class="progress-text">0% Completado</div>
        <div>
          <a-progress :percent="0" stroke-color="#1284ED" type="line" :show-info="false" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts"></script>

<style scoped>
  .progress-container-module-config {
    width: 100%;

    .progress-grid {
      width: 100%;
      display: grid;
      grid-template-columns: 3rem auto;
      grid-gap: 10px;
      align-items: center;
    }

    .icon-container {
      color: #1284ed;
      background: #f1f8ff;
      border: 2px solid #bfd9ef;
      border-radius: 50px;
      height: 3rem;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
    }

    .progress-info {
      width: 73%;
    }

    .progress-text {
      font-weight: 600;
      font-size: 14px;
      color: #2f353a;
      text-align: justify;
    }
  }
</style>
