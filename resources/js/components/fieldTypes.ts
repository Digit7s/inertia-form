export interface FieldMeta {
    [key: string]: unknown;
    options?: Record<string, unknown>;
    disabled?: boolean;
    read_only?: boolean;
    columns?: number | Record<string, number>;
    descriptions?: Record<string, string>;
    disabledOptions?: string[];
}

export interface FormField {
    name: string;
    label: string;
    type: string;
    default?: unknown;
    required?: boolean;
    placeholder?: string | null;
    suffix_icon?: string | null;
    column_span?: number | string | null;
    meta: FieldMeta;
}

export interface GridSchema {
    type: "grid";
    columns: number;
    column_span?: number | string;
    schema: FormSchemaItem[];
}

export type FormSchemaItem = FormField | GridSchema;

const pathParts = (path: string): string[] => path.split(".").filter(Boolean);
const forbiddenPathParts = new Set(["__proto__", "prototype", "constructor"]);

export const getFieldValue = (values: Record<string, any>, path: string): unknown => {
    if (Object.prototype.hasOwnProperty.call(values, path)) return values[path];

    let current: unknown = values;

    for (const part of pathParts(path)) {
        if (forbiddenPathParts.has(part) || !current || typeof current !== "object") return undefined;
        current = (current as Record<string, unknown>)[part];
    }

    return current;
};

export const setFieldValue = (values: Record<string, any>, path: string, value: unknown): void => {
    if (Object.prototype.hasOwnProperty.call(values, path)) {
        values[path] = value;
        return;
    }

    const parts = pathParts(path);
    if (parts.length === 0 || parts.some(part => forbiddenPathParts.has(part))) return;

    let current = values;
    for (const [index, part] of parts.entries()) {
        if (index === parts.length - 1) {
            current[part] = value;
            return;
        }

        const nextPart = parts[index + 1];
        if (!current[part] || typeof current[part] !== "object") {
            current[part] = /^\d+$/.test(nextPart) ? [] : {};
        }
        current = current[part] as Record<string, any>;
    }
};

export const normalizeFormDefaults = (defaults: Record<string, unknown>): Record<string, unknown> => {
    const normalized: Record<string, any> = {};
    for (const [path, value] of Object.entries(defaults)) setFieldValue(normalized, path, value);
    return normalized;
};

export const isGridSchema = (item: FormSchemaItem): item is GridSchema =>
    item.type === "grid" && "schema" in item && Array.isArray(item.schema);

export const schemaFieldNames = (schema: FormSchemaItem[]): string[] => schema.flatMap(item =>
    isGridSchema(item) ? schemaFieldNames(item.schema) : [item.name],
);

export const schemaValues = (schema: FormSchemaItem[], values: Record<string, any>): Record<string, unknown> => {
    const data: Record<string, any> = {};
    for (const name of schemaFieldNames(schema)) {
        if (name === "_method") continue;
        setFieldValue(data, name, getFieldValue(values, name));
    }
    return data;
};

export const fieldId = (name: string): string =>
    `inertia-form-${encodeURIComponent(name).replaceAll("%", "_")}`;

export const spanStyle = (span: number | string | null | undefined): string | undefined => {
    if (span === "full") return "1 / -1";
    if (typeof span === "number" && span > 0) return `span ${span} / span ${span}`;
    return undefined;
};

export const responsiveColumnStyle = (
    columns: number | Record<string, number> | undefined,
): Record<string, string | number> => {
    if (typeof columns === "number") {
        return { "--form-columns-default": columns };
    }

    const style: Record<string, string | number> = {};
    if (columns) {
        for (const [breakpoint, count] of Object.entries(columns)) {
            const key = breakpoint === "default" ? "default" : breakpoint;
            if (["default", "sm", "md", "lg", "xl", "2xl"].includes(key) && Number.isInteger(count) && count > 0) {
                style[`--form-columns-${key}`] = count;
            }
        }
    }

    return style;
};
