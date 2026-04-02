<template>
  <a-card title="Listado de guías" :bordered="false" class="guide-list">
    <a-collapse v-for="(guide, index) in guides" :key="index" style="margin: 10px 0">
      <a-collapse-panel :key="index" :header="guide.provider.fullname">
        <p>
          <strong>
            {{ guide.provider.code }}
            <a-tag v-for="(tag, i) in guide.provider.profiles" :key="i">{{ tag }}</a-tag>
          </strong>
        </p>
        <p>📞 Teléfono: -</p>
        <p>📧 Correo: -</p>
      </a-collapse-panel>
    </a-collapse>
  </a-card>
</template>

<script setup lang="ts">
  import { defineProps } from 'vue';

  //   interface Guide {
  //     name: string;
  //     code: string;
  //     tags: string[];
  //     phone: string;
  //     email: string;
  //   }

  // 🔹 Propiedad que recibe la lista de guías
  defineProps<{
    guides: any[];
  }>();
</script>

<style scoped>
  .guide-list {
    background-color: #ffffff;
  }
</style>
