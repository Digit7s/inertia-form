<script setup lang="ts">
import { computed, ref, watch } from "vue";
import type { FormField } from "../fieldTypes";

const props = defineProps<{
    modelValue: unknown;
    field: FormField;
    fieldId?: string;
    errorId?: string;
    error?: string;
}>();

const emit = defineEmits<{ "update:modelValue": [value: File | null] }>();
const input = ref<HTMLInputElement | null>(null);
const id = computed(() => props.fieldId || `inertia-form-${encodeURIComponent(props.field.name)}`);
const existingValue = computed(() => typeof props.modelValue === "string" ? props.modelValue : "");

watch(() => props.modelValue, value => {
    // A server-provided path is display-only. Clear any previously selected
    // browser File when the form resets to null or adopts a stored path.
    if ((value === null || value === undefined || typeof value === "string") && input.value) {
        input.value.value = "";
    }
}, { flush: "post" });

const chooseFile = (event: Event) => {
    const input = event.target as HTMLInputElement;
    emit("update:modelValue", input.files?.item(0) || null);
};
</script>

<template>
    <div class="space-y-2 text-left">
        <label :for="id" class="block text-sm font-medium">
            {{ field.label }} <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
        </label>
        <p v-if="existingValue" class="text-sm">Current file: {{ existingValue }}</p>
        <input
            ref="input"
            :id="id"
            type="file"
            :required="Boolean(field.required && !existingValue)"
            :disabled="field.meta.disabled"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? (errorId || `${id}-error`) : undefined"
            class="block w-full text-sm disabled:opacity-60"
            @change="chooseFile"
        >
        <p class="text-xs opacity-75">Choose a file to upload. The form submits a browser File value through Inertia.</p>
        <p v-if="error" :id="errorId || `${id}-error`" class="text-sm text-red-700" role="alert">
            {{ error }}
        </p>
    </div>
</template>
