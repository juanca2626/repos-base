import { isAuthenticatedCognito, isAuthenticated, getUrlAuroraFront } from '../utils/auth';
export default function auth({ next, router, to }) {
  if (!isAuthenticated() || !isAuthenticatedCognito()) {
    router.push({ name: 'login' });
  }

  if (to.name.search('home') > -1) {
    if (window.environment != 'local') {
      window.location.href = getUrlAuroraFront();
      return false;
    }
  }

  return next();
}
