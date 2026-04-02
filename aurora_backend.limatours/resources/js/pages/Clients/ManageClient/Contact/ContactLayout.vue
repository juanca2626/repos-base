<template>
    <div class="animated fadeIn">
        <list/>
    </div>
</template>

<script>
  import List from './List'

  export default {
    components: {
      'list': List
    },
    data: () => {
      return {}
    },
    mounted () {
    },
    computed: {},
    created () {

    },
    methods: {}
  }
</script>

<style lang="stylus">
    .marl {
        margin-left: 800px;
    }
</style>


