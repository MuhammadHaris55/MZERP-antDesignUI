<template>
    <app-layout>
        <template #header>
            <h2 class="header font-semibold text-lg text-white p-4">Create Company</h2>
        </template>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
            <div class="">
                <a-form :form="form" @submit.prevent="form.post(route('companies.store'))" :label-col="{ span: 4 }"
                    :wrapper-col="{ span: 14 }">
                    <a-form-item label="Name">
                        <a-input v-model:value="form.name" placeholder="Enter your name" />


                        <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.name">
                            {{ errors.name }}
                        </div>
                    </a-form-item>

                    <a-form-item label="Address ">
                        <a-textarea v-model:value="form.address" placeholder="Enter your address" />

                        <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.address">
                            {{ errors.address }}
                        </div>
                    </a-form-item>

                    <a-form-item label="Email">
                        <a-input v-model:value="form.email" placeholder="Enter your email" />

                        <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.email">
                            {{ errors.email }}
                        </div>
                    </a-form-item>

                    <a-form-item label="Web Address">
                        <a-input v-model:value="form.web" placeholder="Enter your web" />

                        <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.web">
                            {{ errors.web }}
                        </div>
                    </a-form-item>
                    <a-form-item label="Phone No">
                        <a-input v-model:value="form.phone" placeholder="Enter your phone" />

                        <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.phone">
                            {{ errors.phone }}
                        </div>
                    </a-form-item>
                    <a-form-item label="Fiscal">
                        <a-select v-model:value="form.fiscal" :options="fiscals"
                            :field-names="{ label: 'name', value: 'name' }" optionFilterProp="name" mode="single"
                            placeholder="Please select" showArrow class="w-full" />

                        <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.fiscal">
                            {{ errors.fiscal }}
                        </div>
                    </a-form-item>
                    <a-form-item label="Incorp">
                        <!-- <a-datePicker v-model:value="form.incorp" /> -->
                        <a-input
                        v-model:value="form.incorp"
                        type="date"
                        />
                        <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.incorp">
                            {{ errors.incorp }}
                        </div>
                    </a-form-item>
                    <a-form-item class="text-right">
                        <a-button type="primary" :disabled="form.processing" htmlType="submit">Submit</a-button>
                    </a-form-item>
                </a-form>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import { useForm } from "@inertiajs/inertia-vue3";
import {
    Form,
    Input,
    Button,
    Select,
    TreeSelect,
    DatePicker,
} from "ant-design-vue";

export default {
    components: {
        AppLayout,
        useForm,
        "a-tree-select": TreeSelect,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-textarea": Input.TextArea,
        "a-button": Button,
        "a-select": Select,
        "a-datePicker": DatePicker,
    },

    props: {
        errors: Object,
        // fiscals: Object,
        fiscal_first: Object,
    },

    data() {
        return {
            fiscals: [
                { name: "June" },
                { name: "July" },
                { name: "September" },
                { name: "December" },
            ],
        };
    },

    setup(props) {
        const form = useForm({
            name: null,
            address: null,
            email: null,
            web: null,
            phone: null,
            fiscal: "June",
            incorp: null,
        });
        return { form };
    },


    methods: {
        submitForm() {
            //   console.log(this.form);
            this.$inertia.post(route("companies.store"), this.form);
            // Send form data to server using axios or fetch
        },
        //     submit() {
        //       this.$inertia.post(route("companies.store"), this.form);
        //     },
    },
};
</script>
