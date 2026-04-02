import { isAuthenticatedCognito, isAuthenticated } from '@/utils/auth';

export default function auth({ next, router }) {
  if (isAuthenticated() && isAuthenticatedCognito()) {
    router.push({ name: 'home' });
  }

  return next();
}
