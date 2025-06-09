import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { getCsrfToken } from "~/utils/auth";

export default defineNuxtPlugin(async () => {
  // @ts-ignore
  window.Pusher = Pusher;
  const csrf = await getCsrfToken();

  const echo = new Echo({
    broadcaster: "reverb",
    key: "my-rent-hub-key",
    wsHost: "localhost",
    wsPort: 8080,
    forceTLS: false,
    enabledTransports: ["ws"],
    authEndpoint: "/api/broadcasting/auth",
    authorizer: (channel, options) => {
      return {
        authorize: async (socketId, callback) => {
          try {
            const csrf = await getCsrfToken();

            const response = await $fetch("/api/broadcasting/auth", {
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
          } catch (error: any) {
            callback(new Error("Authorization failed"), error?.data || error);
          }
        },
      };
    },
  });

  return { provide: { echo } };
});
