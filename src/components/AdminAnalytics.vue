<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" :max-width="$vuetify.breakpoint.mobile ? '100%' : '80%'" persistent scrollable> 
    <v-card color="grey lighten-4">
      <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2 title_text">
            {{ date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>
      <v-card-text class="pa-0">
      <v-row no-gutters>
        <v-col cols="3" class="pt-4 ml-8">
          <v-select 
            height="30"
            class="pt-0"
            v-model="differ_name" 
            :items="name_items"
            @change="change_name"
            append-icon="arrow_drop_down"
            hide-details
          ></v-select>
        </v-col>
        <v-col cols="1" class="pt-4 ml-8">
          {{ total_agenda }}
        </v-col>
        <v-col cols="2" class="pt-4 ml-8">
          {{ get_total_salary }}
        </v-col>
      </v-row>
      <v-data-table 
        :headers="headers"
        :items="items"
        class="mt-3 fixed-header2"
        hide-default-footer 
        disable-pagination
        disable-sort
      >
      <template v-slot:item.date="{ item }">
        <div>{{ get_date(item.date) }} </div>
      </template>
      </v-data-table>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  name: "adminanalytics",
  components: {
  },
  props: [
    'items', 'name_items', 'name', 'date'
  ],
  data: () => ({
    headers: [
      { value:"date", text:"日付", width: "8%", align: 'center'},
      { value:"agenda", text:"案件", width: "20%", align: 'start'},
      { value:"start_time", text:"出勤", width: "12%", align: 'center'},
      { value:"end_time", text:"退勤", width: "12%", align: 'center'},
      { value:"total_time", text:"時間", width: "10%", align: 'center'},
      { value:"staff_hour_salary", text:"時給", width: "12%", align: 'center'},
      { value:"staff_day_salary", text:"日給", width: "14%", align: 'center'},
      { value:"staff_expense", text:"経費", width: "12%", align: 'center'}
    ],
    total_agenda: 0,
    differ_name: '',
    dialog: false,
  }),
  created() {
    this.total_agenda = this.items.length + "件"
    this.differ_name = this.name
    this.dialog = true;
  },
  watch: {
    items(element) {
      this.total_agenda = element.length + "件"
    },
  },
  computed: {
    get_total_salary() {
      const salary = this.items.reduce((stack, obj) => {
        if (obj.staff_day_salary != '') {
          return stack + parseInt(obj.staff_day_salary)
        } else {
          return stack
        }
      }, 0)
      return "￥" + salary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
  },
  methods: {
    get_date(date) {
      const day = date.split("-")
      if (day[2] < 10) {
        return day[2][1]
      } else {
        return day[2]
      }
    },
    change_name() {
      this.$emit("change", this.differ_name);
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>