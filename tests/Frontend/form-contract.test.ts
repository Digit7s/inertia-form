import { beforeEach, describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";
import { fieldRenderers, resolveFieldRenderer } from "../../resources/js/components/fieldRegistry";
import SchemaRenderer from "../../resources/js/components/SchemaRenderer.vue";
import type { FormField, FormSchemaItem } from "../../resources/js/components/fieldTypes";
import TextInput from "../../resources/js/components/fields/TextInput.vue";
import TextareaInput from "../../resources/js/components/fields/TextareaInput.vue";
import SelectInput from "../../resources/js/components/fields/SelectInput.vue";
import CheckboxListInput from "../../resources/js/components/fields/CheckboxListInput.vue";
import DateTimeInput from "../../resources/js/components/fields/DateTimeInput.vue";
import FileInput from "../../resources/js/components/fields/FileInput.vue";
import RadioInput from "../../resources/js/components/fields/RadioInput.vue";
import RichTextEditorInput from "../../resources/js/components/fields/RichTextEditorInput.vue";
import MarkdownEditorInput from "../../resources/js/components/fields/MarkdownEditorInput.vue";
import InertiaForm from "../../resources/js/components/InertiaForm.vue";

const inertiaMock = vi.hoisted(() => ({
    submit: null as null | ((method: string, url: string, options: unknown) => void),
    transform: null as null | ((data: Record<string, unknown>) => Record<string, unknown>),
    form: null as any,
    options: null as any,
}));

vi.mock("@inertiajs/vue3", async () => {
    const { reactive, watch } = await import("vue");

    const clone = (value: any): any => {
        if (typeof File !== "undefined" && value instanceof File) return value;
        if (Array.isArray(value)) return value.map(clone);
        if (value && typeof value === "object") return Object.fromEntries(Object.entries(value).map(([key, child]) => [key, clone(child)]));
        return value;
    };

    return {
        useForm: (defaults: Record<string, unknown>) => {
            let transformer = (data: Record<string, unknown>) => data;
            let currentDefaults = clone(defaults);
            const form: any = reactive({
                ...clone(defaults),
                errors: {},
                isDirty: false,
                processing: false,
                progress: null,
                wasSuccessful: false,
                data: () => Object.fromEntries(Object.keys(currentDefaults).map(key => [key, clone(form[key])])),
                defaults: (fields?: Record<string, unknown>) => {
                    currentDefaults = fields ? { ...currentDefaults, ...clone(fields) } : clone(form.data());
                    return form;
                },
                reset: (...names: string[]) => {
                    if (names.length === 0) Object.assign(form, clone(currentDefaults));
                    else names.forEach(name => { if (name in currentDefaults) form[name] = clone(currentDefaults[name]); });
                    return form;
                },
                clearErrors: (...names: string[]) => {
                    if (names.length === 0) form.errors = {};
                    else names.forEach(name => { delete form.errors[name]; });
                    return form;
                },
                resetAndClearErrors: (...names: string[]) => {
                    form.reset(...names);
                    form.clearErrors(...names);
                    return form;
                },
                setError: (name: string, message: string) => { form.errors[name] = message; },
                transform: (callback: (data: Record<string, unknown>) => Record<string, unknown>) => {
                    transformer = callback;
                    inertiaMock.transform = callback;
                    return form;
                },
                submit: (method: string, url: string, options: unknown) => {
                    form.processing = true;
                    inertiaMock.options = options;
                    inertiaMock.submit?.(method, url, { options, data: transformer(form.data()) });
                },
            });
            watch(form, () => {
                form.isDirty = JSON.stringify(form.data()) !== JSON.stringify(currentDefaults);
            }, { deep: true });
            inertiaMock.form = form;
            return form;
        },
    };
});

const field = (type: string, name = type, extra: Partial<FormField> = {}): FormField => ({
    name,
    label: `${name} label`,
    type,
    default: "",
    required: false,
    placeholder: "Enter value",
    meta: {},
    ...extra,
});

const rendererCases: Array<[string, unknown]> = [
    ["text", TextInput],
    ["textarea", TextareaInput],
    ["select", SelectInput],
    ["checkbox-list", CheckboxListInput],
    ["date", DateTimeInput],
    ["time", DateTimeInput],
    ["datetime", DateTimeInput],
    ["file", FileInput],
    ["radio", RadioInput],
    ["rich-text-editor", RichTextEditorInput],
    ["markdown-editor", MarkdownEditorInput],
];

describe("PHP to Vue field contract", () => {
    beforeEach(() => {
        inertiaMock.submit = null;
        inertiaMock.transform = null;
        inertiaMock.form = null;
        inertiaMock.options = null;
    });

    it.each(rendererCases)("maps %s to its renderer", (type, component) => {
        expect(fieldRenderers[type]).toBe(component);
    });

    it("shows an explicit error for unknown types instead of rendering text", () => {
        const error = vi.spyOn(console, "error").mockImplementation(() => {});
        const unknown = field("custom-control", "custom");
        const wrapper = mount(SchemaRenderer, {
            props: { schema: [unknown], values: {}, errors: {} },
        });

        expect(resolveFieldRenderer("custom-control")).not.toBe(TextInput);
        expect(wrapper.get('[role="alert"]').text()).toContain("custom-control");
        expect(error).toHaveBeenCalled();
        error.mockRestore();
    });

    it("renders field defaults with stable, associated labels", () => {
        const wrapper = mount(SchemaRenderer, {
            props: {
                schema: [field("text", "profile.name", { default: "Ada" })],
                values: { "profile.name": "Ada" },
                errors: {},
            },
        });

        expect(wrapper.get("input").element.value).toBe("Ada");
        expect(wrapper.get("label").attributes("for")).toBe(wrapper.get("input").attributes("id"));
        expect(wrapper.get("input").attributes("id")).toContain("profile.name");
    });

    it("renders the expected defaults for all core field controls", () => {
        const cases: Array<{ item: FormField; value: unknown; selector: string; expected: string }> = [
            { item: field("text", "text"), value: "sample", selector: "input", expected: "sample" },
            { item: field("textarea", "area"), value: "paragraph", selector: "textarea", expected: "paragraph" },
            { item: field("select", "select", { meta: { options: { one: "One" } } }), value: "one", selector: "select", expected: "one" },
            { item: field("checkbox-list", "checks", { meta: { options: { one: "One" } } }), value: ["one"], selector: 'input[type="checkbox"]', expected: "true" },
            { item: field("date", "date"), value: "2026-10-10", selector: 'input[type="date"]', expected: "2026-10-10" },
            { item: field("time", "time"), value: "12:30", selector: 'input[type="time"]', expected: "12:30" },
            { item: field("datetime", "datetime"), value: "2026-10-10T12:30", selector: 'input[type="datetime-local"]', expected: "2026-10-10T12:30" },
            { item: field("file", "file"), value: "uploads/old.txt", selector: "p", expected: "Current file: uploads/old.txt" },
        ];

        for (const testCase of cases) {
            const wrapper = mount(fieldRenderers[testCase.item.type] as any, {
                props: { field: testCase.item, fieldId: `id-${testCase.item.name}`, modelValue: testCase.value },
            });
            const target = wrapper.get(testCase.selector);
            const actual = target.element instanceof HTMLInputElement && target.element.type === "checkbox"
                ? String((target.element as HTMLInputElement).checked)
                : target.element instanceof HTMLInputElement || target.element instanceof HTMLTextAreaElement || target.element instanceof HTMLSelectElement
                    ? target.element.value
                    : target.text();
            expect(actual).toBe(testCase.expected);
        }
    });

    it.each(rendererCases)("renders validation errors for %s", (type) => {
        const component = fieldRenderers[type] as any;
        const wrapper = mount(component, {
            props: {
                field: field(type, `field-${type}`, { meta: type === "select" ? { options: { one: "One" } } : {} }),
                fieldId: `id-${type}`,
                errorId: `error-${type}`,
                error: "This field is invalid.",
                modelValue: type === "checkbox-list" ? [] : "",
            },
        });

        expect(wrapper.get('[role="alert"]').text()).toBe("This field is invalid.");
    });

    it("supports text and textarea two-way value updates", async () => {
        for (const [type, component, selector] of [
            ["text", TextInput, "input"],
            ["textarea", TextareaInput, "textarea"],
        ] as const) {
            const wrapper = mount(component, {
                props: { modelValue: "before", field: field(type, type), fieldId: `id-${type}` },
            });
            await wrapper.get(selector).setValue("after");
            expect(wrapper.emitted("update:modelValue")?.[0]).toEqual(["after"]);
        }
    });

    it("normalizes select and checkbox values to strings", async () => {
        const select = mount(SelectInput, {
            props: {
                modelValue: 2,
                field: field("select", "role", { meta: { options: { 2: "Editor" } } }),
                fieldId: "role-id",
            },
        });
        await select.get("select").setValue("2");
        expect(select.emitted("update:modelValue")?.[0]).toEqual(["2"]);

        const checkboxes = mount(CheckboxListInput, {
            props: {
                modelValue: [],
                field: field("checkbox-list", "tags", { meta: { options: { 1: "One", 2: "Two" } } }),
                fieldId: "tags-id",
            },
        });
        await checkboxes.findAll('input[type="checkbox"]')[0].setValue(true);
        expect(checkboxes.emitted("update:modelValue")?.[0]).toEqual([["1"]]);
    });

    it("recursively renders nested grids and preserves field values and errors", async () => {
        const schema: FormSchemaItem[] = [
            {
                type: "grid",
                columns: 2,
                column_span: "full",
                schema: [
                    field("text", "first", { column_span: 1 }),
                    {
                        type: "grid",
                        columns: 3,
                        column_span: 2,
                        schema: [field("textarea", "nested")],
                    },
                ],
            },
        ];
        const wrapper = mount(SchemaRenderer, {
            props: {
                schema,
                values: { first: "one", nested: "two" },
                errors: { nested: "Nested error" },
            },
        });

        expect(wrapper.findAll(".form-grid")).toHaveLength(2);
        expect(wrapper.get("input").element.value).toBe("one");
        expect(wrapper.get("textarea").element.value).toBe("two");
        expect(wrapper.text()).toContain("Nested error");

        await wrapper.get("textarea").setValue("changed");
        expect(wrapper.emitted("update:field")?.at(-1)).toEqual(["nested", "changed"]);
        expect(wrapper.findAll(".form-grid")[1].attributes("style")).toContain("span 2 / span 2");
    });

    it("uses native date, time, and datetime controls with valid browser values", () => {
        const date = mount(DateTimeInput, {
            props: { modelValue: "2026-10-10", field: field("date", "date"), fieldId: "date-id" },
        });
        const datetime = mount(DateTimeInput, {
            props: { modelValue: "2026-10-10 12:30:45", field: field("datetime", "at"), fieldId: "at-id" },
        });
        expect(date.get("input").attributes("type")).toBe("date");
        expect(date.get("input").element.value).toBe("2026-10-10");
        expect(datetime.get("input").attributes("type")).toBe("datetime-local");
        expect(datetime.get("input").element.value).toBe("2026-10-10T12:30");
    });

    it("emits browser File values and keeps rich-text values as untrusted textarea text", async () => {
        const file = new File(["contents"], "avatar.txt", { type: "text/plain" });
        const upload = mount(FileInput, {
            props: { modelValue: null, field: field("file", "avatar"), fieldId: "avatar-id" },
        });
        const input = upload.get('input[type="file"]');
        Object.defineProperty(input.element, "files", { value: { item: () => file } });
        await input.trigger("change");
        expect(upload.emitted("update:modelValue")?.[0]).toEqual([file]);

        const fixtures = [
            '<script>alert(1)</script>',
            '<img src="x" onerror="alert(1)">',
            '<a href="javascript:alert(1)">link</a>',
            '<svg><g onload="alert(1)"></g></svg>',
            '<div><b>malformed',
            '&lt;script&gt;alert(1)&lt;/script&gt;',
            '<p><strong>ordinary formatting</strong></p>',
        ];

        for (const fixture of fixtures) {
            const richText = mount(RichTextEditorInput, {
                props: {
                    modelValue: fixture,
                    field: field("rich-text-editor", "content"),
                    fieldId: "content-id",
                },
            });
            expect(richText.find("script, img, a, svg, strong").exists()).toBe(false);
            expect((richText.get("textarea").element as HTMLTextAreaElement).value).toBe(fixture);
            richText.unmount();
        }
    });

    it("clears a selected browser file when the form adopts an existing server path", async () => {
        const wrapper = mount(FileInput, {
            props: { modelValue: null, field: field("file", "attachment"), fieldId: "attachment-id" },
        });
        const input = wrapper.get('input[type="file"]');
        Object.defineProperty(input.element, "value", {
            configurable: true,
            writable: true,
            value: "C:\\fakepath\\new-upload.txt",
        });

        await wrapper.setProps({ modelValue: "uploads/current.txt" });

        expect((input.element as HTMLInputElement).value).toBe("");
    });

    it("keeps existing radio and markdown editor bindings available", async () => {
        const radio = mount(RadioInput, {
            props: {
                modelValue: "a",
                field: field("radio", "choice", { meta: { options: { a: "A", b: "B" }, columns: { default: 1, md: 2 } } }),
                fieldId: "choice-id",
            },
        });
        await radio.findAll('input[type="radio"]')[1].setValue();
        expect(radio.emitted("update:modelValue")?.[0]).toEqual(["b"]);

        const markdown = mount(MarkdownEditorInput, {
            props: {
                modelValue: "# Title",
                field: field("markdown-editor", "markdown", { meta: { preview: true } }),
                fieldId: "markdown-id",
            },
        });
        expect(markdown.get("textarea").element.value).toBe("# Title");
        expect(markdown.get("pre").text()).toBe("# Title");
        expect(markdown.find("h1").exists()).toBe(false);
    });

    it("updates form values through the root renderer and spoofs file update methods for Laravel", async () => {
        const submit = vi.fn((method: string, url: string, _options: unknown) => {});
        inertiaMock.submit = submit;
        const textForm = mount(InertiaForm, {
            props: {
                formPayload: { schema: [field("text", "name")], defaults: { name: "Ada" }, action: "/users" },
            },
        });
        await textForm.get("input").setValue("Grace");
        expect(inertiaMock.form.name).toBe("Grace");

        const fileForm = mount(InertiaForm, {
            props: {
                formPayload: { schema: [field("file", "avatar")], defaults: { avatar: null }, action: "/users/1", method: "put" },
            },
        });
        const file = new File(["data"], "avatar.txt", { type: "text/plain" });
        const input = fileForm.get('input[type="file"]');
        Object.defineProperty(input.element, "files", { value: { item: () => file } });
        await input.trigger("change");
        await fileForm.get("form").trigger("submit");

        expect(submit).toHaveBeenCalledWith("post", "/users/1", expect.objectContaining({
            data: { avatar: file, _method: "put" },
        }));
    });

    it("updates defaults for a clean form when the same record payload changes", async () => {
        const wrapper = mount(InertiaForm, {
            props: { formPayload: { schema: [field("text", "name")], defaults: { name: "Ada" }, action: "/users/1" } },
        });

        await wrapper.setProps({ formPayload: { schema: [field("text", "name")], defaults: { name: "Grace" }, action: "/users/1" } });
        expect(wrapper.get("input").element.value).toBe("Grace");
    });

    it("preserves dirty values when defaults change for the same form identity", async () => {
        const wrapper = mount(InertiaForm, {
            props: { formPayload: { schema: [field("text", "name")], defaults: { name: "Ada" }, action: "/users/1" } },
        });

        await wrapper.get("input").setValue("Local edit");
        await wrapper.setProps({ submitLabel: "Save profile" });
        expect(wrapper.get("input").element.value).toBe("Local edit");
        await wrapper.setProps({ formPayload: { schema: [field("text", "name")], defaults: { name: "Server update" }, action: "/users/1" } });
        expect(wrapper.get("input").element.value).toBe("Local edit");
    });

    it("uses an explicit form key as the record identity", async () => {
        const wrapper = mount(InertiaForm, {
            props: {
                formPayload: { schema: [field("text", "name")], defaults: { name: "Ada" }, action: "/users" },
                formKey: "user:1",
            },
        });

        await wrapper.setProps({
            formPayload: { schema: [field("text", "name")], defaults: { name: "Grace" }, action: "/users" },
            formKey: "user:2",
        });
        expect(wrapper.get("input").element.value).toBe("Grace");
    });

    it("requires explicit confirmation before replacing a dirty form identity", async () => {
        const submit = vi.fn();
        inertiaMock.submit = submit;
        const wrapper = mount(InertiaForm, {
            props: { formPayload: { schema: [field("text", "name")], defaults: { name: "Ada" }, action: "/users/1" } },
        });

        await wrapper.get("input").setValue("Unsaved edit");
        await wrapper.setProps({ formPayload: { schema: [field("text", "name")], defaults: { name: "Grace" }, action: "/users/2" } });

        expect(wrapper.text()).toContain("unsaved changes are preserved");
        expect(wrapper.get("input").element.value).toBe("Unsaved edit");
        await wrapper.get('form').trigger("submit");
        expect(submit).not.toHaveBeenCalled();

        await wrapper.get('button[type="button"]').trigger("click");
        expect(wrapper.get("input").element.value).toBe("Grace");
        await wrapper.get('form').trigger("submit");
        expect(submit).toHaveBeenCalledWith("post", "/users/2", expect.anything());
    });

    it.each(["post", "put", "patch", "delete"] as const)("submits %s through Inertia", async method => {
        const submit = vi.fn();
        inertiaMock.submit = submit;
        const wrapper = mount(InertiaForm, {
            props: { formPayload: { schema: [field("text", "name")], defaults: { name: "Ada" }, action: "/users/1", method } },
        });

        await wrapper.get("form").trigger("submit");
        expect(submit).toHaveBeenCalledWith(method, "/users/1", expect.anything());
    });

    it("uses component action and method overrides and rejects invalid submission settings", async () => {
        const submit = vi.fn();
        inertiaMock.submit = submit;
        const wrapper = mount(InertiaForm, {
            props: {
                formPayload: { schema: [field("text", "name")], defaults: { name: "Ada" }, action: "/payload", method: "delete" },
                action: "/override",
                method: "patch",
            },
        });

        await wrapper.get("form").trigger("submit");
        expect(submit).toHaveBeenCalledWith("patch", "/override", expect.anything());

        const missing = mount(InertiaForm, {
            props: { formPayload: { schema: [], defaults: {} } },
        });
        await missing.get("form").trigger("submit");
        expect(missing.get('[role="alert"]').text()).toContain("no action URL");

        const invalid = mount(InertiaForm, {
            props: { formPayload: { schema: [], defaults: {}, action: "/users", method: "trace" } },
        });
        await invalid.get("form").trigger("submit");
        expect(invalid.get('[role="alert"]').text()).toContain("unsupported method");
    });

    it("prevents duplicate submissions while processing and clears its lock on finish", async () => {
        const submit = vi.fn();
        inertiaMock.submit = submit;
        const wrapper = mount(InertiaForm, {
            props: { formPayload: { schema: [], defaults: {}, action: "/users" } },
        });

        await wrapper.get("form").trigger("submit");
        await wrapper.get("form").trigger("submit");
        expect(submit).toHaveBeenCalledTimes(1);
        expect(wrapper.get('button[type="submit"]').attributes("disabled")).toBeDefined();

        (inertiaMock.form as any).processing = false;
        (inertiaMock.options as any).onFinish();
        await wrapper.vm.$nextTick();
        expect(wrapper.get('button[type="submit"]').attributes("disabled")).toBeUndefined();
    });

    it.each(["put", "patch", "delete"] as const)("spoofs file-bearing %s and never reuses a stale _method", async method => {
        const submit = vi.fn();
        inertiaMock.submit = submit;
        const wrapper = mount(InertiaForm, {
            props: {
                formPayload: { schema: [field("file", "attachment")], defaults: { attachment: null }, action: "/users/1", method },
            },
        });
        const file = new File(["payload"], "file.txt");
        const input = wrapper.get('input[type="file"]');
        Object.defineProperty(input.element, "files", { value: { item: () => file } });
        await input.trigger("change");
        (inertiaMock.form as any)._method = "stale";
        await wrapper.get("form").trigger("submit");

        expect(submit).toHaveBeenCalledWith("post", "/users/1", expect.anything());
        expect((inertiaMock.options as any).errorBag).toBeUndefined();
        expect(inertiaMock.transform?.({ attachment: file, _method: "stale" })).toEqual({ attachment: file, _method: method });
    });

    it("passes named error bags and preserves/focuses nested validation errors", async () => {
        const submit = vi.fn();
        inertiaMock.submit = submit;
        const wrapper = mount(InertiaForm, {
            props: {
                formPayload: { schema: [field("text", "profile.email")], defaults: { "profile.email": "ada@example.com" }, action: "/profile" },
                errorBag: "profileForm",
            },
            attachTo: document.body,
        });

        await wrapper.get("input").setValue("edited@example.com");
        await wrapper.get("form").trigger("submit");
        expect((inertiaMock.options as any).errorBag).toBe("profileForm");
        expect(submit).toHaveBeenCalledWith("post", "/profile", expect.objectContaining({
            data: { profile: { email: "edited@example.com" } },
        }));
        (inertiaMock.form as any).errors["profile.email"] = "Use a valid email.";
        await (inertiaMock.options as any).onError({ "profile.email": "Use a valid email." });
        await wrapper.vm.$nextTick();

        expect(wrapper.get('[role="alert"]').text()).toContain("highlighted errors");
        expect(wrapper.get("input").attributes("aria-describedby")).toContain("error");
        expect((wrapper.get("input").element as HTMLInputElement).value).toBe("edited@example.com");
        expect(document.activeElement).toBe(wrapper.get("input").element);
    });

    it("clears a field error when edited and preserves values after request failures", async () => {
        const submit = vi.fn();
        inertiaMock.submit = submit;
        const wrapper = mount(InertiaForm, {
            props: { formPayload: { schema: [field("text", "name")], defaults: { name: "Ada" }, action: "/users" } },
        });

        (inertiaMock.form as any).setError("name", "Invalid name.");
        await wrapper.get("input").setValue("Grace");
        expect((inertiaMock.form as any).errors.name).toBeUndefined();
        await wrapper.get("form").trigger("submit");
        (inertiaMock.options as any).onNetworkError();
        (inertiaMock.form as any).processing = false;
        (inertiaMock.options as any).onFinish();
        await wrapper.vm.$nextTick();
        expect(wrapper.get("input").element.value).toBe("Grace");
        expect(wrapper.get('[role="alert"]').text()).toContain("could not reach the server");

        (inertiaMock.form as any).processing = false;
        await wrapper.get("form").trigger("submit");
        (inertiaMock.options as any).onHttpException();
        await wrapper.vm.$nextTick();
        expect(wrapper.get('[role="alert"]').text()).toContain("server could not process");
    });

    it("preserves ordinary values after success, clears selected files, and supports reset-on-success", async () => {
        const submit = vi.fn();
        inertiaMock.submit = submit;
        const wrapper = mount(InertiaForm, {
            props: {
                formPayload: {
                    schema: [field("text", "name"), field("text", "password", { meta: { input_type: "password" } }), field("file", "attachment")],
                    defaults: { name: "Ada", password: "", attachment: null },
                    action: "/users",
                },
            },
        });

        await wrapper.get('input[type="text"]').setValue("Grace");
        const file = new File(["payload"], "file.txt");
        const fileInput = wrapper.get('input[type="file"]');
        Object.defineProperty(fileInput.element, "files", { value: { item: () => file } });
        await fileInput.trigger("change");
        await wrapper.get("form").trigger("submit");
        (inertiaMock.options as any).onSuccess({});
        (inertiaMock.form as any).defaults();
        expect((wrapper.get('input[type="text"]').element as HTMLInputElement).value).toBe("Grace");
        expect((wrapper.get('input[type="password"]').element as HTMLInputElement).value).toBe("");
        expect((inertiaMock.form as any).attachment).toBeNull();
        expect((fileInput.element as HTMLInputElement).value).toBe("");

        (inertiaMock.form as any).processing = false;
        (inertiaMock.options as any).onFinish();
        await wrapper.vm.$nextTick();
        await wrapper.setProps({ resetOnSuccess: true });
        await wrapper.get('input[type="text"]').setValue("Changed");
        await wrapper.get("form").trigger("submit");
        (inertiaMock.options as any).onSuccess({});
        (inertiaMock.form as any).processing = false;
        (inertiaMock.options as any).onFinish();
        await wrapper.vm.$nextTick();
        expect((wrapper.get('input[type="text"]').element as HTMLInputElement).value).toBe("Grace");
    });

    it("adopts the new record after a successful create redirect changes the form identity", async () => {
        inertiaMock.submit = vi.fn();
        const wrapper = mount(InertiaForm, {
            props: {
                formPayload: { schema: [field("text", "name")], defaults: { name: "" }, action: "/items", method: "post" },
            },
        });

        await wrapper.get("input").setValue("Created item");
        await wrapper.get("form").trigger("submit");
        await wrapper.setProps({
            formPayload: { schema: [field("text", "name")], defaults: { name: "Created item" }, action: "/items/1", method: "put" },
        });
        expect(wrapper.text()).toContain("unsaved changes are preserved");

        (inertiaMock.options as any).onSuccess({});
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).not.toContain("unsaved changes are preserved");
        expect((wrapper.get("input").element as HTMLInputElement).value).toBe("Created item");
        expect((inertiaMock.form as any).isDirty).toBe(false);
    });

    it("exposes a manual reset that restores defaults and clears errors", async () => {
        const wrapper = mount(InertiaForm, {
            props: {
                formPayload: {
                    schema: [field("text", "name"), field("text", "password", { meta: { input_type: "password" } })],
                    defaults: { name: "Ada", password: "" },
                    action: "/users",
                },
            },
        });
        await wrapper.get("input").setValue("Grace");
        await wrapper.get('input[type="password"]').setValue("secret");
        (inertiaMock.form as any).setError("name", "Invalid name.");

        (wrapper.vm as any).reset();
        await wrapper.vm.$nextTick();
        expect(wrapper.get("input").element.value).toBe("Ada");
        expect((wrapper.get('input[type="password"]').element as HTMLInputElement).value).toBe("");
        expect(Object.keys((inertiaMock.form as any).errors)).toHaveLength(0);
    });
});
