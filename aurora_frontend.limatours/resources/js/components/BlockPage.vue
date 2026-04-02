<template>
    <block-u-i :message="msg" v-show="blockPage">
        <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
    </block-u-i>
</template>
<script>
  export default {
    data: () => {
      return {
        msg:'Por Favor Espere',
        blockPage:false
      }
    },
    created () {
      this.$root.$on('blockPage',(data)=>{
        this.blockPage = true
        this.msg = data.message
      })
      this.$root.$on('unlockPage',()=>{
        this.blockPage = false
      })
    }

  }
</script>

<style scoped>

</style>
