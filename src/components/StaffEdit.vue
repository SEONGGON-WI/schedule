<template>
  <v-dialog v-model="dialog" @keydown.enter="edit" @keydown.esc="close" max-width="80%" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2 title_text">
            {{ date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="success mx-2 botton_size" @click="edit">
            <v-icon>save</v-icon>保存
          </v-btn>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>
        <v-row no-gutters>
              <v-col cols="2" class="pt-4 ml-8">
                <div>{{ items.name }}</div>
              </v-col>
              <v-col cols="2" class="pt-4 ml-8">
                <div>{{ items.agenda }}</div>
              </v-col>
              <v-col cols="3">
                <v-switch
              v-model="salary_change"
              inset
              :label="`給与形態 : ${salary_change ?  '日給' : '時給'}`"
            ></v-switch>
              </v-col>
            </v-row>

        <v-data-table 
          :headers="headers" 
          :items="[items]" 
          hide-default-footer 
          disable-pagination
          disable-sort
        >
        
          <template v-slot:item.start_time>
            <v-text-field
              height="40"
              class="pa-1 mt-4"
              v-model="start"
              label="出勤時間"
              placeholder="0900"
              :rules="rules"
              counter=4
              :disabled="salary_change"
            ></v-text-field>
          </template>

          <template v-slot:item.end_time>
            <v-text-field
              height="40"
              class="pa-1 mt-4"
              v-model="end"
              label="退勤時間"
              placeholder="1800"
              :rules="rules"
              counter=4
              :disabled="salary_change"
            ></v-text-field>
          </template>

          <template v-slot:item.total_time>
            <v-text-field
              height="40"
              class="pa-1 mt-4"
              v-model="total"
              label="勤労時間"
              :disabled="salary_change"
            ></v-text-field>
          </template>

          <template v-slot:item.staff_hour_salary>
            <v-text-field
              height="40"
              class="pa-1 mt-4"
              v-model="hour_salary"
              label="時給"
              :disabled="salary_change"
            ></v-text-field>
          </template>
          <template v-slot:item.staff_day_salary>
            <v-text-field
              height="40"
              class="pa-1 mt-4"
              v-model="day_salary"
              label="日給"
              :disabled="!salary_change"
            ></v-text-field>
          </template>
        </v-data-table>
      </v-card>
    </v-container>
  </v-dialog>
</template>
<script>
export default {
  name: "staffschedule",
  components: {
  },
  props: [
    'items'
  ],
  data: () => ({
    headers: [
      { value:"start_time", text:"出勤時間", align: 'center'},
      { value:"end_time", text:"退勤時間", align: 'center'},
      { value:"total_time", text:"勤労時間", align: 'center'},
      { value:"staff_hour_salary", text:"時給", align: 'center'},
      { value:"staff_day_salary", text:"日給", align: 'center'},
    ],
    date: '',
    start: '',
    end: '',
    total: '',
    hour_salary: '',
    day_salary: '',
    rules: [v => v.length == 4 || v == '' || '4桁入力'],
    dialog: false,
    salary_change: false,
  }),
  created() {
    const day = this.items.date.split("-");
    this.date = day[0] +'年 '+ day[1] +'月 '+ day[2]+'日';
    this.salary_change = this.items.staff_hour_salary ? false : true;
    this.start = this.items.start_time;
    this.end = this.items.end_time;
    this.total = this.items.total_time;
    this.hour_salary = this.items.staff_hour_salary;
    this.day_salary = this.items.staff_day_salary;
    this.dialog = true;
  },
  watch: {
    salary_change(element) {
      if (element) {
        this.start = '';
        this.end = '';
        this.total = '';
        this.hour_salary = '';
      } else {
        this.day_salary = '';
      }
    },
    hour_salary(element) {
      if (!this.salary_change) {
        this.day_salary = element * this.total
      }
    },
  },
  methods: {
    edit() {
      if ((this.start || this.end) && (this.start.length != 4 || this.end.length != 4)) {
        return;
      }
      const data = {
        start_time: this.start.replace(/^\s+|\s+$/gm,''),
        end_time: this.end.replace(/^\s+|\s+$/gm,''),
        total_time: this.total.replace(/^\s+|\s+$/gm,''),
        staff_hour_salary: this.hour_salary,
        staff_day_salary: this.day_salary
      }
      this.dialog = false;
      this.$emit("edit", data);
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>