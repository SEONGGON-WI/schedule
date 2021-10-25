<template>
  <v-dialog v-model="dialog" :max-width="$vuetify.breakpoint.mobile ? '100%' : '80%'" persistent>
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
          <v-spacer></v-spacer>
          <v-col cols="3">
            <v-switch
              v-model="salary_change"
              inset
              :label="`給与形態 : ${salary_change ?  '日給' : '時給'}`"
            ></v-switch>
          </v-col>
        </v-row>

        <v-data-table 
          :headers="header" 
          :items="[items]" 
          hide-default-footer 
          disable-pagination
          disable-sort
        >
          <template v-slot:item.start_time>
            <v-text-field
              ref="start"
              v-model="start"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
              :rules="rules"
              :dense="salary_change"
              :filled="salary_change"
              :disabled="salary_change"
              @input="timeColon(1)"
              @keydown.enter="enter(1)"
              class="my-3"
              label="出勤時間"
              height="40"
              placeholder="0900"
              maxlength="5"
            ></v-text-field>
          </template>

          <template v-slot:item.end_time>
            <v-text-field
              ref="end"
              v-model="end"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
              :rules="rules"
              :dense="salary_change"
              :filled="salary_change"
              :disabled="salary_change"
              @input="timeColon(2)"
              @keydown.enter="enter(2)"
              class="my-3"
              label="退勤時間"
              height="40"
              placeholder="1800"
              maxlength="5"
            ></v-text-field>
          </template>

          <template v-slot:item.total_time>
            <v-text-field
              ref="total"
              :value="total = get_total()"
              dense
              filled
              disabled
              class="my-3"
              label="勤労時間"
              height="40"
            ></v-text-field>
          </template>
        </v-data-table>
        <v-data-table 
          :headers="headers" 
          :items="[items]" 
          hide-default-footer 
          disable-pagination
          disable-sort
        >
          <template v-slot:item.staff_hour_salary>
            <v-text-field
              ref="hour"
              v-model="hour_salary"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
              :dense="salary_change"
              :filled="salary_change"
              :disabled="salary_change"
              @keydown.enter="enter(3)"
              class="my-3"
              label="時給"
              height="40"
            ></v-text-field>
          </template>
          <template v-slot:item.staff_day_salary>
            <v-text-field
              ref="day"
              v-model="day_salary"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
              :dense="!salary_change"
              :filled="!salary_change"
              :disabled="!salary_change"
              @keydown.enter="enter(3)"
              class="my-3"
              label="日給"
              height="40"
            ></v-text-field>
          </template>
          <template v-slot:item.staff_expense>
            <v-text-field
              ref="expense"
              v-model="expense"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
              @keydown.enter="enter(5)"
              class="my-3"
              label="経費"
              height="40"
            ></v-text-field>
          </template>
        </v-data-table>
      </v-card>
    </v-container>
  </v-dialog>
</template>
<script>
export default {
  name: "staffedit",
  components: {
  },
  props: [
    'items'
  ],
  data: () => ({
    header: [
      { value:"start_time", text:"出勤時間", width:"33%", align: 'center'},
      { value:"end_time", text:"退勤時間", width:"33%", align: 'center'},
      { value:"total_time", text:"勤労時間", width:"33%", align: 'center'}
    ],
    headers: [
      { value:"staff_hour_salary", text:"時給", width:"33%", align: 'center'},
      { value:"staff_day_salary", text:"日給", width:"33%", align: 'center'},
      { value:"staff_expense", text:"経費", width:"33%", align: 'center'}
    ],
    date: '',
    start: '',
    end: '',
    total: '',
    hour_salary: '',
    day_salary: '',
    expense: '',
    rules: [v => v.length == 5 || v == '' || '時間の様式に合わせてください。'],
    dialog: false,
    salary_change: false,
  }),
  created() {
    const day = this.items.date.split("-");
    this.date = day[0] +'年 '+ day[1] +'月 '+ day[2]+'日';
    if (this.items.staff_hour_salary == '' && this.items.staff_day_salary == '') {
      this.salary_change = false
    } else if (this.items.staff_hour_salary != '') {
      this.salary_change = false;
    } else {
      this.salary_change = true;
    }
    this.start = this.items.start_time;
    this.end = this.items.end_time;
    this.total = this.items.total_time;
    this.hour_salary = this.items.staff_hour_salary;
    this.day_salary = this.items.staff_day_salary;
    this.expense = this.items.staff_expense;
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
        let salary = element * this.total
        var m = Number((Math.abs(salary) * 100).toPrecision(15));
        this.day_salary =  Math.round(m) / 100 * Math.sign(salary);
      }
    },
    total(element) {
      if (!this.salary_change) {
        let salary = element * this.hour_salary
        var m = Number((Math.abs(salary) * 100).toPrecision(15));
        this.day_salary =  Math.round(m) / 100 * Math.sign(salary);
      }
    },
  },
  methods: {
    timeColon(type) {
      let replaceTime = type === 1 ? this.start.replace(":", "") : this.end.replace(":", "");
      let hours = '00'
      let minute = '00'

      if(replaceTime.length >= 4 && replaceTime.length < 5) {
        hours = replaceTime.substring(0, 2)
        minute = replaceTime.substring(2, 4)
        if(isFinite(hours + minute) == false) {
          type === 1 ? this.start = "" : this.end = "";
          return false;
        }
        if (hours > 23 ) {
          type === 1 ? this.start = "24:00" : this.end = "24:00";
          return false;
        }
        if (minute > 59) {
          type === 1 ? this.start = hours + ":00" : this.end = hours + ":00";
          return false;
        }
        type === 1 ? this.start = hours + ":" + minute : this.end = hours + ":" + minute;
      }
    },
    get_total() {
      let time = ''
      if (this.salary_change === false && this.start != '' && this.end != '') {
        var start = new Date ("2021-12-31 " + this.start)
        var end = new Date ("2021-12-31 " + this.end)
        time = (end - start) / 1000 / 60 / 60 - 1

        var m = Number((Math.abs(time) * 100).toPrecision(15));
        time =  Math.round(m) / 100 * Math.sign(time);
      }
      return time
    },
    enter(index) {
      switch (index) {
        case 1:
          this.$refs.end.focus()
          break;

        case 2:
          this.$refs.hour.focus()
          break;

        case 3:
          this.$refs.expense.focus()
          break;

        default:
          this.edit();
          break;
      }
    },
    edit() {
      if ((this.start || this.end) && (this.start.length != 5 || this.end.length != 5)) {
        return;
      }
      const data = {
        start_time: this.start.replace(/^\s+|\s+$/gm,''),
        end_time: this.end.replace(/^\s+|\s+$/gm,''),
        total_time: this.total,
        staff_hour_salary: this.hour_salary,
        staff_day_salary: this.day_salary,
        staff_expense: this.expense
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