import { useAuthStore } from '~/store/auth';

export default defineNuxtRouteMiddleware((to) => {
  const { $localePath } = useNuxtApp();
  const authStore = useAuthStore();

  const allowedUnauthPaths = [
    $localePath('login'),    
    $localePath('register'),  
    '/'                       
  ];

  if (!authStore.isAuthenticated) {
    if (!allowedUnauthPaths.includes(to.path)) {
      return navigateTo($localePath('login'));
    }
  } 
  else {
    if (allowedUnauthPaths.includes(to.path)) {
      return navigateTo($localePath('properties'));
    }
  }
});
