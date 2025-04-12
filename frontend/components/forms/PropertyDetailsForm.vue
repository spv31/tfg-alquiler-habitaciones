<template>
  <div class="flex flex-col gap-4">
    <div class="flex mx-auto items-center">
      <input
        id="is_financed"
        type="checkbox"
        class="w-4 h-4 mr-2"
        v-model="localPropertyData.is_financed"
        :true-value="1"
        :false-value="0"
      />
      <label for="is_financed" class="font-semibold">
        {{ $t("properties.is_financed") }}
      </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div v-for="field in optionalFields" :key="field.key" class="flex flex-col">
        <label :for="field.key" class="label">{{ field.label }}</label>

        <input
          v-if="field.type !== 'checkbox'"
          :id="field.key"
          :type="field.type"
          class="custom-input"
          v-model="localPropertyData[field.key]"
          @input="validateField(field.key, field.type)"
          :placeholder="field.placeholder"
        />

        <p v-if="localErrors[field.key]" class="error-message">
          {{ localErrors[field.key] }}
        </p>
      </div>
    </div>

    <div class="flex flex-col mx-auto">
      <label for="property_size" class="label">
        {{ $t("properties.property_size") }}
      </label>
      <input
        id="property_size"
        type="number"
        class="custom-input"
        v-model="localPropertyData.property_size"
        @input="validateField('property_size', 'number')"
        placeholder="0.00"
      />
      <p v-if="localErrors.property_size" class="error-message">
        {{ localErrors.property_size }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
const { t: $t } = useI18n();

const emits = defineEmits(["update:propertyData", "update:errors"]);

const props = defineProps<{
  propertyData: Record<string, any>;
  errors: Record<string, any>;
  optionalFields: Array<{
    key: string;
    label: string;
    type: string;
    placeholder?: string;
  }>;
}>();

// Computed para propertyData y errors
const localPropertyData = computed({
  get: () => props.propertyData,
  set: (val) => emits("update:propertyData", val),
});
const localErrors = computed({
  get: () => props.errors,
  set: (val) => emits("update:errors", val),
});

const validateField = (key: string, type: string) => {
  const value = localPropertyData.value[key];
  if (type === "number") {
    if (value !== null && value !== "") {
      if (isNaN(Number(value)) || Number(value) < 0) {
        localErrors.value[key] = $t("errors.invalid_number");
      } else {
        localErrors.value[key] = "";
      }
    } else {
      localErrors.value[key] = "";
    }
  } else if (type === "date") {
    if (value === "" || !isNaN(new Date(value).getTime())) {
      localErrors.value[key] = "";
    } else {
      localErrors.value[key] = $t("errors.invalid_date");
    }
  }
}
</script>
