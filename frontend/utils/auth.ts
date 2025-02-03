export const getCsrfToken = async (): Promise<string | null> => {
  await $fetch("http://localhost:8000/sanctum/csrf-cookie", {
    credentials: "include",
  });

  const matches = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  return matches ? decodeURIComponent(matches[1]) : null;
};
