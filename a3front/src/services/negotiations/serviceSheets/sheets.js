import negotiationsApi from '@/modules/negotiations/negotiationsApi.ts';

class ServicesSheet {
  constructor() {}

  getTypeSheetService = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/request/tables');

    return data.serviceSheets;
  };
}

export default ServicesSheet;
