<script setup lang="ts">
import { computed, ref } from "vue";
import type { FormField } from "../fieldTypes";

interface SelectOption { value: string; label: string }
interface SelectGroup { label: string | null; options: SelectOption[] }

const props = defineProps<{
    modelValue: unknown;
    field: FormField;
    fieldId?: string;
    errorId?: string;
    error?: string;
}>();

const emit = defineEmits<{ "update:modelValue": [value: string | string[]] }>();
const search = ref("");
const id = computed(() => props.fieldId || `inertia-form-${encodeURIComponent(props.field.name)}`);
const isMultiple = computed(() => Boolean(props.field.meta.multiple));

const groups = computed<SelectGroup[]>(() => {
    const result: SelectGroup[] = [];
    const query = props.field.meta.searchable ? search.value.trim().toLocaleLowerCase() : "";

    for (const [key, value] of Object.entries(props.field.meta.options || {})) {
        if (value && typeof value === "object" && !Array.isArray(value)) {
            const options = Object.entries(value as Record<string, unknown>)
                .map(([optionValue, label]) => ({ value: optionValue, label: String(label ?? "") }))
                .filter(option => option.label.toLocaleLowerCase().includes(query));
            if (options.length) result.push({ label: key, options });
        } else {
            const label = String(value ?? "");
            if (label.toLocaleLowerCase().includes(query)) {
                let group = result.find(candidate => candidate.label === null);
                if (!group) {
                    group = { label: null, options: [] };
                    result.push(group);
                }
                group.options.push({ value: key, label });
            }
        }
    }

    return result;
});

const selectedValues = computed(() => {
    const value = props.modelValue;
    return (Array.isArray(value) ? value : value == null || value === "" ? [] : [value]).map(String);
});

const onChange = (event: Event) => {
    const select = event.target as HTMLSelectElement;
    if (isMultiple.value) {
        emit("update:modelValue", Array.from(select.selectedOptions).map(option => option.value));
    } else {
        emit("update:modelValue", select.value);
    }
};
</script>

<template>
    <div class="space-y-2 text-left">
        <label :for="id" class="block text-sm font-medium">
            {{ field.label }} <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
        </label>

        <input
            v-if="field.meta.searchable"
            v-model="search"
            type="search"
            :aria-label="`Search ${field.label} options`"
            :placeholder="String(field.meta.searchPrompt || 'Search options…')"
            class="mb-2 w-full rounded border px-3 py-2 text-sm"
        >

        <select
            :id="id"
            :multiple="isMultiple"
            :value="selectedValues"
            :required="field.required"
            :disabled="field.meta.disabled"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? (errorId || `${id}-error`) : undefined"
            class="w-full rounded border px-3 py-2 text-sm disabled:opacity-60"
            @change="onChange"
        >
            <option v-if="!isMultiple" value="">{{ field.placeholder || 'Select an option…' }}</option>
            <template v-for="(group, index) in groups" :key="group.label ?? `options-${index}`">
                <optgroup v-if="group.label" :label="group.label">
                    <option v-for="option in group.options" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </optgroup>
                <option v-else v-for="option in group.options" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </template>
        </select>

        <p v-if="error" :id="errorId || `${id}-error`" class="text-sm text-red-700" role="alert">
            {{ error }}
        </p>
    </div>
</template>
