<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-lg text-white p-4">Edit Transactions</h2>
    </template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
      <div class="">
        <form @submit.prevent="submit">
          <!-- DOCUMENT TYPE ID -->
           <a-form-item label="Select Voucher :" :label-col="{ span: 4 }"
                            :wrapper-col="{ span: 14 }">
            <a-input
                  type="text"
                  v-model:value="document.type_name"
                  disabled
                  label="ref"
                  placeholder="Enter Voucher"
                />
             </a-form-item>

             <a-form-item label="Reference :" :label-col="{ span: 4 }"
                                :wrapper-col="{ span: 14 }">
                <a-input
                      type="text"
                      v-model:value="document.ref"
                      disabled
                      label="ref"
                      placeholder="Enter Voucher"
                    />
                 </a-form-item>

          <!-- DESCRIPTION -->
            <a-form-item label="Description :" :label-col="{ span: 4 }"
                        :wrapper-col="{ span: 14 }">
                <a-textarea :disabled="can['edit'] ? disabled : ''" v-model:value="form.description" placeholder="Enter your Description" />

                <div class="text-red-700 px-4 py-2" role="alert" v-if="errors.description">
                    {{ errors.description }}
                </div>
            </a-form-item>

          <!-- DESCRIPTION -->
          <!-- DATE -->

            <a-form-item label="Select Date :" :label-col="{ span: 4 }"
                        :wrapper-col="{ span: 14 }">
                <a-input
                v-model:value="form.date"
                type="date"
                :min="form.start"
                :max="form.end"
                required
                :disabled="can['edit'] ? disabled : ''"
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
                    :disabled="can['edit'] ? disabled : ''"
                    showArrow
                    />

                  </td>
                  <td>
                     <a-input
                          v-model:value="entry.debit"
                          type="number"
                          @change="debitchange(index)"
                          class="rounded-md w-36"
                         :disabled="can['edit'] ? disabled : ''"
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
                        :disabled="can['edit'] ? disabled : ''"
                        />
                        <div class="text-red-700 px-4" role="alert" v-if="errors['entries.' + index + '.account_id']">
                        {{ ' - ' }}
                        </div>
                  </td>
                  <td>
                    <a-button
                      @click.prevent="deleteRow(index)"
                      v-if="index > 1 && can['edit']"
                      type="danger"

                    >
                      Delete
                    </a-button>
                    <a-button
                      v-else-if="index == 1 && can['edit']"

                      type="primary"
                      ghost
                      @click.prevent="addRow"
                    >
                      Add row
                    </a-button>
                  </td>
                </tr>

                <tr v-if="can['edit']">
                  <th>Difference:</th>
                  <th>Debit:</th>
                  <th>Credit:</th>
                  <th></th>
                </tr>

                <tr v-if="can['edit']">
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

          <div
            v-if="can['edit']"
            class="px-4 py-2 flex justify-center items-center"
          >
             <a-button type="primary" :disabled="form.processing" @click="submit()">  Update Transaction</a-button>

          </div>
        </form>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
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
    format,
    Multiselect,
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

    document: Object,
    entriess: Object,

    accounts: Object,
    account_first: Object,

    min_start: Object,
    max_end: Object,

    can: Object,
  },

  data() {
    return {
      option: this.accounts,

      difference: 0,
      credit: 0,
      debit: 0,
      total: 0,
      //   count: entries.length,
      isError: null,
      form: this.$inertia.form({
        // entries: this.entries,
        start: this.min_start
          ? this.min_start
          : new Date().toISOString().substr(0, 10),
        end: this.max_end
          ? this.max_end
          : new Date().toISOString().substr(0, 10),
        type_id: this.document.type_id,
        date: this.document.date,
        description: this.document.description,

        entries: this.entriess,
      }),
    };
  },

  methods: {
    submit() {
      //   entries = this.entries;
      if (this.difference === 0) {
        this.$inertia.put(
          route("documents.update", this.document.id),
          this.form
        );
      } else {
        alert("Entry is not equal");
      }
    },
    //ON CHANGE FUNCTION ON DEBIT CREDIT TO NULL THE PARALLEL VALUES ---START ----
    debitchange(index) {
      let a = this.form.entries[index];
      a.credit = 0;

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

    //TO CHECK THAT THE DEBIT CREDIT ARE NOT ZERO --------------- STARTS ------------------
    checkingZero() {
      for (var i = 0; i < this.form.entries.length; i++) {
        if (
          this.form.entries[i].credit == 0 &&
          this.form.entries[i].debit == 0
        ) {
          this.difference = null;
        //   alert("Please fill debit OR credit field of a Transaction");
        }
      }
    },
    //TO CHECK THAT THE DEBIT CREDIT ARE NOT ZERO --------------- END ------------------

    addRow() {
      this.form.entries.push({
        // account_id: this.account_first.id,
        account_id: this.accounts[0].id,
        debit: 0,
        credit: 0,
      });
      this.difference = null;

      count += 1;
      console.log(count);
    },
    deleteRow(index) {
      this.form.entries.splice(index, 1);
      this.tcredit();
      this.tdebit();
      //   this.creditchange(index);
      //   this.debitchange(index);
    },
  },

  watch: {
    //  FOR DIFFERENCE OF DEBIT CREDIT ---START -----
    debit: function () {
      this.checkingZero();
      let diff = 0;
      if (this.debit == 0 && this.credit == 0) {
        this.difference = null;
      } else {
        diff = parseInt(this.debit) - parseInt(this.credit);
        this.difference = diff;
      }
    },
    credit: function () {
      this.checkingZero();

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
