import type { Router } from 'vue-router';

export function pushSupplierEditForm(router: Router, id: number) {
  return router.push({ name: 'supplier-edit-form', params: { id } });
}
