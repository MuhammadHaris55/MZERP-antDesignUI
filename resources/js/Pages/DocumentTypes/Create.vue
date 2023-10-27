<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-lg text-white p-4">Create Voucher</h2>
        </template>
        <!-- <div
      v-if="$page.props.flash.success"
      class="bg-yellow-600 text-white text-center"
    >
      {{ $page.props.flash.success }}
    </div> -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
            <div class="">
                <a-form
                    @submit.prevent="form.post(route('documenttypes.store'))"
                    :label-col="{ span: 4 }"
                    :wrapper-col="{ span: 14 }"
                >
                    <a-form-item label="Voucher Name :">
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

                    <a-form-item class="text-right">
                        <a-button
                            type="primary"
                            :disabled="form.processing"
                            htmlType="submit"
                            >Create Voucher</a-button
                        >
                    </a-form-item>
                    <!-- <div class="px-4 py-2 flex justify-center items-center">
            <button
              class="
                border
                rounded-xl
                shadow-md
                p-1
                px-4
                mt-1
                bg-gray-800
                text-white
                ml-2
                inline-block
                hover:bg-gray-700 hover:text-white
              "
              type="submit"
              :disabled="form.processing"
            >
              Create Voucher
            </button>
          </div> -->
                </a-form>
                <!-- <Form :v-model="form" @finish="onSubmit" @finishFailed="onError">
          <FormItem
            label="Username"
            name="username"
            :rules="[{ required: true, message: 'Please input voucher name!' }]"
          >
            <Input v-model:value="form.name" size="small" />
          </FormItem>
          <FormItem>
            <Button type="primary" html-type="submit">Create Voucher</Button>
          </FormItem>
        </Form> -->
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import { useForm } from "@inertiajs/inertia-vue3";
import FlashMessage from "@/Layouts/FlashMessage";
import { Form, FormItem, Input, Button } from "ant-design-vue";
import "ant-design-vue/dist/antd.css";

export default {
    components: {
        AppLayout,
        FlashMessage,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-button": Button,
    },

    props: {
        errors: Object,
    },

    setup() {
        const form = useForm({
            name: null,
        });
        return { form };
    },

    //   data() {
    //     return {
    //       form: this.$inertia.form({
    //         name: null,
    //       }),
    //     };
    //   },

    methods: {
        //         submit() {
        //             const response = await fetch("https://some.api/process-form", {
        //     method: "POST",
        //     body: _formData,
        //   },),},
        //     submit() {
        //       this.$inertia.post(route("documenttypes.store"), this.form);
        //     },
    },
};
</script>
