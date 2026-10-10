<script setup lang="ts">
import { computed, ref } from "vue";
import type { FormField } from "../fieldTypes";

const props = withDefaults(defineProps<{
    modelValue: unknown;
    field: FormField;
    fieldId?: string;
    errorId?: string;
    error?: string;
}>(), { fieldId: undefined, errorId: undefined });

const emit = defineEmits<{ "update:modelValue": [value: string] }>();
const passwordVisible = ref(false);
const meta = computed(() => props.field.meta as Record<string, any>);

const inputType = computed(() => {
    const type = String(meta.value.input_type || "text");
    return type === "password" && passwordVisible.value ? "text" : type;
});

const id = computed(() => props.fieldId || `inertia-form-${encodeURIComponent(props.field.name)}`);
const describedBy = computed(() => props.error ? (props.errorId || `${id.value}-error`) : undefined);
</script>

<template>
    <div class="space-y-2 text-left">
        <label :for="id" class="block text-sm font-medium">
            {{ field.label }} <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
        </label>

        <div class="flex items-center">
            <span v-if="meta.prefix" class="rounded-l border px-3 py-2 text-sm">{{ meta.prefix }}</span>
            <input
                :id="id"
                :type="inputType"
                :value="modelValue ?? ''"
                :placeholder="field.placeholder || undefined"
                :required="field.required"
                :disabled="meta.disabled"
                :readonly="meta.read_only"
                :autofocus="meta.autofocus"
                :autocomplete="meta.autocomplete"
                :minlength="meta.min_length"
                :maxlength="meta.max_length"
                :min="meta.min_value"
                :max="meta.max_value"
                :step="meta.step"
                :aria-invalid="Boolean(error)"
                :aria-describedby="describedBy"
                class="min-w-0 flex-1 rounded border px-3 py-2 text-sm disabled:opacity-60"
                @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            >
            <span v-if="meta.suffix" class="rounded-r border px-3 py-2 text-sm">{{ meta.suffix }}</span>
            <button
                v-if="meta.input_type === 'password' && !meta.disabled"
                type="button"
                class="ml-2 rounded border px-2 py-1 text-sm"
                :aria-label="passwordVisible ? 'Hide password' : 'Show password'"
                @click="passwordVisible = !passwordVisible"
            >
                {{ passwordVisible ? 'Hide' : 'Show' }}
            </button>
        </div>

        <p v-if="error" :id="errorId || `${id}-error`" class="text-sm text-red-700" role="alert">
            {{ error }}
        </p>
    </div>
</template>
