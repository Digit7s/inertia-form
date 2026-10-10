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
const inputType = computed(() => props.field.type === "datetime" ? "datetime-local" : props.field.type);

const nativeValue = (value: unknown): string => {
    if (value == null) return "";
    let normalized = String(value).trim().replace(" ", "T");
    if (props.field.type === "date") return normalized.slice(0, 10);
    if (props.field.type === "time") {
        const time = normalized.includes("T") ? normalized.split("T").at(-1) || "" : normalized;
        return props.field.meta.has_seconds ? time.slice(0, 8) : time.slice(0, 5);
    }
    return props.field.meta.has_seconds ? normalized.slice(0, 19) : normalized.slice(0, 16);
};

const step = computed(() => {
    if (props.field.type === "date") return undefined;
    if (props.field.meta.has_seconds) return Number(props.field.meta.seconds_step || 1);
    if (props.field.type === "time" && props.field.meta.minutes_step) return Number(props.field.meta.minutes_step) * 60;
    return 60;
});
</script>

<template>
    <div class="space-y-2 text-left">
        <label :for="id" class="block text-sm font-medium">
            {{ field.label }} <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
        </label>
        <input
            :id="id"
            :type="inputType"
            :value="nativeValue(modelValue)"
            :min="nativeValue(field.meta.min_date) || undefined"
            :max="nativeValue(field.meta.max_date) || undefined"
            :step="step"
            :required="field.required"
            :disabled="field.meta.disabled"
            :readonly="field.meta.read_only"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? (errorId || `${id}-error`) : undefined"
            class="w-full rounded border px-3 py-2 text-sm disabled:opacity-60"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        >
        <p v-if="error" :id="errorId || `${id}-error`" class="text-sm text-red-700" role="alert">
            {{ error }}
        </p>
    </div>
</template>
