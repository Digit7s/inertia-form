<script setup lang="ts">
import { computed } from "vue";
import type { FormField } from "../fieldTypes";

const props = defineProps<{
    modelValue: unknown;
    field: FormField;
    fieldId?: string;
    errorId?: string;
    error?: string;
}>();

const emit = defineEmits<{ "update:modelValue": [value: string] }>();
const id = computed(() => props.fieldId || `inertia-form-${encodeURIComponent(props.field.name)}`);
const value = computed(() => props.modelValue == null ? "" : String(props.modelValue));
</script>

<template>
    <div class="space-y-2 text-left">
        <label :for="id" class="block text-sm font-medium">
            {{ field.label }} <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
        </label>
        <textarea
            :id="id"
            :value="value"
            :placeholder="field.placeholder || undefined"
            :required="field.required"
            :disabled="field.meta.disabled"
            :readonly="field.meta.read_only"
            :rows="Number(field.meta.rows || 3)"
            :cols="field.meta.cols as number | undefined"
            :minlength="field.meta.min_length as number | undefined"
            :maxlength="field.meta.max_length as number | undefined"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? (errorId || `${id}-error`) : undefined"
            class="w-full rounded border px-3 py-2 text-sm disabled:opacity-60"
            @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        />
        <p v-if="error" :id="errorId || `${id}-error`" class="text-sm text-red-700" role="alert">
            {{ error }}
        </p>
    </div>
</template>
