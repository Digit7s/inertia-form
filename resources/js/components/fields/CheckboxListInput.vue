<script setup lang="ts">
import { computed, ref } from "vue";
import type { FormField } from "../fieldTypes";
import { responsiveColumnStyle } from "../fieldTypes";

interface CheckboxOption { value: string; label: string }

const props = defineProps<{
    modelValue: unknown;
    field: FormField;
    fieldId?: string;
    errorId?: string;
    error?: string;
}>();

const emit = defineEmits<{ "update:modelValue": [value: string[]] }>();
const search = ref("");
const id = computed(() => props.fieldId || `inertia-form-${encodeURIComponent(props.field.name)}`);
const options = computed<CheckboxOption[]>(() => Object.entries(props.field.meta.options || {})
    .map(([value, label]) => ({ value, label: String(label ?? "") }))
    .filter(option => option.label.toLocaleLowerCase().includes(
        props.field.meta.searchable ? search.value.trim().toLocaleLowerCase() : "",
    )));
const selected = computed(() => (Array.isArray(props.modelValue) ? props.modelValue : []).map(String));
const disabledOptions = computed(() => (props.field.meta.disabledOptions || []).map(String));
const columns = computed(() => responsiveColumnStyle(props.field.meta.columns));

const isDisabled = (value: string) => {
    if (props.field.meta.disabled || disabledOptions.value.includes(value)) return true;
    const maximum = Number(props.field.meta.maxItems || 0);
    return maximum > 0 && selected.value.length >= maximum && !selected.value.includes(value);
};

const toggle = (value: string, checked: boolean) => {
    if (isDisabled(value) && checked) return;
    const next = new Set(selected.value);
    checked ? next.add(value) : next.delete(value);
    emit("update:modelValue", Array.from(next));
};

const toggleAll = () => {
    const available = Object.keys(props.field.meta.options || {}).filter(value => !disabledOptions.value.includes(value));
    const allSelected = available.every(value => selected.value.includes(value));
    const maximum = Number(props.field.meta.maxItems || 0);
    const additions = available.filter(value => !selected.value.includes(value));
    const boundedAdditions = maximum > 0 ? additions.slice(0, Math.max(0, maximum - selected.value.length)) : additions;
    const next = allSelected
        ? selected.value.filter(value => !available.includes(value))
        : Array.from(new Set([...selected.value, ...boundedAdditions]));
    emit("update:modelValue", next);
};
</script>

<template>
    <fieldset class="space-y-2 text-left" :aria-describedby="error ? (errorId || `${id}-error`) : undefined">
        <legend class="text-sm font-medium">
            {{ field.label }}
            <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
            <span v-if="field.required" class="sr-only"> (required)</span>
        </legend>

        <input
            v-if="field.meta.searchable"
            v-model="search"
            type="search"
            :aria-label="String(field.meta.searchPrompt || `Search ${field.label} options`)"
            :placeholder="String(field.meta.searchPrompt || 'Search options…')"
            class="w-full rounded border px-3 py-2 text-sm"
        >
        <button v-if="field.meta.bulkToggleable" type="button" class="rounded border px-2 py-1 text-sm" @click="toggleAll">
            {{ options.every(option => selected.includes(option.value)) ? 'Deselect all' : 'Select all' }}
        </button>

        <div
            class="checkbox-options-grid"
            :style="columns"
            :aria-required="field.required || undefined"
            role="group"
            :aria-label="field.label"
        >
            <label v-for="option in options" :key="option.value" class="flex items-start gap-2 text-sm">
                <input
                    type="checkbox"
                    :id="`${id}-${encodeURIComponent(option.value).replaceAll('%', '_')}`"
                    :name="field.name"
                    :value="option.value"
                    :checked="selected.includes(option.value)"
                    :disabled="isDisabled(option.value)"
                    :aria-invalid="Boolean(error)"
                    @change="toggle(option.value, ($event.target as HTMLInputElement).checked)"
                >
                <span>
                    {{ option.label }}
                    <small v-if="field.meta.descriptions?.[option.value]" class="block opacity-75">
                        {{ field.meta.descriptions[option.value] }}
                    </small>
                </span>
            </label>
        </div>

        <p v-if="error" :id="errorId || `${id}-error`" class="text-sm text-red-700" role="alert">
            {{ error }}
        </p>
    </fieldset>
</template>

<style scoped>
.checkbox-options-grid {
    display: grid;
    grid-template-columns: repeat(var(--form-columns-default, 1), minmax(0, 1fr));
    gap: 0.75rem;
}

@media (min-width: 640px) {
    .checkbox-options-grid { grid-template-columns: repeat(var(--form-columns-sm, var(--form-columns-default, 1)), minmax(0, 1fr)); }
}
@media (min-width: 768px) {
    .checkbox-options-grid { grid-template-columns: repeat(var(--form-columns-md, var(--form-columns-sm, var(--form-columns-default, 1))), minmax(0, 1fr)); }
}
@media (min-width: 1024px) {
    .checkbox-options-grid { grid-template-columns: repeat(var(--form-columns-lg, var(--form-columns-md, var(--form-columns-sm, var(--form-columns-default, 1)))), minmax(0, 1fr)); }
}
@media (min-width: 1280px) {
    .checkbox-options-grid { grid-template-columns: repeat(var(--form-columns-xl, var(--form-columns-lg, var(--form-columns-md, var(--form-columns-sm, var(--form-columns-default, 1))))), minmax(0, 1fr)); }
}
@media (min-width: 1536px) {
    .checkbox-options-grid { grid-template-columns: repeat(var(--form-columns-2xl, var(--form-columns-xl, var(--form-columns-lg, var(--form-columns-md, var(--form-columns-sm, var(--form-columns-default, 1)))))), minmax(0, 1fr)); }
}
</style>
