<template>
    <div class="loading-container" style="display: none;">
        <div :class="'loading-backdrop'+typeBack"></div>
        <div id="spinner-loader">
        	<div class="spinner"><span class="logo"></span></div>
        	<div :class="'spinner-text customColor'+typeBack">{{ text1 }}<small>{{ text2 }}</small></div>
        </div>
    </div>
</template>


<script>

    export default {
        data: () => {
            return {
                lang: '',
                text1:"Please wait",
                text2:"Loading",
                typeBack:""
            }
        },
        created: function () {
            this.lang = localStorage.getItem('lang')
            this.$root.$on('loadingPage',(data)=>{
                if( data.typeBack ) {
                    if( data.typeBack === 1 ) {
                      this.typeBack = ""
                    } else {
                        this.typeBack = data.typeBack
                    }
                }
                if( data.message ) {
                    this.text2 = data.message
                    this.typeBack = 2
                }
            })
        },
        mounted: function() {
        },
        computed: {
        },
        methods: {
            toggleView: function () {
            }
        }
    }
</script>
<style>
  .loading-backdrop2{
    background: rgba(75, 75, 75, 0.87) !important;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    z-index: 9998;
  }
  .customColor2{
    color: #FFFFFF !important;
  }
</style>
