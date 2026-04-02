import { usePermissionStore } from '@/stores/permission-store';

export default function checkPermission(...args) {
  let to, next;

  // Check if called as custom middleware (single object arg)
  if (args.length === 1 && typeof args[0] === 'object' && args[0].next) {
    to = args[0].to;
    next = args[0].next;
  } else {
    // Called as standard Vue Router guard (to, from, next)
    to = args[0];
    next = args[2];
  }

  const subject = to?.meta?.permission ?? '';
  const action = to?.meta?.action ?? '';

  if (subject === '' || action === '') {
    return next();
  }

  const permissionStore = usePermissionStore();
  const permissions = permissionStore.getPermissions();

  if (!permissions) {
    return next({ name: 'login' });
  }

  const permission = permissions.find((item) => item.subject === subject);

  if (permission && permission.action.includes(action)) {
    return next();
  }

  return next({ name: 'error_403' });
}
