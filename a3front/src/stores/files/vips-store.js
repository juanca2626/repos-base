import { defineStore } from 'pinia';
import {
  fetchVips,
  createVipRelated,
  addVipAndFileRelated,
  changeVipAndFileRelated,
} from '@service/files';

import { createVipAdapter } from '@store/files/adapters';
import { removeVipAndFileRelatedService } from '../../services/files/vips';

export const useVipsStore = defineStore({
  id: 'vips',
  state: () => ({
    loading: false,
    vips: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getCustomVips: (state) => {
      const EMPTY_VIP = { label: '', value: '' };
      const copyVips = state.vips.map((vip) => ({
        label: vip.name,
        value: vip.id,
      }));
      copyVips.splice(0, 0, EMPTY_VIP);
      return copyVips;
    },
    getVips: (state) => state.vips,
  },
  actions: {
    inited() {
      this.loading = true;
    },
    fetchAll() {
      this.loading = true;
      return fetchVips()
        .then(({ data }) => {
          this.vips = data.data.map((v) => createVipAdapter(v));
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    createVipRelated({ fileId, vipName }) {
      this.loading = true;
      return createVipRelated({ fileId, vipName })
        .then(({ data }) => {
          console.log('createVipRElated', { data });
          this.loading = false;
          return data;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    addVipAndFileRelated({ vipId, fileId }) {
      this.loading = true;
      return addVipAndFileRelated({ fileId, vipId })
        .then(({ data }) => {
          this.loading = false;
          return data;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    changeVipAndFileRelated({ fileId, vipId }) {
      this.loading = true;
      return changeVipAndFileRelated({ fileId, vipId })
        .then((data) => {
          this.loading = false;
          return data;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    removeVipAndFileRelated({ fileId, vipId }) {
      this.loading = true;
      return removeVipAndFileRelatedService({ fileId, vipId })
        .then((data) => {
          this.loading = false;
          return data;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
