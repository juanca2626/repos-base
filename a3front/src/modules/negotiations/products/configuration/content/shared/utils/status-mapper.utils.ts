import {
  ServiceStatusApi,
  ServiceStatusForm,
} from '@/modules/negotiations/products/configuration/content/shared/enums/service-status.enum';

export const mapApiStatusToFormStatus = (apiStatus: string): string => {
  const statusMap: Record<string, string> = {
    [ServiceStatusApi.ACTIVE]: ServiceStatusForm.ACTIVO,
    [ServiceStatusApi.SUSPENDED]: ServiceStatusForm.SUSPENDIDO,
    [ServiceStatusApi.INACTIVE]: ServiceStatusForm.INACTIVO,
  };
  return statusMap[apiStatus] || apiStatus.toLowerCase();
};

export const mapStatusToApiFormat = (status: string | undefined): string => {
  const statusMap: Record<string, string> = {
    [ServiceStatusForm.ACTIVO]: ServiceStatusApi.ACTIVE,
    [ServiceStatusForm.SUSPENDIDO]: ServiceStatusApi.SUSPENDED,
    [ServiceStatusForm.INACTIVO]: ServiceStatusApi.INACTIVE,
  };
  return status ? statusMap[status] || status.toUpperCase() : ServiceStatusApi.SUSPENDED;
};
