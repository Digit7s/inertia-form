import type { Component } from "vue";
import MarkdownEditorInput from "./fields/MarkdownEditorInput.vue";
import RadioInput from "./fields/RadioInput.vue";
import RichTextEditorInput from "./fields/RichTextEditorInput.vue";
import SelectInput from "./fields/SelectInput.vue";
import TextInput from "./fields/TextInput.vue";
import TextareaInput from "./fields/TextareaInput.vue";
import CheckboxListInput from "./fields/CheckboxListInput.vue";
import DateTimeInput from "./fields/DateTimeInput.vue";
import FileInput from "./fields/FileInput.vue";
import UnknownFieldInput from "./fields/UnknownFieldInput.vue";

export const fieldRenderers: Record<string, Component> = {
    text: TextInput,
    textarea: TextareaInput,
    select: SelectInput,
    "checkbox-list": CheckboxListInput,
    date: DateTimeInput,
    time: DateTimeInput,
    datetime: DateTimeInput,
    file: FileInput,
    radio: RadioInput,
    "rich-text-editor": RichTextEditorInput,
    "markdown-editor": MarkdownEditorInput,
};

export const resolveFieldRenderer = (type: string): Component => {
    const renderer = fieldRenderers[type];
    if (renderer) return renderer;

    if (import.meta.env?.DEV) {
        console.error(`InertiaForm: No renderer is registered for field type "${type}".`);
    }

    return UnknownFieldInput;
};
