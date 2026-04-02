<template>
    <div>
       {{ client_id }}
    </div>
</template>

<script>

    export default {
        props:['client_id'],
        data() {
            return {

            }
        },
        mounted() {
            console.log('componente montado')
        }
    }
</script>