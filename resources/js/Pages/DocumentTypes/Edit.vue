<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-lg text-white p-4">Edit Voucher</h2>
    </template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
      <div class="">
         <a-form :form="form" @submit.prevent="submit" :label-col="{ span: 4 }" :wrapper-col="{ span: 14 }">
            <a-form-item label="Voucher Name :">
                <a-input v-model:value="form.name" placeholder="Enter your name" />

                <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.name">
                    {{ errors.name }}
                </div>
            </a-form-item>
             <a-form-item class="text-right">
                <a-button type="primary" @click="submit">Update Voucher</a-button>
            </a-form-item>
            <!-- <div class="p-2 mr-2 mb-2 mt-4 ml-6 flex flex-wrap">
            <label class="my-2 mr-8 text-right w-36 font-bold"
              >Account Group :</label
            ><input
              type="text"
              v-model="form.name"
              class="
                pr-2
                pb-2
                w-full
                lg:w-1/4
                rounded-md
                placeholder-indigo-300
              "
              label="name"
              placeholder="Enter Voucher name:"
            />
            <div
              class="
                ml-2
                bg-red-100
                border border-red-400
                text-red-700
                px-4
                py-2
                rounded
                relative
              "
              role="alert"
              v-if="errors.name"
            >
              {{ errors.name }}
            </div>
          </div>
          <div class="px-4 py-2 flex justify-center items-center">
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
            >
              Update Voucher
            </button>
          </div> -->
        </a-form>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import {
    Form,
    Input,
    Button,
} from "ant-design-vue";

export default {
  components: {
    AppLayout,
    "a-form": Form,
    "a-form-item": Form.Item,
    "a-input": Input,
    "a-button": Button
    },

  props: {
    errors: Object,
    documenttype: Object,
  },

  data() {
    return {
      form: this.$inertia.form({
        name: this.documenttype.name,
      }),
    };
  },

  methods: {
    submit() {
      this.$inertia.put(
        route("documenttypes.update", this.documenttype.id),
        this.form
      );
    },
  },
};
</script>
