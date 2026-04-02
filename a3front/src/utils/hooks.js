import { ref, readonly } from 'vue';

const actuaBreadcrumbView = ref({ view: null, label: null });
const actuaBreadcrumbVars = ref([]);
export const useBreadcrumb = () => {
  const changeView = (view) => {
    actuaBreadcrumbView.value = view ? view : { view: null, label: null };
  };
  const setVariables = (vars) => {
    actuaBreadcrumbVars.value = vars ? vars : [];
  };
  return {
    actuaBreadcrumbView: readonly(actuaBreadcrumbView),
    actuaBreadcrumbVars: readonly(actuaBreadcrumbVars),
    changeView,
    setVariables,
  };
};

// --------- useBreadcrumb()
// useBreadcrumb() se usara para modificar y obtener el ultimo link que no viene de las rutas, sino de vistas que son componentes
//
// Instrucciones de uso
//
// ----- importar hook -------
// import { useBreadcrumb } from '@/utils/hooks.js'
// const { changeView, actuaBreadcrumbView } = useBreadcrumb();
//
// ----- Para cambiar de vista(SPA) llamar a changeView({view, label}) ---------
// changeView({view, label})
//
// * si se envia changeView(null) el valor de `actuaBreadcrumbView` se restablecera a ({view: null, label: null})
//
// ----- Para obtener la vista actual llamar a actuaBreadcrumbView
// actuaBreadcrumbView.value?.view ----> nombre de la vista
// actuaBreadcrumbView.value?.label ----> label de la vista
//
