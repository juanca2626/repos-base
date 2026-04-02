<template>
    <div class="row">
        <div class="col-md-12">
            Dashboard
        </div>
    </div>
</template>

<script>


  export default {

    mounted () {

       if(this.$can('read', 'packages')) {
           window.location = '#/packages';
       }else {
           if (this.$can('read', 'hotels')) {
               window.location = '#/hotels';
           }else{
               if (this.$can('read', 'services')) {
                   window.location = '#/services_new';
               }
           }
       }
    }
 }






</script>

<style lang="stylus">

</style>
