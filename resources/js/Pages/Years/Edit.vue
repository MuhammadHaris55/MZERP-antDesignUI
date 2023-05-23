<template>
    <app-layout>
        <template #header>
            <h2 class="header">Year</h2>
        </template>
        <div class="">
            <a-form :form="form" @submit.prevent="submit" :label-col="{ span: 4 }" :wrapper-col="{ span: 14 }">
                <a-form-item label="Begin Date">
                    <a-input type="date" class="w-full" v-model:value="form.begin" />

                    <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.begin">
                        {{ errors.begin }}
                    </div>
                </a-form-item>

                <a-form-item label="End Date">
                    <a-input class="w-full" type="date" v-model:value="form.end" />

                    <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.end">
                        {{ errors.end }}
                    </div>
                </a-form-item>
                <!-- :wrapper-col="{ span: 14, offset: 4 }" -->
                <a-form-item class="text-right">
                    <a-button type="primary" @click="submit">Update Year</a-button>
                </a-form-item>
            </a-form>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import { Form, Input, Button, } from "ant-design-vue";

export default {
    components: {
        AppLayout,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-button": Button,
    },

    props: {
        errors: Object,
        year: Object,
    },

    data() {
        return {
            form: {
                // begin: moment(this.year.begin, "YYYY-MM-DD"),
                // end: moment(this.year.end, "YYYY-MM-DD"),
                begin: this.year.begin
                    ? this.year.begin
                    : null,
                end: this.year.end
                    ? this.year.end
                    : null,
            },
        };
    },

    methods: {
        submit() {
            this.$inertia.put(route("years.update", this.year.id), this.form);
        },
    },
};
</script>
