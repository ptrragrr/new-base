<template>
    <div>
        <select
            class="form-control form-select"
            :id="id"
            :name="name"
            :disabled="disabled"
            :required="required"
        ></select>
    </div>
</template>

<script>
import select2 from "select2/dist/js/select2.full.js";
select2();
import "select2/dist/css/select2.min.css";

export default {
    name: "Select2",
    data() {
        return {
            select2: null,
        };
    },
    emits: ["update:modelValue", "select"],
    props: {
        modelValue: [String, Array, Number], // previously was `value: String`
        id: {
            type: String,
            default: "",
        },
        name: {
            type: String,
            default: "",
        },
        placeholder: {
            type: String,
            default: "",
        },
        options: {
            type: Array,
            default: () => [],
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        required: {
            type: Boolean,
            default: false,
        },
        settings: {
            type: Object,
            default: () => {},
        },
        class: {
            type: String,
            default: "",
        },
    },
    watch: {
        options: {
            handler(val) {
                this.setOption(val);
            },
            deep: true,
        },
        modelValue: {
            handler(val) {
                this.setValue(val);
            },
            deep: true,
        },
    },
    methods: {
        setOption(val = []) {
            this.select2?.empty();
            this.select2?.select2({
                ...this.settings,
                placeholder: this.placeholder,
                containerCssClass: `form-select ${this.class}`,
                data: val,
            });
            this.setValue(this.modelValue);
            this.select2
                ?.data()
                .select2.$selection.addClass(`form-select ${this.class}`);
        },
        setValue(val) {
            if (val instanceof Array) {
                this.select2?.val([...val]);
            } else {
                this.select2?.val([val]);
            }
            this.select2?.trigger("change");
        },
    },
    mounted() {
        console.log(this.options)
        this.select2 = $(this.$el)
            .find("select")
            .select2({
                ...this.settings,
                placeholder: this.placeholder,
                containerCssClass: `form-select ${this.class}`,
                data: this.options,
            })
            .on("select2:select select2:unselect", (ev) => {
                this.$emit("update:modelValue", this.select2.val());
                this.$emit("select", ev["params"]["data"]);
            });
        this.setValue(this.modelValue);
        this.select2
            ?.data()
            .select2.$selection.addClass(`form-select ${this.class}`);
    },
    beforeUnmount() {
        this.select2?.select2("destroy");
    },
};
</script>
