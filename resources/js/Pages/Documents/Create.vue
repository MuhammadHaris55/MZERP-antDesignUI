<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-lg text-white p-4">Create Transactions</h2>
    </template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
      <div class="">
        <form
          @submit.prevent="
            this.difference == 0 ? form.post(route('documents.store')) : func()
          "
        >
          <!-- DOCUMENT TYPE ID -->
         <a-form-item label="Select Voucher" :label-col="{ span: 4 }"
                        :wrapper-col="{ span: 14 }">
              <a-select
                v-model:value="form.type_id"
                :options="doc_"
                :field-names="{ label: 'name', value: 'id' }"
                show-search
                filterOption="true"
                optionFilterProp="name"
                mode="single"
                placeholder="Select Voucher"
                showArrow
              />
            <div v-if="errors.type_id">{{ errors.type_id }}</div>
         </a-form-item>

          <a-form-item label="Description :" :label-col="{ span: 4 }"
                    :wrapper-col="{ span: 14 }">
            <a-textarea v-model:value="form.description" placeholder="Enter your Description" />

            <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.description">
                {{ errors.description }}
            </div>
         </a-form-item>

        <a-form-item label="Select Date :" :label-col="{ span: 4 }"
                    :wrapper-col="{ span: 14 }">
                            <!-- <a-datePicker v-model:value="form.incorp" /> -->
            <a-input
            v-model:value="form.date"
            type="date"
            :min="form.start"
            :max="form.end"
            required
            />
            <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.date">
                {{ errors.date }}
            </div>
        </a-form-item>

        <a-form-item v-if="errors.entries" style="color: #c53a2e" label="Error :" :label-col="{ span: 4 }"
                    :wrapper-col="{ span: 14 }">
            <div v-if="errors.entries" class="bg-red-100 border px-4 border-red-400 text-red-700  rounded" role="alert">
                {{ errors.entries }}
            </div>
        </a-form-item>
          <!-- TABLE FOR ENTRIES ---- START ------------- -->
          <div class="panel-body flex justify-center items-start">
            <table class="table flex">
              <thead class="">
                <tr>
                  <th>Account:</th>
                  <th>Debit:</th>
                  <th>Credit:</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, index) in form.entries" :key="entry.id">
                  <td>
                    <a-select
                    style="width:100%"
                    show-search
                    v-model:value="entry.account_id"
                    :options="option"
                    :field-names="{ label: 'name', value: 'id' }"
                    filterOption="true"
                    optionFilterProp="name"
                    mode="single"
                    placeholder="Select Account"
                    showArrow
                    />


                    <div class="text-red-700 px-4" role="alert" v-if="errors['entries.' + index + '.account_id']">
                                {{ errors['entries.' + index + '.account_id'] }}
                    </div>
                </td>
                  <td>
                    <a-input
                      v-model:value="entry.debit"
                      type="number"
                      @change="debitchange(index)"
                      class="rounded-md w-36"
                    />
                    <div class="text-red-700 px-4" role="alert" v-if="errors['entries.' + index + '.account_id']">
                    {{ ' - ' }}
                    </div>
                  </td>
                  <td>
                    <a-input
                      v-model:value="entry.credit"
                      type="number"
                      @change="creditchange(index)"
                      class="rounded-md w-36"
                    />
                    <div class="text-red-700 px-4" role="alert" v-if="errors['entries.' + index + '.account_id']">
                    {{ ' - ' }}
                    </div>
                </td>
                  <td  v-if="index > 0">
                    <a-button
                      @click.prevent="deleteRow(index)"
                      v-if="index > 1"
                      type="danger"
                    >
                      Delete
                    </a-button>
                     <div class="text-red-700 px-4" role="alert" v-if="errors['entries.' + index + '.account_id']">
                        {{ ' - ' }}
                    </div>
                    <a-button
                      v-else-if="index == 1"
                      type="primary"
                      @click.prevent="addRow"
                      ghost
                      >
                      Add Row
                    </a-button>
                    <div v-else></div>
                  </td>

                </tr>
                <tr>
                  <th>Difference:</th>
                  <th>Debit:</th>
                  <th>Credit:</th>
                  <th></th>
                </tr>

                <tr>
                  <td>
                    <a-input
                      type="number"
                      v-model:value="difference"
                      readonly
                      class="rounded-md w-full"
                    />
                  </td>
                  <td>
                    <a-input
                      type="number"
                      v-model:value="debit"
                      readonly
                      class="rounded-md w-36"
                    />
                  </td>
                  <td>
                    <a-input
                      type="number"
                      v-model:value="credit"
                      readonly
                      class="rounded-md w-36"
                    />
                  </td>
                  <td></td>
                </tr>
              </tbody>
            </table>

            <div v-if="isError">{{ firstError }}</div>
          </div>
          <!-- TABLE FOR ENTRIES ---- END ------------- -->
          <div class="px-4 py-2 flex justify-center items-center">
             <a-button type="primary" :disabled="form.processing" htmlType="submit">  Create Transaction
            </a-button>
          </div>
        </form>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import { useForm } from "@inertiajs/inertia-vue3";
// import Label from "../../Jetstream/Label.vue";
import Datepicker from "vue3-datepicker";
import format from "date-fns/format";
import Multiselect from "@suadelabs/vue3-multiselect";
import {
    Form,
    Input,
    Button,
    Select,
} from "ant-design-vue";


export default {
  components: {
    AppLayout,
    Datepicker,
    Multiselect,
    format,
    "a-form": Form,
    "a-form-item": Form.Item,
    "a-input": Input,
    "a-textarea": Input.TextArea,
    "a-button": Button,
    "a-select": Select,
  },

  props: {
    errors: Object,

    doc_types: Object,
    doc_type_first: Object,

    // accounts: Object,
    accounts: Object,
    account_first: Object,
    min_start: Object,
    max_end: Object,
  },

  data() {
    return {
      option: this.accounts,
      doc_: this.doc_types,
      difference: null,
      credit: 0,
      debit: 0,
      total: 0,
      isError: null,
    };
  },

  setup(props) {
    const form = useForm({
      type_id: props.doc_types[0].id,
      date: null,
      description: null,

      entries: [
        {
          account_id: props.accounts[0].id,
          debit: 0,
          credit: 0,
        },
        {
          account_id: props.accounts[1].id,
          debit: 0,
          credit: 0,
        },
      ],
      start: props.min_start
        ? props.min_start
        : new Date().toISOString().substr(0, 10),
      end: props.max_end
        ? props.max_end
        : new Date().toISOString().substr(0, 10),
    });

    return { form };
  },

  methods: {
    func() {
      alert("Please fill all Input fields Diffrence not equal to 0");
    },
    //ON CHANGE FUNCTION ON DEBIT CREDIT TO NULL THE PARALLEL VALUES ---START ----
    debitchange(index) {
      let a = this.form.entries[index];
      a.credit = 0;
      console.log(a.debit);

      this.tdebit();
      this.tcredit();
    },
    creditchange(index) {
      let b = this.form.entries[index];
      b.debit = 0;
      console.log(b.credit);

      this.tcredit();
      this.tdebit();
    },
    //ON CHANGE FUNCTION ON DEBIT CREDIT TO NULL THE PARALLEL VALUES --- END ----

    // CALCULATING TOTAL AMOUNT OF DEBIT AND CREDIT ----START ----------------
    tcredit() {
      let dtotal = 0;
      for (var i = 0; i < this.form.entries.length; i++) {
        dtotal = dtotal + parseInt(this.form.entries[i].credit);
      }
      this.credit = dtotal;
    },
    tdebit() {
      let dtotal = 0;
      for (var i = 0; i < this.form.entries.length; i++) {
        dtotal = dtotal + parseInt(this.form.entries[i].debit);
      }
      this.debit = dtotal;
    },
    // CALCULATING TOTAL AMOUNT OF DEBIT AND CREDIT ---- END ----------------

    addRow() {
      this.form.entries.push({
        // account_id: this.account_first.id,
        account_id: this.accounts,
        debit: 0,
        credit: 0,
      });
    },
    deleteRow(index) {
     this.creditchange(index);
     this.debitchange(index);
     this.form.entries.splice(index, 1);
    },
  },
  watch: {
    //  FOR DIFFERENCE OF DEBIT CREDIT ---START -----
    debit: function () {
      let diff = 0;
      if (this.debit == 0 && this.credit == 0) {
        this.difference = null;
      } else {
        diff = parseInt(this.debit) - parseInt(this.credit);
        this.difference = diff;
      }
    },
    credit: function () {
      let diff = 0;
      console.log(this.credit);
      if (this.debit == 0 && this.credit == 0) {
        this.difference = null;
      } else {
        diff = parseInt(this.debit) - parseInt(this.credit);
        this.difference = diff;
      }
    },
    //  FOR DIFFERENCE OF DEBIT CREDIT --- END -----
  },
};
</script>
