<template>
  <div :class="[box ? 'default' : 'not-box']">
    <a-form-item :name="name" :label="label" required>
      <template #label>
        <span class="custom-label"> {{ label }}</span>
      </template>
      <div class="custom-select" :class="{ 'is-active': isOpen }">
        <div :class="['select-header', !box ? 'border-0 p-0' : '']" @click="toggleDropdown">
          <span :class="['selected-rooms', !box ? 'text-700' : '']" v-if="!hasSelection"
            >0 SGL 0 DBL 0 TPL</span
          >
          <span v-bind:class="['selected-rooms', !box ? 'text-700' : '']" v-else>{{
            formatSelectedRooms()
          }}</span>
          <template v-if="editable">
            <template v-if="!box">
              <span
                style="
                  margin-left: 11px;
                  line-height: 21px;
                  vertical-align: middle;
                  cursor: pointer;
                "
                ><svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="17"
                  height="17"
                  fill="none"
                  stroke="#575757"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  class="feather feather-edit"
                  viewBox="0 0 24 24"
                >
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg
              ></span>
            </template>
            <DownOutlined :class="{ rotated: isOpen }" v-if="box" />
          </template>
        </div>
        <div v-if="isOpen" class="select-dropdown">
          <div class="room-types">
            <div v-for="(type, index) in roomTypes" :key="type.key" class="room-type">
              <a-checkbox v-model:checked="type.checked" @change="onCheckboxChange(type.key)">
                {{ type.label }}
              </a-checkbox>
              <div class="input-group">
                <a-input
                  :id="`room-${type.key}-${index}`"
                  :name="`room-${type.key}-${index}`"
                  v-model:value="accommodationModel[type.key]"
                  :disabled="!type.checked"
                  @input="onInputChange(type.key, $event)"
                  @blur="validateInput(type.key)"
                  @keypress="onKeyPress"
                />
                <div class="room-controls">
                  <a-button @click="incrementRoom(type.key)" :disabled="!type.checked">
                    <font-awesome-icon :icon="['fas', 'chevron-up']" />
                  </a-button>
                  <a-button @click="decrementRoom(type.key)" :disabled="!type.checked">
                    <font-awesome-icon :icon="['fas', 'chevron-down']" />
                  </a-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </a-form-item>
  </div>
</template>

<script>
  import { computed, defineComponent, onMounted, onUnmounted, ref, watch } from 'vue';
  import { DownOutlined } from '@ant-design/icons-vue';

  export default defineComponent({
    name: 'BaseSelectAccommodations',
    components: {
      DownOutlined,
    },
    props: {
      modelValue: {
        type: Object,
        required: true,
      },
      label: {
        type: String,
        default: '',
      },
      name: {
        type: String,
        default: '',
      },
      box: {
        type: Boolean,
        default: true,
      },
      editable: {
        type: Boolean,
        default: true,
      },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
      const isOpen = ref(false);
      const accommodationModel = ref({ ...props.modelValue });

      const roomTypes = ref([
        { key: 'SGL', label: 'Simple', checked: false },
        { key: 'DBL', label: 'Doble', checked: false },
        { key: 'TPL', label: 'Triple', checked: false },
      ]);

      const hasSelection = computed(() => {
        return Object.values(accommodationModel.value).some((value) => parseInt(value) > 0);
      });

      const updateAccommodation = () => {
        emit('update:modelValue', { ...accommodationModel.value });
      };

      const onCheckboxChange = (key) => {
        const type = roomTypes.value.find((t) => t.key === key);
        if (type.checked) {
          accommodationModel.value[key] = '1';
        } else {
          accommodationModel.value[key] = '0';
        }
        updateAccommodation();
      };

      const onInputChange = (key, event) => {
        accommodationModel.value[key] = event.target.value.replace(/\D/g, '');
        validateInput(key);
      };

      const validateInput = (key) => {
        let value = parseInt(accommodationModel.value[key], 10);
        if (isNaN(value) || value < 0) {
          value = 0;
        }
        accommodationModel.value[key] = value.toString();
        const type = roomTypes.value.find((t) => t.key === key);
        if (type) {
          type.checked = value > 0;
        }
        updateAccommodation();
      };

      const incrementRoom = (key) => {
        let value = parseInt(accommodationModel.value[key], 10) || 0;
        accommodationModel.value[key] = (value + 1).toString();
        validateInput(key);
      };

      const decrementRoom = (key) => {
        let value = parseInt(accommodationModel.value[key], 10) || 0;
        if (value > 0) {
          accommodationModel.value[key] = (value - 1).toString();
          validateInput(key);
        }
      };

      const formatSelectedRooms = () => {
        return roomTypes.value
          .map(
            (type) =>
              `${accommodationModel.value[type.key] != undefined ? accommodationModel.value[type.key] : 0} ${type.key}`
          )
          .join(' ');
      };

      const toggleDropdown = () => {
        if (props.editable) {
          isOpen.value = !isOpen.value;
          if (isOpen.value) {
            updateCheckboxStates();
          }
        }
      };

      const closeDropdown = (event) => {
        if (!event.target.closest('.custom-select')) {
          isOpen.value = false;
        }
      };

      const updateCheckboxStates = () => {
        roomTypes.value.forEach((type) => {
          type.checked = parseInt(accommodationModel.value[type.key], 10) > 0;
        });
      };

      const onKeyPress = (event) => {
        const keyCode = event.keyCode ? event.keyCode : event.which;
        if (keyCode < 48 || keyCode > 57) {
          event.preventDefault();
        }
      };

      watch(
        () => props.modelValue,
        (newValue) => {
          accommodationModel.value = { ...newValue };
          updateCheckboxStates();
        },
        { deep: true }
      );

      onMounted(() => {
        document.addEventListener('click', closeDropdown);
        updateCheckboxStates();
      });

      onUnmounted(() => {
        document.removeEventListener('click', closeDropdown);
      });

      return {
        isOpen,
        accommodationModel,
        roomTypes,
        hasSelection,
        onCheckboxChange,
        onInputChange,
        onKeyPress,
        validateInput,
        incrementRoom,
        decrementRoom,
        formatSelectedRooms,
        toggleDropdown,
      };
    },
  });
</script>

<style lang="scss">
  .not-box {
    .ant-form-item {
      margin: 0 !important;

      &-control-input {
        height: auto !important;
        min-height: 10px !important;
      }
    }

    .text-700 {
      color: #575757 !important;
    }
  }

  .custom-select {
    position: relative;
    width: 100%;
    font-family: Arial, sans-serif;
  }

  .h-auto {
    height: auto !important;
    min-height: auto !important;
  }

  .select-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    border: 1px solid #c4c4c4;
    border-radius: 4px;
    cursor: pointer;
    background-color: #fff;
    height: 45px;
  }

  .border-0 {
    border: 0 !important;
    height: auto !important;
    justify-content: start !important;
  }

  .select-header span {
    font-family: Montserrat, serif;
    font-size: 14px;
    font-weight: 500;
    color: #2f353a;
  }

  .select-header span + span {
    font-size: 12px;
    color: rgba(0, 0, 0, 0.25);
  }

  .select-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background-color: #fff;
    border: 1px solid #d9d9d9;
    border-top: none;
    border-radius: 0 0 4px 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    padding: 0 10px 0 10px;
    min-width: 210px;
  }

  .room-types {
    padding: 8px;
  }

  .room-type {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;

    label {
      font-family: Montserrat, serif;
      font-size: 15px;
      font-weight: 500;
      color: #212529;
    }
  }

  .room-type:last-child {
    margin-bottom: 0;
  }

  .input-group {
    display: flex;
    align-items: center;
  }

  .input-group .ant-input {
    width: 50px;
    height: 35px;
    margin: 0 5px;
  }

  .input-group .ant-btn {
    min-width: 32px;
    padding: 0 8px;
  }

  .rotated {
    transform: rotate(180deg);
  }

  .is-active .select-header {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
  }

  .room-controls {
    display: flex;
    flex-direction: column;

    button {
      background: none;
      border: none;
      cursor: pointer;
      color: #eb5757;
      box-shadow: none;
      width: 20px;
      height: 20px;

      &:hover {
        color: #eb5757;
      }
    }
  }

  .default .ant-form-item {
    margin-bottom: 2px;
  }

  .ant-form-item-required:not(.ant-form-item-required-mark-optional)::before {
    display: none;
  }
</style>
