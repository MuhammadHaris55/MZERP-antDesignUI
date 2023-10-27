<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-lg text-white p-4">
                Create Accounts
            </h2>
        </template>
        <div v-if="$page.props.flash.success" class="bg-yellow-600 text-white">
            {{ $page.props.flash.success }}
        </div>

        <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
      <div class=""> -->
        <a-form
            :form="form"
            @submit.prevent="form.post(route('accounts.store'))"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 14 }"
        >
            <a-form-item label="Name">
                <a-input
                    v-model:value="form.name"
                    placeholder="Enter your name"
                />

                <div
                    class="text-red-700 px-4 py-2"
                    role="alert"
                    v-if="errors.name"
                >
                    {{ errors.name }}
                </div>
            </a-form-item>

            <a-form-item label="Group">
                <!-- filterOption="(input, node) => node.name.toLowerCase().indexOf(input.toLowerCase()) > -1" -->
                <a-tree-select
                    v-model:value="form.group"
                    :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                    :tree-data="option"
                    :field-names="{ label: 'name', value: 'id' }"
                    show-search
                    filterOption="true"
                    treeNodeFilterProp="name"
                    :treeDataSimpleMode="{ pID: 'parent_id' }"
                    placeholder="Please Select Group"
                    tree-default-expand-all
                >
                    <!-- filterOption="true"  -->
                </a-tree-select>
                <div
                    class="text-red-700 px-4 py-2"
                    role="alert"
                    v-if="errors.group"
                >
                    {{ errors.group }}
                </div>
            </a-form-item>

            <a-form-item class="text-right">
                <a-button
                    type="primary"
                    :disabled="form.processing"
                    htmlType="submit"
                    >Create Account</a-button
                >
                <!-- <a-button type="primary" :disabled="form.processing"  @click="submitForm">Submit</a-button> -->
            </a-form-item>
        </a-form>
        <!-- </div>
    </div> -->
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import { useForm } from "@inertiajs/inertia-vue3";
// import Multiselect from "@suadelabs/vue3-multiselect";
// import Treeselect from "vue3-treeselect";
// import "vue3-treeselect/dist/vue3-treeselect.css";
import { Form, Input, Button, TreeSelect } from "ant-design-vue";

export default {
    components: {
        "a-tree-select": TreeSelect,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-button": Button,
        AppLayout,
        useForm,
    },

    props: {
        errors: Object,
        data: Object,
        groups: Array,
        group_first: Object,
    },

    data() {
        return {
            option: this.groups,
        };
    },
    setup(props) {
        const form = useForm({
            name: null,
            //   email: null,
            group: props.groups_first,
        });

        return { form };
    },

    //   data() {
    //     return {
    //       form: {
    //         name: "",
    //         email: "",
    //       },
    //     };
    //   },
    methods: {
        submitForm() {
            //   console.log(this.form);
            this.$inertia.post(route("accounts.store"), this.form);
            // Send form data to server using axios or fetch
        },
    },
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
