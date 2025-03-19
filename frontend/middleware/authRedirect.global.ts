import { useAuthStore } from '~/store/auth';

export default defineNuxtRouteMiddleware((to) => {
  const { $localePath } = useNuxtApp();
  const authStore = useAuthStore();

  const allowedUnauthPaths = [
    $localePath('login'),    
    $localePath('register'),  
    $localePath('reset-password'),
    $localePath('/register/owner'),
    $localePath('/register/tenant'),
    '/reset-password',
    '/'                       
  ];

  const isAllowed = allowedUnauthPaths.some(path => to.path.startsWith(path));

  if (!authStore.isAuthenticated) {
    if (!isAllowed) {
      return navigateTo($localePath('login'));
    }
  } 
  else {
    if (!isAllowed) {
      return navigateTo($localePath('/dashboard'));
    }
  }
});
