<template>
  <div class="max-w-4xl mx-auto p-4 md:p-6">
    <Card class="shadow-xl rounded-2xl overflow-hidden border border-gray-100">
      <template #title>
        <div
          class="flex flex-wrap justify-between items-center gap-2 pb-3 border-b border-gray-100"
        >
          <div class="flex items-center gap-3">
            <h1
              class="text-xl md:text-2xl font-semibold text-gray-800 flex items-center gap-2"
            >
              <i class="pi pi-user text-blue-500"></i>
              Datos Personales
            </h1>
            <Tag
              :value="
                user?.email_verified_at
                  ? 'Verificado'
                  : 'Pendiente de verificar'
              "
              :severity="user?.email_verified_at ? 'success' : 'warn'"
              class="text-[0.65rem] md:text-xs"
            />
          </div>
          <div class="flex gap-2">
            <button
              v-if="!editing"
              class="button-primary inline-flex items-center justify-center rounded-lg whitespace-nowrap"
              @click="startEditing"
            >
              <i class="pi pi-pencil mr-2"></i>
              Editar perfil
            </button>
            <button
              v-else
              class="button-primary inline-flex items-center justify-center rounded-lg whitespace-nowrap"
              @click="saveProfile"
            >
              <i class="pi pi-save mr-2"></i>
              Guardar
            </button>
            <button
              v-if="editing"
              class="button-secondary inline-flex items-center justify-center rounded-lg whitespace-nowrap ml-1"
              @click="cancelEditing"
            >
              Cancelar
            </button>
          </div>
        </div>
      </template>

      <template #content>
        <div v-if="user" class="grid grid-cols-1 gap-6 py-2">
          <div class="flex justify-center mb-2">
            <div class="relative group">
              <div
                class="rounded-full p-1 bg-gradient-to-r from-blue-100 to-indigo-100"
              >
                <img
                  :src="user.profile_image_url || '/avatars/default.jpg'"
                  alt="Avatar"
                  class="w-32 h-32 md:w-28 md:h-28 rounded-full object-cover border-4 border-white shadow-md"
                />
              </div>

              <button
                class="absolute bottom-0 right-0 w-10 h-10 bg-blue-500 hover:bg-blue-600 rounded-full flex items-center justify-center shadow-lg z-10 transition-colors duration-200"
                @click="triggerFileInput"
              >
                <i class="pi pi-camera text-white text-lg"></i>
              </button>
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onAvatarSelected"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div class="space-y-4">
              <div class="bg-gray-50 rounded-xl p-3 border border-info/10">
                <label
                  class="block text-[0.6rem] font-semibold text-gray-500 uppercase tracking-wide mb-1"
                  >Nombre completo</label
                >
                <template v-if="editing">
                  <input
                    v-model="form.name"
                    class="custom-input w-full"
                    placeholder="Nombre completo"
                  />
                </template>
                <template v-else>
                  <p
                    class="text-base font-medium text-gray-800 flex items-center gap-1"
                  >
                    <i class="pi pi-user mr-1 text-blue-500"></i>
                    {{ user.name }}
                  </p>
                </template>
              </div>

              <div class="bg-gray-50 rounded-xl p-3 border border-info/10">
                <label
                  class="block text-[0.6rem] font-semibold text-gray-500 uppercase tracking-wide mb-1"
                  >Email</label
                >
                <p
                  class="text-base font-medium text-gray-800 flex items-center gap-1"
                >
                  <i class="pi pi-envelope mr-1 text-blue-500"></i>
                  {{ user.email }}
                </p>
              </div>

              <div class="bg-gray-50 rounded-xl p-3 border border-info/10">
                <label
                  class="block text-[0.6rem] font-semibold text-gray-500 uppercase tracking-wide mb-1"
                  >Teléfono</label
                >
                <template v-if="editing">
                  <input
                    v-model="form.phone_number"
                    class="custom-input w-full"
                    placeholder="Teléfono"
                  />
                </template>
                <template v-else>
                  <p
                    class="text-base font-medium text-gray-800 flex items-center gap-1"
                  >
                    <i class="pi pi-mobile mr-1 text-blue-500"></i>
                    {{ user.phone_number || "No especificado" }}
                  </p>
                </template>
              </div>
            </div>

            <div class="space-y-4">
              <div class="bg-gray-50 rounded-xl p-3 border border-info/10">
                <label
                  class="block text-[0.6rem] font-semibold text-gray-500 uppercase tracking-wide mb-1"
                  >Tipo de usuario</label
                >
                <p
                  class="text-base font-medium text-gray-800 flex items-center gap-1"
                >
                  <i class="pi pi-id-card mr-1 text-blue-500"></i>
                  {{ userTypeLabel(user.user_type) }}
                </p>
              </div>

              <div class="bg-gray-50 rounded-xl p-3 border border-info/10">
                <label
                  class="block text-[0.6rem] font-semibold text-gray-500 uppercase tracking-wide mb-1"
                  >Identificador</label
                >
                <p
                  class="text-base font-medium text-gray-800 flex items-center gap-1"
                >
                  <i class="pi pi-key mr-1 text-blue-500"></i>
                  {{ user.identifier }}
                </p>
              </div>

              <div class="bg-gray-50 rounded-xl p-3 border border-info/10">
                <label
                  class="block text-[0.6rem] font-semibold text-gray-500 uppercase tracking-wide mb-1"
                  >Rol</label
                >
                <p
                  class="text-base font-medium text-gray-800 flex items-center gap-1"
                >
                  <i class="pi pi-shield mr-1 text-blue-500"></i>
                  {{ roleLabel(user.role) }}
                </p>
              </div>
            </div>

            <div class="md:col-span-2">
              <div class="bg-gray-50 rounded-xl p-3 border border-info/10">
                <label
                  class="block text-[0.6rem] font-semibold text-gray-500 uppercase tracking-wide mb-1"
                  >Dirección</label
                >
                <template v-if="editing">
                  <input
                    v-model="form.address"
                    class="custom-input w-full"
                    placeholder="Dirección"
                  />
                </template>
                <template v-else>
                  <p
                    class="text-base font-medium text-gray-800 flex items-center gap-1"
                  >
                    <i class="pi pi-home mr-1 text-blue-500"></i>
                    {{ user.address || "No especificada" }}
                  </p>
                </template>
              </div>
            </div>

            <div class="md:col-span-2 pt-3 border-t border-info/10">
              <p
                class="text-sm font-medium text-gray-500 flex items-center gap-1"
              >
                <i class="pi pi-calendar mr-1"></i>
                Se unió el {{ formatDate(user.created_at) }}
              </p>
            </div>
          </div>

          <div
            v-if="!user.email_verified_at"
            class="mt-4 p-5 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200"
          >
            <div
              class="flex flex-col md:flex-row justify-between items-center gap-4"
            >
              <div class="flex items-start gap-3">
                <i class="pi pi-envelope text-blue-600 mt-1 text-xl"></i>
                <div>
                  <h3 class="font-bold text-blue-800 text-lg">
                    Verifica tu dirección de correo
                  </h3>
                  <p class="text-blue-600 mt-1">
                    Tu correo electrónico aún no ha sido verificado. Por favor
                    verifícalo para acceder a todas las funciones.
                  </p>
                </div>
              </div>
              <button
                class="button-primary inline-flex items-center justify-center rounded-lg whitespace-nowrap"
                @click="handleVerifyEmail"
              >
                <i
                  class="pi pi-envelope mr-2 text-base flex items-center justify-center"
                ></i>
                <span>Verificar correo</span>
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <ProgressSpinner class="!w-16 !h-16" strokeWidth="4" />
          <p class="mt-5 text-gray-600 text-base font-medium">
            Cargando datos personales…
          </p>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/store/auth";
import { useMyConfirm } from '~/composables/useMyConfirm'
import { useMyToast } from '~/composables/useMyToast'

const auth = useAuthStore();
const { user } = storeToRefs(auth);
const toast = useMyToast();
const confirm = useMyConfirm();

const editing = ref(false);
const form = reactive({
  name: "",
  phone_number: "",
  address: "",
});

const startEditing = () => {
  if (!user.value) return;
  form.name = user.value.name;
  form.phone_number = user.value.phone_number || "";
  form.address = user.value.address || "";
  editing.value = true;
}

const cancelEditing = () => {
  editing.value = false;
}

const saveProfile = async () => {
  try {
    await auth.updateProfile({
      name: form.name,
      phone_number: form.phone_number,
      address: form.address,
    });
    editing.value = false;
  } catch (e) {
    console.error(e);
  }
}

const triggerFileInput = () => {
  (fileInput.value as HTMLInputElement).click();
}

const fileInput = ref<HTMLInputElement | null>(null);

const onAvatarSelected = (e: Event) => {
  const input = e.target as HTMLInputElement;
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();
  reader.onload = async () => {
    const base64 = (reader.result as string).split(",")[1];
    try {
      await auth.changeAvatar(base64);
    } catch (e) {
      console.error(e);
    }
  };
  reader.readAsDataURL(file);
}

const handleVerifyEmail = () => {
  confirm.show({
    message: 'Se va a enviar un correo de verificación a tu dirección. ¿Continuar?',
    header: 'Verificar correo',
    icon: 'pi pi-envelope',
    acceptLabel: 'Sí, enviar',
    rejectLabel: 'Cancelar',
    acceptSeverity: 'info',
    onAccept: async () => {
      try {
        await auth.resendVerification()
        toast.success('Enlace de verificación enviado correctamente.', 5000)
      } catch (e) {
        toast.error('No se ha podido enviar el correo. Por favor inténtalo de nuevo.', 5000)
        console.error(e)
      }
    }
  })
}

const formatDate = (dateString: string) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString("es-ES", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
};

const roleLabel = (role: string) =>
  ({
    tenant: "Inquilino",
    owner: "Propietario",
  })[role] || role;

const userTypeLabel = (type: string) =>
  ({
    individual: "Individual",
    company: "Empresa",
  })[type] || type;
</script>

<style scoped>
.text-base {
  line-height: 1.4;
}
.text-sm {
  line-height: 1.3;
}
.text-lg {
  line-height: 1.5;
}

:deep(.p-tag) {
  min-width: max-content;
}
</style>
