<script setup lang="ts">
import { computed } from "vue";
import type { FormField } from "../fieldTypes";
import { responsiveColumnStyle } from "../fieldTypes";

const props = defineProps<{
    modelValue: unknown;
    field: FormField;
    fieldId?: string;
    errorId?: string;
    error?: string;
}>();

const emit = defineEmits<{ "update:modelValue": [value: string] }>();
const id = computed(() => props.fieldId || `inertia-form-${encodeURIComponent(props.field.name)}`);
const options = computed(() => props.field.meta.options || {});
const descriptions = computed(() => props.field.meta.descriptions || {});
const disabledOptions = computed(() => (props.field.meta.disabledOptions || []).map(String));
const columnsStyle = computed(() => responsiveColumnStyle(
    props.field.meta.columns as number | Record<string, number> | undefined,
));
const isDisabled = (value: string) => Boolean(
    props.field.meta.disabled || props.field.meta.read_only || disabledOptions.value.includes(value),
);
const optionId = (value: string) => `${id.value}-${encodeURIComponent(value).replaceAll('%', '_')}`;
</script>

<template>
    <fieldset class="space-y-2 text-left" :aria-describedby="error ? (errorId || `${id}-error`) : undefined">
        <legend class="text-sm font-medium">
            {{ field.label }}
            <span v-if="field.required" aria-hidden="true" class="text-red-600">*</span>
            <span v-if="field.required" class="sr-only"> (required)</span>
        </legend>
        <div
            class="radio-options-grid"
            :class="field.meta.inline ? 'is-inline' : ''"
            :style="columnsStyle"
            role="radiogroup"
            :aria-required="field.required || undefined"
            :aria-invalid="Boolean(error)"
        >
            <label v-for="(label, value) in options" :key="value" :for="optionId(String(value))" class="flex items-start gap-2 text-sm">
                <input
                    :id="optionId(String(value))"
                    type="radio"
                    :name="field.name"
                    :value="String(value)"
                    :checked="String(modelValue ?? '') === String(value)"
                    :required="field.required"
                    :disabled="isDisabled(String(value))"
                    @change="emit('update:modelValue', String(value))"
                >
                <span>
                    {{ label }}
                    <small v-if="descriptions[String(value)]" class="block opacity-75">
                        {{ descriptions[String(value)] }}
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
.radio-options-grid {
    display: grid;
    grid-template-columns: repeat(var(--form-columns-default, 1), minmax(0, 1fr));
    gap: 0.75rem;
}
.radio-options-grid.is-inline { display: flex; flex-wrap: wrap; }
@media (min-width: 640px) {
    .radio-options-grid { grid-template-columns: repeat(var(--form-columns-sm, var(--form-columns-default, 1)), minmax(0, 1fr)); }
}
@media (min-width: 768px) {
    .radio-options-grid { grid-template-columns: repeat(var(--form-columns-md, var(--form-columns-sm, var(--form-columns-default, 1))), minmax(0, 1fr)); }
}
@media (min-width: 1024px) {
    .radio-options-grid { grid-template-columns: repeat(var(--form-columns-lg, var(--form-columns-md, var(--form-columns-sm, var(--form-columns-default, 1)))), minmax(0, 1fr)); }
}
@media (min-width: 1280px) {
    .radio-options-grid { grid-template-columns: repeat(var(--form-columns-xl, var(--form-columns-lg, var(--form-columns-md, var(--form-columns-sm, var(--form-columns-default, 1))))), minmax(0, 1fr)); }
}
@media (min-width: 1536px) {
    .radio-options-grid { grid-template-columns: repeat(var(--form-columns-2xl, var(--form-columns-xl, var(--form-columns-lg, var(--form-columns-md, var(--form-columns-sm, var(--form-columns-default, 1)))))), minmax(0, 1fr)); }
}
</style>
