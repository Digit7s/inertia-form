<script setup lang="ts">
import { computed } from "vue";
import type { FormField } from "../fieldTypes";

const props = defineProps<{
    modelValue: string | null;
    field: FormField;
    fieldId?: string;
    errorId?: string;
    error?: string;
}>();

const emit = defineEmits<{ "update:modelValue": [value: string] }>();
const id = computed(() => props.fieldId || `inertia-form-${encodeURIComponent(props.field.name)}`);
</script>

<template>
    <div class="space-y-2 text-left">
        <label :for="id" class="block text-sm font-medium">
            {{ field.label }} <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
        </label>
        <textarea
            :id="id"
            :value="modelValue || ''"
            :placeholder="String(field.meta.placeholder || field.placeholder || '')"
            :disabled="field.meta.disabled"
            :readonly="field.meta.read_only"
            :required="field.required"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? (errorId || `${id}-error`) : undefined"
            class="w-full rounded border px-3 py-2 text-sm"
            :style="{ minHeight: typeof field.meta.minHeight === 'number' ? `${field.meta.minHeight}px` : String(field.meta.minHeight || '160px') }"
            @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        />
        <p class="text-xs opacity-75">Rich-text markup is shown as text here. Treat submitted HTML as untrusted on the server.</p>
        <p v-if="error" :id="errorId || `${id}-error`" class="text-sm text-red-700" role="alert">
            {{ error }}
        </p>
    </div>
</template>
