import { defineStore } from 'pinia';
import { reactive } from 'vue';

interface ButtonsState {
  btnNext: boolean;
  btnBack: boolean;
  btnSend: boolean;
}

export const useButtonStore = defineStore('buttonStore', {
  state: (): { buttons: ButtonsState } => ({
    buttons: reactive({
      btnNext: true,
      btnBack: true,
      btnSend: true,
    }),
  }),
  actions: {
    setButtonState(button: keyof ButtonsState, state: boolean) {
      if (button in this.buttons) {
        this.buttons[button] = state;
      }
    },
    enableAll() {
      Object.keys(this.buttons).forEach((key) => {
        this.buttons[key as keyof ButtonsState] = false;
      });
    },
    disableAll() {
      Object.keys(this.buttons).forEach((key) => {
        this.buttons[key as keyof ButtonsState] = true;
      });
    },
  },
  getters: {
    isButtonDisabled:
      (state) =>
      (button: keyof ButtonsState): boolean =>
        state.buttons[button],
  },
});
