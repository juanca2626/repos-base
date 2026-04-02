<template>
  <div class="text-center p-5">
    <loading :active="loading" :is-full-page="false" />
    <div v-if="!loading">
      <h4>Redirigiendo a Mesa de Ayuda...</h4>
      <p v-if="error" class="text-danger">{{ error }}</p>
    </div>
  </div>
</template>

<script>
import { API } from "./../api";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";

export default {
  components: { Loading },
  data() {
    return {
      loading: true,
      error: null,
    };
  },
  async mounted() {
    try {
      // 1️⃣ obtener usuario actual
      const { data: user } = await API.get("/user");

      // 2️⃣ solicitar la URL firmada desde el backend
      const { data } = await API.post("/sso/generate-url", user);

      // 3️⃣ redirigir
      if (data?.url) {
        window.location.href = data.url;
      } else {
        this.error = "No se pudo generar la URL de acceso.";
      }
    } catch (e) {
      console.error(e);
      this.error = "Ocurrió un error al redirigir.";
    } finally {
      this.loading = false;
    }
  },
};
</script>

<style scoped>
.text-center {
  text-align: center;
}
.p-5 {
  padding: 3rem;
}
</style>
