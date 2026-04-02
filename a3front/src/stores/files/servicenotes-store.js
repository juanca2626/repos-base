import { defineStore } from 'pinia';
import {
  fetchServiceNotes,
  create,
  update,
  remove,
  getClassification,
  fetchServiceNoteGeneral,
  createNoteGeneral,
  updateNoteGeneral,
  listNote,
  createNote,
  updateNote,
  deleteNote,
  findExternalHousing,
  listExternalHousing,
  createExternalHousing,
  updateExternalHousing,
  deleteExternalHousing,
  fetchAllFileNotes,
  fetchAllRequirementFileNotes,
} from '@service/files';
// import { createStatusAdapter } from '@store/files/adapters'

export const useServiceNotesStore = defineStore({
  id: 'serviceNotes',
  state: () => ({
    loading: false,
    loadingSaveOrUpdate: false,
    loadingNote: false,
    loadingNoteRequirement: false,
    loadingSaveOrUpdateNote: false,
    loadingExternalHousing: false,
    loadingSaveOrUpdateExternalHousing: false,
    serviceNotes: [],
    serviceNoteGeneral: [],
    classifications: [],
    countNotes: 0,
    external_housing: [],
    file_note_all: [],
    file_note_all_requirement: [],
    flag_change: false,
  }),
  getters: {
    isLoading: (state) => state.loading,
    isLoadingNote: (state) => state.loadingNote,
    isLoadingNoteRequirement: (state) => state.loadingNoteRequirement,
    isloadingSaveOrUpdate: (state) => state.loadingSaveOrUpdate,
    isloadingSaveOrUpdateNote: (state) => state.loadingSaveOrUpdateNote,
    isLoadingExternalHousing: (state) => state.loadingExternalHousing,
    isLoadingSaveOrUpdateExternalHousing: (state) => state.loadingSaveOrUpdateExternalHousing,
    getServiceNotes: (state) => state.serviceNotes,
    getCountServiceNotes: (state) => state.countNotes,
    getClassifications: (state) => state.classifications,
    getServiceNoteGeneral: (state) => state.serviceNoteGeneral,
    hasEvent: (state) => {
      return !!(
        state.serviceNoteGeneral.date_event &&
        state.serviceNoteGeneral.date_event !== '' &&
        state.serviceNoteGeneral.type_event &&
        state.serviceNoteGeneral.type_event !== '' &&
        state.serviceNoteGeneral.description_event &&
        state.serviceNoteGeneral.description_event !== ''
      );
    },
    hasPagingBoard: (state) => {
      return !!(state.serviceNoteGeneral.image_logo && state.serviceNoteGeneral.image_logo !== '');
    },
    getExternalHousing: (state) => state.external_housing,
    getAllFileNote: (state) => state.file_note_all,
    getAllRequirementFileNote: (state) => state.file_note_all_requirement,
  },
  actions: {
    fetchAll({ id, file_id }) {
      this.loading = true;
      // console.log("Aqui el file id :"+file_id);
      return fetchServiceNotes({ id, file_id })
        .then(({ data }) => {
          // this.serviceNotes = data.data.map(d => createStatusAdapter(d))
          this.serviceNotes = data.data;
          this.loading = false;
          this.countNotes = data.data.length;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    create({ id, file_id, data }) {
      return create({ id, file_id, data })
        .then(async (response) => {
          console.log(response.data);
          console.log('AQUI EMPIEZA EL CREATE');
          this.countNotes++;
          await this.fetchAll({ id, file_id });
          this.loading = false;
          if (data.type_note === 'INFORMATIVE') {
            await this.fetchAllFileNotes({ file_id });
          } else {
            await this.fetchAllRequirementFileNotes({ file_id });
          }
          console.log('AQUI ACABO EL CREATE');
          return response.data;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    update({ note_id, itinerary_id, file_id, data }) {
      return update({ note_id, itinerary_id, file_id, data })
        .then(async (response) => {
          await this.fetchAll({ id: itinerary_id, file_id: file_id });
          this.loading = false;
          if (data.type_note === 'INFORMATIVE') {
            await this.fetchAllFileNotes({ file_id });
          } else {
            await this.fetchAllRequirementFileNotes({ file_id });
          }
          return response.data;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    remove({ note_id, file_id, data }) {
      return remove({ note_id, file_id, data })
        .then(async ({ data }) => {
          console.log(data);
          this.loading = false;
          await this.fetchAllFileNotes({ file_id });
          await this.fetchAllRequirementFileNotes({ file_id });
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    getClassification() {
      return getClassification()
        .then(({ data }) => {
          this.classifications = data.data;
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async fetchServiceNoteGeneral({ file_id }) {
      this.loading = true;
      return fetchServiceNoteGeneral({ file_id })
        .then(({ data }) => {
          this.serviceNoteGeneral = data.data;
          this.countNotes = data.data.length || 0;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    createNoteGeneral({ file_id, data }) {
      this.loadingSaveOrUpdate = true;
      return createNoteGeneral({ file_id, data })
        .then(({ data }) => {
          console.log(data);
          return data;
        })
        .catch((error) => {
          console.log(error);
          this.loadingSaveOrUpdate = false;
        })
        .finally(() => {
          this.loadingSaveOrUpdate = false;
        });
    },
    async editNoteGeneral({ file_id, data, recharge }) {
      if (recharge) {
        await this.fetchServiceNoteGeneral({ file_id });
      }

      if (data) {
        this.serviceNoteGeneral = {
          ...this.serviceNoteGeneral,
          isCreatedEvent: data.isEvent,
          isCreatedImage: data.isImage,
        };
      }

      this.flag_change = true;
    },
    updateNoteGeneral({ note_id, file_id, data }) {
      this.loadingSaveOrUpdate = true;
      return updateNoteGeneral({ note_id, file_id, data })
        .then(({ data }) => {
          console.log(data);
          return data;
        })
        .catch((error) => {
          console.log(error);
          this.loadingSaveOrUpdate = false;
        })
        .finally(() => {
          this.loadingSaveOrUpdate = false;
        });
    },
    async listNote({ file_id }) {
      this.loading = true;
      return listNote({ file_id })
        .then(({ data }) => {
          console.log(data);
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    async createNote({ file_id, data }) {
      this.loadingSaveOrUpdateNote = true;
      return createNote({ file_id, data })
        .then(({ data }) => {
          return data;
        })
        .catch((error) => {
          this.loadingSaveOrUpdateNote = false;
          return error;
        })
        .finally(() => {
          this.loadingSaveOrUpdateNote = false;
        });
    },
    async editNote({ file_id, data, isCreated = false, isUpdated = false, ids = [], recharge }) {
      console.log('EL EDIT NOTE', data);
      if (data) {
        if (data.type_note === 'INFORMATIVE') {
          if (recharge) {
            await this.fetchAllFileNotes({ file_id: file_id });
          }

          if (data.record_type === 'FOR_FILE') {
            const additionalInfo = this.file_note_all?.additional_information || [];
            const index = additionalInfo.findIndex(
              (e) => e.id === data.id && e.entity === data.entity
            );
            if (index !== -1 || index != null) {
              additionalInfo[index] = {
                ...additionalInfo[index],
                isUpdated: isUpdated,
                isCreated: isCreated,
              };
            }
          } else {
            const serviceInfo = this.file_note_all?.for_service || [];
            for (const city in serviceInfo) {
              for (const date in serviceInfo[city]) {
                serviceInfo[city][date].forEach((note, index) => {
                  if (ids.includes(note.id)) {
                    serviceInfo[city][date][index] = {
                      ...note,
                      isCreated: isCreated,
                      isUpdated: isUpdated,
                    };
                  }
                });
              }
            }
            this.file_note_all.for_service = serviceInfo;
          }
        } else {
          if (recharge) {
            await this.fetchAllRequirementFileNotes({ file_id: file_id });
          }
          this.file_note_all_requirement.forEach((value, index) => {
            if (ids.includes(value.id) && value.entity === data.entity) {
              console.log('AQUI SE CAMBIO EL ESTADO', data);
              this.file_note_all_requirement[index] = {
                ...value,
                isCreated: isCreated,
                isUpdated: isUpdated,
              };
            }
          });
        }
        this.flag_change = true;
      }
    },
    async updateNote({ file_id, id, data }) {
      this.loadingSaveOrUpdateNote = true;
      return updateNote({ file_id, id, data })
        .then(({ data }) => {
          return data;
        })
        .catch((error) => {
          this.loadingSaveOrUpdateNote = false;
          return error;
        })
        .finally(() => {
          this.loadingSaveOrUpdateNote = false;
        });
    },
    async deleteNote({ file_id, id, data = {} }) {
      this.loadingSaveOrUpdateNote = true;
      return deleteNote({ file_id, id, data })
        .then(({ data }) => {
          return data;
        })
        .catch((error) => {
          console.log(error);
          this.loadingSaveOrUpdateNote = false;
          return error;
        })
        .finally(() => {
          this.loadingSaveOrUpdateNote = false;
        });
    },
    async quitNote({ file_id, data }) {
      if (data) {
        if (data.type_note === 'INFORMATIVE') {
          await this.fetchAllFileNotes({ file_id: file_id });
        } else {
          this.fetchAllRequirementFileNotes({ file_id: file_id });
        }
        this.flag_change = true;
      }
    },
    async findExternalHousing({ file_id, id }) {
      this.loadingExternalHousing = true;
      return findExternalHousing({ file_id, id })
        .then(({ data }) => {
          return data;
        })
        .catch((err) => {
          console.log(err);
          return err;
        })
        .finally(() => {
          this.loadingExternalHousing = false;
        });
    },
    async listExternalHousing({ file_id }) {
      this.loadingExternalHousing = true;
      return listExternalHousing({ file_id })
        .then(({ data }) => {
          console.log(data);
          if (data.success) {
            this.external_housing = data.data;
          } else {
            this.external_housing = [];
          }
        })
        .catch((error) => {
          console.log(error);
          this.loadingExternalHousing = false;
        })
        .finally(() => {
          this.loadingExternalHousing = false;
        });
    },
    async createExternalHousing({ file_id, data }) {
      this.loadingSaveOrUpdateExternalHousing = true;
      return createExternalHousing({ file_id, data })
        .then(({ data }) => {
          return data;
        })
        .catch((error) => {
          this.loadingSaveOrUpdateExternalHousing = false;
          return error;
        })
        .finally(() => {
          this.loadingSaveOrUpdateExternalHousing = false;
        });
    },
    async addExternalHousing(data) {
      if (data) {
        await this.listExternalHousing({ file_id: data.file_id });

        const index = this.external_housing.findIndex((e) => e.id === data.id);
        if (index !== -1) {
          this.external_housing[index] = {
            ...data,
            isUpdated: true,
            isCreated: false,
          };
        } else {
          this.external_housing.push({
            ...data,
            isCreated: true,
            isUpdated: false,
          });
        }

        this.flag_change = true;
      }
    },
    async updateExternalHousing({ file_id, id, data }) {
      this.loadingSaveOrUpdateExternalHousing = true;
      return updateExternalHousing({ file_id, id, data })
        .then(({ data }) => {
          return data;
        })
        .catch((error) => {
          this.loadingSaveOrUpdateExternalHousing = false;
          return error;
        })
        .finally(() => {
          this.loadingSaveOrUpdateExternalHousing = false;
        });
    },
    async editExternalHousing(data) {
      if (data) {
        await this.listExternalHousing({ file_id: data.file_id });

        const index = this.external_housing.findIndex((e) => e.id === data.id);
        if (index !== -1) {
          this.external_housing[index] = {
            ...data,
            isUpdated: true,
            isCreated: false,
          };
        }

        this.flag_change = true;
      }
    },
    async deleteExternalHousing({ file_id, id, data = {} }) {
      this.loadingSaveOrUpdateExternalHousing = true;
      return deleteExternalHousing({ file_id, id, data })
        .then(({ data }) => {
          return data;
        })
        .catch((error) => {
          this.loadingSaveOrUpdateExternalHousing = false;
          return error;
        })
        .finally(() => {
          this.loadingSaveOrUpdateExternalHousing = false;
        });
    },
    quitExternalHousing({ file_id }) {
      this.listExternalHousing({ file_id });
      this.flag_change = true;
    },
    async fetchAllFileNotes({ file_id }) {
      this.loadingNote = true;
      return fetchAllFileNotes({ file_id })
        .then(({ data }) => {
          console.log('AQUI ES LA DATA DE LISTAR TODAS LAS NOTAS: ', data);
          if (data.success) {
            this.file_note_all = data.data;
          } else {
            this.file_note_all = [];
          }
        })
        .catch((error) => {
          console.log(error);
          this.loadingNote = false;
        })
        .finally(() => {
          this.loadingNote = false;
        });
    },
    async fetchAllRequirementFileNotes({ file_id }) {
      this.loadingNoteRequirement = true;
      return fetchAllRequirementFileNotes({ file_id })
        .then(({ data }) => {
          console.log('AQUI EL DATA DE REQUIREMENT: ', data);
          if (data.success) {
            this.file_note_all_requirement = data.data;
          } else {
            this.file_note_all_requirement = [];
          }
        })
        .catch((error) => {
          console.log(error);
          this.loadingNoteRequirement = false;
        })
        .finally(() => {
          this.loadingNoteRequirement = false;
        });
    },
    changeFlagChange() {
      this.flag_change = false;
    },
  },
});
