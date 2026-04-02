export interface Lock {
  provider: {
    _id: string;
    code: string;
    __v: number;
    contract: string;
    createdAt: string;
    fullname: string;
    status: boolean;
    type: string;
    updatedAt: string;
  };
  locks: {
    [key: string]: {
      selected?: boolean;
      details: {
        iso: string;
        description: string;
        datetime_start: string;
        datetime_end: string;
      }[];
      meta: {
        className: string;
        countSVS: number;
        countNonSVS: number;
      };
    };
  };
}
