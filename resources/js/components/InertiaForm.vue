<script setup lang="ts">
import { computed, nextTick, ref, shallowRef, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import SchemaRenderer from "./SchemaRenderer.vue";
import { getFieldValue, isGridSchema, normalizeFormDefaults, schemaFieldNames, schemaValues, setFieldValue } from "./fieldTypes";
import type { FormSchemaItem } from "./fieldTypes";

type FormMethod = "post" | "put" | "patch" | "delete";
const validMethods = new Set<FormMethod>(["post", "put", "patch", "delete"]);

interface FormPayload {
    schema: FormSchemaItem[];
    defaults: Record<string, unknown>;
    action?: string | null;
    method?: string;
}

interface FormConfig {
    payload: FormPayload;
    action?: string;
    method?: string;
    identity: string;
}

const configMethod = (method?: string, payloadMethod?: string): string =>
    String(method || payloadMethod || "post").toLowerCase();

const configAction = (action?: string | null, payloadAction?: string | null): string => {
    const candidate: unknown = action || payloadAction;
    return typeof candidate === "string" ? candidate.trim() : "";
};

const props = withDefaults(defineProps<{
    formPayload: FormPayload;
    submitLabel?: string;
    method?: FormMethod;
    action?: string;
    formKey?: string;
    errorBag?: string;
    resetOnSuccess?: boolean;
}>(), {
    resetOnSuccess: false,
});

const readConfig = (): FormConfig => {
    const config = {
        payload: props.formPayload,
        action: props.action,
        method: props.method,
    };
    const identity = props.formKey?.trim()
        || `action:${configAction(config.action, config.payload.action)}|method:${configMethod(config.method, config.payload.method)}`;

    return { ...config, identity };
};

const activeConfig = shallowRef<FormConfig>(readConfig());
const pendingConfig = shallowRef<FormConfig | null>(null);
const initialValues = normalizeFormDefaults(activeConfig.value.payload.defaults);
const form = useForm<Record<string, any>>(initialValues);
const values = form as unknown as Record<string, any>;
const formElement = ref<HTMLFormElement | null>(null);
const submissionError = ref("");
const submitting = ref(false);
const isBusy = computed(() => submitting.value || form.processing);
const hasValidationErrors = computed(() => Object.keys(form.errors).length > 0);
const summaryVisible = computed(() => Boolean(submissionError.value || hasValidationErrors.value));

const resolvedMethod = (config: FormConfig): string => configMethod(config.method, config.payload.method);
const resolvedAction = (config: FormConfig): string => configAction(config.action, config.payload.action);

const updateDefaults = (config: FormConfig, resetCurrent: boolean): void => {
    const normalized = normalizeFormDefaults(config.payload.defaults);
    const previous = normalizeFormDefaults(activeConfig.value.payload.defaults);
    const combined: Record<string, unknown> = { ...normalized };

    // Inertia's defaults() merges keys. Mark removed top-level data undefined so
    // it cannot survive an identity change or a schema update.
    for (const key of Object.keys(previous)) {
        if (!(key in combined)) combined[key] = undefined;
    }

    form.defaults(combined);

    if (resetCurrent) {
        form.resetAndClearErrors();
        submissionError.value = "";
        return;
    }

    // Initialize newly introduced fields without replacing existing user edits.
    const oldNames = new Set(schemaFieldNames(activeConfig.value.payload.schema));
    for (const name of schemaFieldNames(config.payload.schema)) {
        if (!oldNames.has(name)) setFieldValue(values, name, getFieldValue(normalized, name));
    }
};

const adoptConfig = (config: FormConfig, resetCurrent: boolean): void => {
    updateDefaults(config, resetCurrent);
    activeConfig.value = config;
    pendingConfig.value = null;
};

watch(
    () => [props.formPayload, props.action, props.method, props.formKey] as const,
    () => {
        const incoming = readConfig();

        if (incoming.identity !== activeConfig.value.identity && form.isDirty) {
            pendingConfig.value = incoming;
            return;
        }

        const resetCurrent = !form.isDirty;
        adoptConfig(incoming, resetCurrent);
    },
    { flush: "post" },
);

const loadPendingConfig = (): void => {
    if (pendingConfig.value) adoptConfig(pendingConfig.value, true);
};

const resetForm = (): void => {
    form.resetAndClearErrors();
    clearTransientValues(activeConfig.value);
    form.defaults();
    submissionError.value = "";
};

const updateField = (name: string, value: unknown): void => {
    setFieldValue(values, name, value);
    form.clearErrors(name as keyof typeof form.errors & string);
    submissionError.value = "";
};

const containsFile = (value: unknown, seen = new Set<object>()): boolean => {
    if (typeof File !== "undefined" && value instanceof File) return true;
    if (typeof Blob !== "undefined" && value instanceof Blob) return true;
    if (!value || typeof value !== "object") return false;
    if (seen.has(value)) return false;
    seen.add(value);

    return Object.values(value).some(child => containsFile(child, seen));
};

const focusFirstError = async (): Promise<void> => {
    await nextTick();
    formElement.value?.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
};

const clearTransientValues = (config: FormConfig): void => {
    const clearInSchema = (schema: FormSchemaItem[]): void => {
        for (const item of schema) {
            if (isGridSchema(item)) {
                clearInSchema(item.schema);
            } else if (item.type === "file") {
                setFieldValue(values, item.name, null);
            } else if (item.type === "text" && item.meta.input_type === "password") {
                setFieldValue(values, item.name, "");
            }
        }
    };

    clearInSchema(config.payload.schema);
};

const submit = (): void => {
    if (isBusy.value || pendingConfig.value) return;

    const config = activeConfig.value;
    const finalMethod = resolvedMethod(config);
    const finalAction = resolvedAction(config);

    if (!validMethods.has(finalMethod as FormMethod)) {
        submissionError.value = `Cannot submit this form: unsupported method "${finalMethod}".`;
        return;
    }

    if (!finalAction) {
        submissionError.value = "Cannot submit this form: no action URL is configured.";
        return;
    }

    const data = schemaValues(config.payload.schema, values);
    const hasFiles = containsFile(data);
    const needsMethodSpoofing = hasFiles && finalMethod !== "post";
    const method = needsMethodSpoofing ? "post" : finalMethod as FormMethod;

    submissionError.value = "";
    form.clearErrors();
    submitting.value = true;

    form.transform(() => {
        const cleanData = { ...data };
        delete cleanData._method;

        return needsMethodSpoofing ? { ...cleanData, _method: finalMethod } : cleanData;
    });

    try {
        form.submit(method, finalAction, {
            preserveScroll: true,
            preserveState: true,
            errorBag: props.errorBag || undefined,
            onSuccess: () => {
                submissionError.value = "";
                // A successful create commonly redirects to the newly created
                // record's edit page, changing action identity while the sent
                // values are still dirty. Adopt that server response as the new
                // record baseline instead of leaving the form paused as though
                // the user navigated away with unsaved edits.
                if (pendingConfig.value) {
                    adoptConfig(pendingConfig.value, true);
                } else if (props.resetOnSuccess) {
                    form.resetAndClearErrors();
                    clearTransientValues(config);
                } else {
                    clearTransientValues(config);
                }
                // Otherwise Inertia's useForm records the successful data as its new baseline.
            },
            onError: () => {
                void focusFirstError();
            },
            onNetworkError: () => {
                submissionError.value = "The request could not reach the server. Your form values have been kept; try again.";
            },
            onHttpException: () => {
                submissionError.value = "The server could not process this request. Your form values have been kept; try again.";
            },
            onCancel: () => {
                submissionError.value = "The request was cancelled. Your form values have been kept.";
            },
            onFinish: () => {
                submitting.value = false;
            },
        });
    } catch {
        submitting.value = false;
        form.processing = false;
        form.progress = null;
        submissionError.value = "The form submission could not be started. Check the configured action and try again.";
    }
};

defineExpose({ form, reset: resetForm, submit });
</script>

<template>
    <form ref="formElement" class="space-y-6" :aria-busy="isBusy" @submit.prevent="submit">
        <div v-if="pendingConfig" class="space-y-2 rounded border border-amber-700 p-3" role="alert">
            <p>This form now points to a different record. Your unsaved changes are preserved and submission is paused.</p>
            <button type="button" class="underline" @click="loadPendingConfig">
                Discard these changes and load the new record
            </button>
        </div>

        <div v-if="summaryVisible" class="rounded border border-red-700 p-3" role="alert" aria-live="assertive">
            <p v-if="submissionError">{{ submissionError }}</p>
            <p v-if="hasValidationErrors">Please correct the highlighted errors.</p>
        </div>

        <SchemaRenderer
            :schema="activeConfig.payload.schema"
            :values="values"
            :errors="form.errors"
            @update:field="updateField"
        />

        <div class="flex items-center justify-end gap-x-4 pt-4">
            <slot name="actions" :form="form" :reset="resetForm" :processing="isBusy">
                <button
                    type="submit"
                    class="rounded-md border px-4 py-2 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="isBusy || Boolean(pendingConfig)"
                    :aria-busy="isBusy"
                >
                    {{ isBusy ? "Saving…" : (submitLabel || "Save Changes") }}
                </button>
            </slot>
        </div>

        <p v-if="isBusy" class="text-sm" role="status" aria-live="polite">Saving form…</p>

        <div v-if="form.progress" class="space-y-1" role="status" aria-live="polite">
            <progress :value="form.progress.percentage" max="100" class="w-full">
                {{ form.progress.percentage }}%
            </progress>
            <p class="text-sm">Uploading: {{ form.progress.percentage }}%</p>
        </div>
    </form>
</template>
