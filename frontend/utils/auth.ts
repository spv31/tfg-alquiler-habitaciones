export const getCsrfToken = async (): Promise<string | null> => {
  console.log("[getCsrfToken] Obteniendo CSRF Token...");

  let matches = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (matches) {
    console.log("[getCsrfToken] Token encontrado en cookies:", matches[1]);

    return decodeURIComponent(matches[1]);
  }

  console.log("[getCsrfToken] Token no encontrado, enviando solicitud...");

  // If token is not in cookies, we send request
  await $fetch("http://localhost:8000/sanctum/csrf-cookie", {
    credentials: "include",
  });

  // matches = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  // return matches ? decodeURIComponent(matches[1]) : null;
  matches = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (matches) {
    console.log(
      "[getCsrfToken] Token obtenido después de la solicitud:",
      matches[1]
    );
    return decodeURIComponent(matches[1]);
  }

  console.error("[getCsrfToken] No se pudo obtener el CSRF Token.");
  return null;
};
