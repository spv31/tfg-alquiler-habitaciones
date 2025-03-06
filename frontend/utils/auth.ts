export const getCsrfToken = async (): Promise<string | null> => {
  let matches = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (matches) return decodeURIComponent(matches[1]);
  
  // If token is not in cookies, we send request
  await $fetch("http://localhost:8000/sanctum/csrf-cookie", {
    credentials: "include",
  });

  matches = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  return matches ? decodeURIComponent(matches[1]) : null;
};
