<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">Settings</h2>
                <div class="flex justify-end items-center">
                    <a-select
                        v-model:value="selected_year"
                        :options="years"
                        :field-names="{ label: 'end', value: 'id' }"
                        filterOption="true"
                        optionFilterProp="name"
                        mode="single"
                        placeholder="Please select"
                        showArrow
                        @change="yrch"
                        class="w-1/2"
                    />
                    <a-select
                        v-model:value="selected"
                        show-search
                        filterOption="true"
                        optionFilterProp="name"
                        :options="options"
                        :field-names="{ label: 'name', value: 'id' }"
                        mode="single"
                        placeholder="Please select"
                        showArrow
                        @change="coch"
                        class="w-1/2"
                    />
                </div>
            </div>
        </template>

        <div
            class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700"
        >
            <a-form
                :form="form"
                @submit.prevent="submit"
                :label-col="{ span: 4 }"
                :wrapper-col="{ span: 14 }"
            >
                <div class="mb-5">
                    <label
                        for="countries"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        >Account Profile and loss</label
                    >

                    <a-select
                        v-if="this.setting"
                        v-model:value="form.account_id"
                        :options="accounts"
                        show-search
                        :field-names="{ label: 'name', value: 'id' }"
                        filterOption="true"
                        :disabled="true"
                        optionFilterProp="name"
                        mode="single"
                        placeholder="Please select"
                        showArrow
                        class="w-full"
                    />
                    <a-select
                        v-else
                        v-model:value="form.account_id"
                        :options="accounts"
                        show-search
                        :field-names="{ label: 'name', value: 'id' }"
                        filterOption="true"
                        optionFilterProp="name"
                        mode="single"
                        placeholder="Please select"
                        showArrow
                        class="w-full"
                    />
                    <div
                        class="text-red-700 px-4 py-2"
                        role="alert"
                        v-if="errors.account_id"
                    >
                        {{ errors.account_id }}
                    </div>
                </div>

                <button
                    v-if="!this.setting"
                    type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                >
                    Save
                </button>
            </a-form>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import { useForm } from "@inertiajs/inertia-vue3";
// import Multiselect from "@suadelabs/vue3-multiselect";
// import Treeselect from "vue3-treeselect";
// import "vue3-treeselect/dist/vue3-treeselect.css";
import { Form, Input, Button, Select, Modal } from "ant-design-vue";

export default {
    components: {
        "a-select": Select,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-button": Button,
        AppLayout,
        useForm,
        Modal,
    },

    props: {
        companies: Object,
        company: Object,
        years: Object,
        year: Object,
        errors: Object,
        data: Object,
        accounts: Array,
        setting: Array,
    },

    data() {
        return {
            accounts: this.accounts,
            setting: this.setting,
            co_id: this.company,
            options: this.companies,
            selected: this.company.name,
            years: this.years,
            selected_year: this.year.name,
        };
    },
    setup(props) {
        const form = useForm({
            account_id: props.setting
                ? props.setting.value
                : props.accounts[0].id,
        });

        return { form };
    },
    methods: {
        submit() {
            Modal.confirm({
                title: "Do you really want to Profile & Loss Accounts?",
                onOk: () => {
                    this.$inertia.post(route("settings.store"), this.form);
                },
                onCancel: () => {},
            });
        },
    },
    //   data() {
    //     return {
    //       form: {
    //         name: "",
    //         email: "",
    //       },
    //     };
    //   },
};

// export default {
//   components: {
//     AppLayout,
//     Multiselect,
//     Treeselect,
//     Form,
//     FormItem,
//     Select,
//     SelectOption,
//     Input,
//   },

//   props: {
//     errors: Object,
//     data: Object,
//     groups: Array,
//     group_first: Object,
//   },

//   data() {
//     return {
//       option: this.groups,
//       normalizer(node) {
//         return {
//           label: node.name,
//         };
//       },
//     };
//   },
//   setup(props) {
//     const form = useForm({
//       name: null,
//       number: null,
//       group: props.groups_first,
//     });

//     return { form };
//   },

//   //   data() {
//   //     return {
//   //       form: this.$inertia.form({
//   //         name: null,
//   //         number: null,
//   //         group: this.group_first.id,
//   //       }),
//   //     };
//   //   },

//   //   methods: {
//   //     submit() {
//   //       this.$inertia.post(route("accounts.store"), this.form);
//   //     },
//   //   },
// };
</script>
