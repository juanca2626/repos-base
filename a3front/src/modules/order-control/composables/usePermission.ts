export function usePermission() {
  /**
   * Verifica si el usuario tiene permiso para una acción en un módulo específico.
   * @param subject - El nombre del módulo (ej: 'mfmyorders')
   * @param action - La acción a verificar (ej: 'reassign')
   * @returns {boolean}
   */
  const can = (subject: string, action: string): boolean => {
    try {
      const permissionsStr = localStorage.getItem('permissions');
      if (!permissionsStr) return false;

      const permissions = JSON.parse(permissionsStr);

      // Buscar el objeto del módulo
      // Tipado básico para el objeto de permisos recuperado
      const modulePermission = permissions.find(
        (p: { subject: string; action: string[] }) => p.subject === subject
      );

      if (!modulePermission || !Array.isArray(modulePermission.action)) {
        return false;
      }

      return modulePermission.action.includes(action);
    } catch (e) {
      console.error('Error parsing permissions:', e);
      return false;
    }
  };

  return { can };
}
