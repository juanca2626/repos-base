interface BasePermission {
  subject: string;
}

export interface Permission extends BasePermission {
  actions: string[];
}

export interface AbilityPermission extends BasePermission {
  action: string[];
}
