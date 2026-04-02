import { ref, h } from 'vue';
import { Modal } from 'ant-design-vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { handleError } from '@/modules/negotiations/api/responseApi';
import type { DeleteConfirmOptions } from '@/modules/negotiations/interfaces/delete-confirm.interface';

export function useDeleteConfirm() {
  const [modal, contextHolder] = Modal.useModal();
  const cancelDialog = ref({ disabled: false });

  const showDeleteConfirm = ({
    deleteRequest,
    onSuccess,
    confirmOptions,
  }: DeleteConfirmOptions) => {
    const title = confirmOptions?.title || '¿Quieres eliminar el registro?';
    const content =
      confirmOptions?.content || 'Al hacer clic en el botón Eliminar, se eliminará el registro.';
    const okText = confirmOptions?.okText || 'Eliminar';
    const cancelText = confirmOptions?.cancelText || 'Cancelar';

    modal.confirm({
      title,
      icon: h(ExclamationCircleOutlined),
      content,
      okText,
      cancelText,
      okType: 'primary',
      keyboard: false,
      cancelButtonProps: cancelDialog.value,
      async onOk() {
        try {
          cancelDialog.value.disabled = true;
          const response: any = await deleteRequest();
          onSuccess?.(response.data);
        } catch (error: any) {
          handleError(error);
          console.error('Error al eliminar registro:', error);
        } finally {
          cancelDialog.value.disabled = false;
        }
      },
      onCancel() {},
    });
  };

  return {
    showDeleteConfirm,
    contextHolder,
  };
}
