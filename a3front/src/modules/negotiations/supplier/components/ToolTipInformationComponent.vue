<template>
  <a-tooltip
    :overlayStyle="{ maxWidth: '280px', padding: 0 }"
    :overlayInnerStyle="{ backgroundColor: '#ffffff' }"
    :color="'#ffffff'"
    :mouseEnterDelay="0.5"
    placement="bottom"
  >
    <template #title>
      <div class="tooltip-custom">
        <div class="tooltip-header">{{ title }}</div>
        <div class="tooltip-content">
          <slot name="content"></slot>
        </div>
        <div class="tooltip-footer">
          <slot name="footer"></slot>
        </div>
      </div>
    </template>
    <slot></slot>
  </a-tooltip>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';

  export default defineComponent({
    name: 'ToolTipInformationComponent',
    props: {
      title: {
        type: String,
        required: true,
      },
    },
  });
</script>

<style scoped>
  .tooltip-custom {
    background-color: white;
    border-radius: 4px;
    overflow: hidden;
  }
  .tooltip-header {
    background-color: #ffffff;
    padding: 8px 10px;
    font-weight: bold;
    color: #2f353a;
  }
  .tooltip-content {
    margin-left: 10px;
    margin-right: 10px;
    color: #2f353a;
  }
  .tooltip-footer {
    background-color: #ffffff;
    padding: 5px 0px 5px 0px;
    border-top: 1px solid #e8e8e8;
    font-size: 0.9em;
    color: #888;
    margin-left: 10px;
    margin-right: 10px;
  }
</style>
