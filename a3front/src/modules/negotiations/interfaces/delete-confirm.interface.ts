interface ConfirmOptions {
  title?: string;
  content?: string;
  okText?: string;
  cancelText?: string;
}

export interface DeleteConfirmOptions {
  deleteRequest: () => Promise<void>;
  onSuccess?: (data: any) => void;
  confirmOptions?: ConfirmOptions;
}
