<template>
  <a-form-item :name="name" :label="label" required>
    <div class="passengers-input">
      <span class="passengers-icon">
        <UserOutlined />
      </span>
      <div class="passengers-group">
        <div v-for="(type, index) in passengerTypes" :key="type" class="passenger-input">
          <span class="passenger-label">{{ type }}</span>
          <a-input
            :id="`passenger-${type}-${index}`"
            :name="`passenger-${type}-${index}`"
            class="passenger-value"
            :value="modelValue[type]"
            @input="(event) => handleInput(type, event)"
            @keypress="validateNumberInput"
            @paste="handlePaste"
            @blur="(event) => validateInput(type, event.target.value)"
          />
          <div class="passenger-controls">
            <a-button @click="() => increment(type)" class="passenger-button">
              <template #icon>
                <font-awesome-icon :icon="['fas', 'chevron-up']" />
              </template>
            </a-button>
            <a-button @click="() => decrement(type)" class="passenger-button">
              <template #icon>
                <font-awesome-icon :icon="['fas', 'chevron-down']" />
              </template>
            </a-button>
          </div>
        </div>
      </div>
    </div>
  </a-form-item>
</template>

<script>
  import { defineComponent } from 'vue';
  import { UserOutlined } from '@ant-design/icons-vue';

  export default defineComponent({
    name: 'BaseInputPassengers',
    components: {
      UserOutlined,
    },
    props: {
      adultsMax: {
        type: [String, Number],
        default: 100,
      },
      childrenMax: {
        type: [String, Number],
        default: 100,
      },
      name: {
        type: String,
        default: '',
      },
      label: {
        type: String,
        default: '',
      },
      modelValue: {
        type: Object,
        required: true,
        default: () => ({
          ADL: 1,
          CHD: 0,
        }),
      },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
      const passengerTypes = ['ADL', 'CHD'];

      const updatePassenger = (type, value) => {
        if (value === '') {
          emit('update:modelValue', { ...props.modelValue, [type]: value });
          return;
        }

        let numValue = parseInt(value, 10);

        if (isNaN(numValue)) {
          numValue = type === 'ADL' ? 1 : 0;
        }

        if (!isNaN(numValue)) {
          let newValue = type === 'ADL' ? Math.max(1, numValue) : Math.max(0, numValue);

          if (type === 'ADL' && !isNaN(props.adultsMax)) {
            newValue =
              props.adultsMax >= newValue ? parseInt(newValue, 10) : parseInt(props.adultsMax, 10);
          }

          if (type === 'CHD' && !isNaN(props.adultsMax)) {
            newValue =
              props.childrenMax >= newValue
                ? parseInt(newValue, 10)
                : parseInt(props.childrenMax, 10);
          }

          emit('update:modelValue', { ...props.modelValue, [type]: newValue });
        }
      };

      const validateInput = (type, value) => {
        const numValue = parseInt(value, 10);
        if (isNaN(numValue) || value === '') {
          emit('update:modelValue', { ...props.modelValue, [type]: type === 'ADL' ? 1 : 0 });
        } else {
          updatePassenger(type, numValue);
        }
      };

      const increment = (type) => {
        const currentValue = parseInt(props.modelValue[type], 10) || 0;
        const maxValue = type === 'ADL' ? props.adultsMax : props.childrenMax;

        if (currentValue < parseInt(maxValue, 10)) {
          updatePassenger(type, currentValue + 1);
        }
      };

      const decrement = (type) => {
        const currentValue = parseInt(props.modelValue[type], 10) || 0;
        const minValue = type === 'ADL' ? 1 : 0;

        if (currentValue > minValue) {
          updatePassenger(type, currentValue - 1);
        }
      };

      // Validar teclas presionadas - solo permite números
      const validateNumberInput = (event) => {
        const keyCode = event.keyCode || event.which;
        const keyValue = String.fromCharCode(keyCode);
        const regex = /^[0-9]+$/;

        if (!regex.test(keyValue)) {
          event.preventDefault();
        }
      };

      // Validar el pegado de texto - solo permite números
      const handlePaste = (event) => {
        const pastedText = (event.clipboardData || window.clipboardData).getData('text');
        if (!/^\d+$/.test(pastedText)) {
          event.preventDefault();
        }
      };

      // Manejar la entrada limpiando cualquier carácter no numérico
      const handleInput = (type, event) => {
        const value = event.target.value.replace(/[^\d]/g, '');
        updatePassenger(type, value);
      };

      return {
        passengerTypes,
        updatePassenger,
        validateInput,
        increment,
        decrement,
        validateNumberInput,
        handlePaste,
        handleInput,
      };
    },
  });
</script>

<style scoped>
  .passengers-input {
    display: flex;
    align-items: center;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    overflow: hidden;
    height: 45px;
    font-family: 'Montserrat', sans-serif;
  }

  .passengers-icon {
    color: #bbbdbf;
    padding: 0 10px;
    background-color: #ffffff;
    height: 100%;
    display: flex;
    align-items: center;
    font-size: 1.2rem;
  }

  .passengers-group {
    display: flex;
    flex-grow: 1;
  }

  .passenger-input {
    display: flex;
    align-items: center;
  }

  .passenger-label {
    font-size: 14px;
    font-weight: 400;
    color: #373737;
  }

  .passenger-value {
    width: 26px;
    text-align: center;
    border: 0;
    padding: 0;
    border-radius: 0;
    color: #c4c4c4;
  }

  .passenger-controls {
    display: flex;
    flex-direction: column;

    button {
      background: none;
      border: none;
      cursor: pointer;
      color: #eb5757;
      box-shadow: none;
      width: 25px;

      &:hover {
        color: #eb5757;
      }
    }
  }

  .passenger-button {
    padding: 0 4px;
    min-width: auto;
    height: auto;
  }
</style>
