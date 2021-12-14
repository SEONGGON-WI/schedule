<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" persistent scrollable> 
    <v-card color="grey lighten-4">
      <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2">
            {{ date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>
      <v-card-text class="pa-0">
      <v-row no-gutters>
        <v-col cols="3" class="name_agenda pt-3 pl-8">
          <div>{{ name }}</div>
        </v-col>
        <v-col cols="3" class="name_agenda pt-3 pl-8">
          {{ total_agenda }}
        </v-col>
        <v-col cols="6" class="name_agenda pt-3 pl-8">
          {{ get_total_salary }}
        </v-col>
      </v-row>
      <v-data-table 
        name="staff-analytics-table"
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
<style lang="scss">
[name=staff-analytics-table] tbody tr:nth-of-type(odd){
  background: rgba(170, 200, 99,0.2); 
}
</style>
<script>
export default {
  name: "staffanalytics",
  components: {
  },
  props: [
    'items', 'date'
  ],
  data: () => ({
    headers: [
      { value:"date", text:"日付", width: "10%", align: 'center'},
      { value:"agenda", text:"案件", width: "20%", align: 'start'},
      { value:"start_time", text:"出勤", width: "12%", align: 'center'},
      { value:"end_time", text:"退勤", width: "12%", align: 'center'},
      { value:"total_time", text:"時間", width: "10%", align: 'center'},
      { value:"staff_hour_salary", text:"時給", width: "12%", align: 'center'},
      { value:"staff_day_salary", text:"日給", width: "12%", align: 'center'},
      { value:"staff_expense", text:"経費", width: "12%", align: 'center'}
    ],
    total_agenda: 0,
    name: '',
    dialog: false,
  }),
  created() {
    this.total_agenda = this.items.length + "件"
    this.name = this.$store.getters.staff_name
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
          var expense = obj.staff_expense ? parseInt(obj.staff_expense) : 0
          return stack + parseInt(obj.staff_day_salary) + expense
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
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>