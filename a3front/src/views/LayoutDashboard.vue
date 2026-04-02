<template>
  <!-- TODO: Add Header component(s) -->
  <header-component></header-component>
  <div class="container-fluid">
    <div class="row">
      <!-- TODO: Add Navigation component(s) -->
      <navigation-component class="navigation-component"></navigation-component>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-3 py-3">
        <!-- TODO: Add Breadcrumb component(s) -->
        <slot name="breadcrumb"></slot>

        <div class="dashboard-container">
          <div class="container-header">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
              <slot name="forHeader"></slot>
            </div>
          </div>
          <div class="container-main">
            <slot name="forContent"></slot>
          </div>

          <div class="modals-main">
            <slot name="modalInfo"></slot>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
<script setup>
  //TODO: Register Component locally

  //TODO: Register Methods
  //For Breadcrumb
  // const selected = function (crumb) {
  //   return console.log(crumb)
  // }
</script>
