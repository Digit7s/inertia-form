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
const editorStyle = computed(() => ({
    minHeight: typeof props.field.meta.minHeight === "number"
        ? `${props.field.meta.minHeight}px`
        : String(props.field.meta.minHeight || "180px"),
    maxHeight: props.field.meta.maxHeight
        ? typeof props.field.meta.maxHeight === "number"
            ? `${props.field.meta.maxHeight}px`
            : String(props.field.meta.maxHeight)
        : undefined,
}));
</script>

<template>
    <div class="space-y-2 text-left">
        <label :for="id" class="block text-sm font-medium">
            {{ field.label }} <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
        </label>
        <div class="grid gap-3" :class="field.meta.preview && field.meta.sideBySide ? 'md:grid-cols-2' : 'grid-cols-1'">
            <textarea
                :id="id"
                :value="modelValue || ''"
                :placeholder="String(field.meta.placeholder || field.placeholder || 'Write Markdown…')"
                :disabled="field.meta.disabled"
                :readonly="field.meta.read_only"
                :required="field.required"
                :style="editorStyle"
                :aria-invalid="Boolean(error)"
                :aria-describedby="error ? (errorId || `${id}-error`) : undefined"
                class="w-full rounded border px-3 py-2 font-mono text-sm disabled:opacity-60"
                @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
            />
            <div v-if="field.meta.preview" class="min-h-44 overflow-auto rounded border p-3 text-sm">
                <pre class="whitespace-pre-wrap">{{ modelValue }}</pre>
            </div>
        </div>
        <p v-if="error" :id="errorId || `${id}-error`" class="text-sm text-red-700" role="alert">
            {{ error }}
        </p>
    </div>
</template>
