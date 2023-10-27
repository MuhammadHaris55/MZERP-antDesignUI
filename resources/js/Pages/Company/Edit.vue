<template>
    <app-layout>
        <template #header>
            <h2 class="header">Edit Company</h2>
        </template>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
            <div class="">
                <a-form
                    :form="form"
                    @submit.prevent="submit"
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
                    <a-form-item label="Address ">
                        <a-textarea
                            v-model:value="form.address"
                            placeholder="Enter your address"
                        />

                        <div
                            class="text-red-700 px-4 py-2"
                            role="alert"
                            v-if="errors.address"
                        >
                            {{ errors.address }}
                        </div>
                    </a-form-item>

                    <a-form-item label="Email">
                        <a-input
                            v-model:value="form.email"
                            placeholder="Enter your email"
                        />

                        <div
                            class="text-red-700 px-4 py-2"
                            role="alert"
                            v-if="errors.email"
                        >
                            {{ errors.email }}
                        </div>
                    </a-form-item>

                    <a-form-item label="Web Address">
                        <a-input
                            v-model:value="form.web"
                            placeholder="Enter your web"
                        />

                        <div
                            class="text-red-700 px-4 py-2"
                            role="alert"
                            v-if="errors.web"
                        >
                            {{ errors.web }}
                        </div>
                    </a-form-item>

                    <a-form-item label="Phone No">
                        <a-input
                            v-model:value="form.phone"
                            placeholder="Enter your phone"
                        />

                        <div
                            class="text-red-700 px-4 py-2"
                            role="alert"
                            v-if="errors.phone"
                        >
                            {{ errors.phone }}
                        </div>
                    </a-form-item>

                    <a-form-item label="Month End">
                        <a-select
                            v-model:value="form.fiscal"
                            :options="fiscals"
                            :field-names="{ label: 'name', value: 'name' }"
                            optionFilterProp="name"
                            mode="single"
                            placeholder="Please select"
                            showArrow
                            class="w-full"
                        />

                        <div
                            class="text-red-700 px-4 py-2"
                            role="alert"
                            v-if="errors.fiscal"
                        >
                            {{ errors.fiscal }}
                        </div>
                    </a-form-item>
                    <a-form-item label="Incorporation Date">
                        <a-input v-model:value="form.incorp" type="date" />
                        <!-- :min="lower"
                           :max="upper" -->
                        <!-- :locale="locale" -->
                        <!-- clearable -->
                        <div
                            class="text-red-700 px-4 py-2"
                            role="alert"
                            v-if="errors.incorp"
                        >
                            {{ errors.incorp }}
                        </div>
                    </a-form-item>

                    <a-form-item class="text-right">
                        <a-button type="primary" @click="submit"
                            >Update Company</a-button
                        >
                    </a-form-item>
                </a-form>
            </div>
        </div>
    </app-layout>
</template>

<script>
// import locale from "ant-design-vue/es/date-picker/locale/en_US";

// import moment, * as moments from "moment";
// import moment from "moment";
import dayjs, { Dayjs } from "dayjs";
import AppLayout from "@/Layouts/AppLayout";
import {
    Form,
    Input,
    Button,
    Select,
    TreeSelect,
    DatePicker,
} from "ant-design-vue";
// import {} from "ant-design-vue/es/date-picker";

export default {
    components: {
        AppLayout,
        "a-tree-select": TreeSelect,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-textarea": Input.TextArea,
        "a-button": Button,
        "a-select": Select,
        "a-date-picker": DatePicker,
        // moment,
        dayjs,
    },

    props: {
        errors: Object,
        types: Object,
        company: Object,
    },

    data() {
        return {
            //   locale: moment.locale("en-us"),

            fiscals: [
                { name: "June" },
                { name: "July" },
                { name: "September" },
                { name: "December" },
            ],

            form: this.$inertia.form({
                name: this.company.name,
                address: this.company.address,
                email: this.company.email,
                web: this.company.web,
                phone: this.company.phone,
                fiscal: this.company.fiscal,
                incorp: this.company.incorp ? this.company.incorp : null,
            }),
        };
    },

    methods: {
        submit() {
            this.$inertia.put(
                route("companies.update", this.company.id),
                this.form
            );
        },
    },
};
</script>
