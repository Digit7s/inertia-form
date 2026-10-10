<script setup lang="ts">
import { resolveFieldRenderer } from "./fieldRegistry";
import type { FormField, FormSchemaItem, GridSchema } from "./fieldTypes";
import { fieldId, getFieldValue, spanStyle } from "./fieldTypes";

defineOptions({ name: "SchemaRenderer" });

const props = defineProps<{
    schema: FormSchemaItem[];
    values: Record<string, any>;
    errors: Record<string, string>;
    path?: string;
}>();

const emit = defineEmits<{
    "update:field": [name: string, value: unknown];
}>();

const isGrid = (item: FormSchemaItem): item is GridSchema => item.type === "grid";

const itemKey = (item: FormSchemaItem, index: number): string => {
    const prefix = props.path || "schema";
    return isGrid(item) ? `${prefix}.grid-${index}` : `${prefix}.${item.name}`;
};

const gridStyle = (grid: GridSchema) => ({
    display: "grid",
    gridTemplateColumns: `repeat(${Math.max(1, grid.columns || 1)}, minmax(0, 1fr))`,
    gap: "1.5rem",
    gridColumn: spanStyle(grid.column_span),
});

const fieldStyle = (field: FormField) => ({
    gridColumn: spanStyle(field.column_span),
});

const fieldErrorId = (field: FormField): string => `${fieldId(field.name)}-error`;

const updateField = (name: string, value: unknown) => emit("update:field", name, value);
</script>

<template>
    <div class="form-schema" :class="path ? 'form-schema-nested' : 'form-schema-root'">
        <template v-for="(item, index) in schema" :key="itemKey(item, index)">
            <div v-if="isGrid(item)" class="form-grid" :style="gridStyle(item)">
                <SchemaRenderer
                    :schema="item.schema"
                    :values="values"
                    :errors="errors"
                    :path="itemKey(item, index)"
                    @update:field="updateField"
                />
            </div>

            <component
                :is="resolveFieldRenderer(item.type)"
                v-else
                :field="item"
                :field-id="fieldId(item.name)"
                :error-id="fieldErrorId(item)"
                :model-value="getFieldValue(values, item.name)"
                :error="errors[item.name]"
                :style="fieldStyle(item)"
                @update:model-value="updateField(item.name, $event)"
            />
        </template>
    </div>
</template>

<style scoped>
.form-schema-root {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.5rem;
}

.form-schema-nested {
    display: contents;
}
</style>
