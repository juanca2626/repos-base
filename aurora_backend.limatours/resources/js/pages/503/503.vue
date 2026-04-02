<template>
    <div class="app flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <b-jumbotron header="404: Página no Encontrada" lead="Ups... parece que llegaste a un limbo">
                        <p>Nos apena mucho informarte que esta página aún no esta lista.</p>
                        <router-link :to="'/'">
                            <b-button variant="primary">Regresar</b-button>
                        </router-link>
                    </b-jumbotron>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import BJumbotron from 'bootstrap-vue/es/components/jumbotron/jumbotron'
  import BButton from 'bootstrap-vue/es/components/button/button'

  export default {
    components: {
      BJumbotron: BJumbotron,
      BButton: BButton
    }
  }
</script>


