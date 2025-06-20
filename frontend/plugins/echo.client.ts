import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { getCsrfToken } from "~/utils/auth";

export default defineNuxtPlugin(async () => {
  // @ts-ignore
  window.Pusher = Pusher;

  const apiBaseUrl = 'http://localhost:8000'; 

  const echo = new Echo({
    broadcaster: "reverb",
    key: "my-rent-hub-key",
    wsHost: "localhost",
    wsPort: 8080,
    forceTLS: false,
    enabledTransports: ["ws"],
    authEndpoint: `${apiBaseUrl}/broadcasting/auth`,
    authorizer: (channel: any, options: any) => {
      return {
        authorize: async (socketId: any, callback: any) => {
          try {
            const csrf = await getCsrfToken();

            const response = await $fetch(`${apiBaseUrl}/broadcasting/auth`, {
              method: "POST",
              credentials: "include",
              headers: {
                "X-XSRF-TOKEN": csrf!,
              },
              body: {
                socket_id: socketId,
                channel_name: channel.name,
              },
            });

            callback(null, response);
            console.log('Response: ', response);
          } catch (error: any) {
            callback(new Error("Authorization failed"), error?.data || error);
          }
        },
      };
    },
  });

  return { provide: { echo } };
});
