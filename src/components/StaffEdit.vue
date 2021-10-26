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
          <template v-slot:item.start_time="{ item }">
            <v-text-field
              ref="start"
              v-model="item.start_time"
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

          <template v-slot:item.end_time="{ item }">
            <v-text-field
              ref="end"
              v-model="item.end_time"
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

          <template v-slot:item.total_time="{ item }">
            <v-text-field
              ref="total"
              :value="item.total_time = get_total(item)"
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
          <template v-slot:item.staff_hour_salary="{ item }">
            <v-text-field
              ref="hour"
              v-model="item.staff_hour_salary"
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
          <template v-slot:item.staff_day_salary="{ item }">
            <v-text-field
              ref="day"
              :value="item.staff_day_salary = get_staff_day_salary(item)"
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
          <template v-slot:item.staff_expense="{ item }">
            <v-text-field
              ref="expense"
              v-model="item.staff_expense"
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
    this.dialog = true;
  },
  watch: {
    salary_change(element) {
      if (element) {
        this.items.start_time = '';
        this.items.end_time = '';
        this.items.total_time = '';
        this.items.staff_hour_salary = '';
      } else {
        this.items.staff_day_salary = '';
      }
    },
    // hour_salary(element) {
    //   if (!this.salary_change) {
    //     this.items.staff_day_salary = Math.floor(element * this.items.total_time)
    //   }
    // },
    // total(element) {
    //   if (!this.salary_change) {
    //     this.items.staff_day_salary = Math.floor(element * this.items.staff_hour_salary)
    //   }
    // },
  },
  methods: {
    timeColon(type) {
      let replaceTime = type === 1 ? this.items.start_time.replace(":", "") : this.items.end_time.replace(":", "");
      let hours = '00'
      let minute = '00'

      if(replaceTime.length >= 4 && replaceTime.length < 5) {
        hours = replaceTime.substring(0, 2)
        minute = replaceTime.substring(2, 4)
        if(isFinite(hours + minute) == false) {
          type === 1 ? this.items.start_time = "" : this.items.end_time = "";
          return false;
        }
        if (hours > 23 ) {
          type === 1 ? this.items.start_time = "24:00" : this.items.end_time = "24:00";
          return false;
        }
        if (minute > 59) {
          type === 1 ? this.items.start_time = hours + ":00" : this.items.end_time = hours + ":00";
          return false;
        }
        type === 1 ? this.items.start_time = hours + ":" + minute : this.items.end_time = hours + ":" + minute;
        type === 1 ? this.enter(1) : this.enter(2);
      }
    },
    get_total(item) {
      let time = ''
      if (this.salary_change === false && item.start_time != '' && item.end_time != '') {
        var start = item.start_time.split(":")
        var end = item.end_time.split(":")

        var start_date = new Date (2020,11,31,start[0],start[1],0)
        var end_date = new Date (2020,11,31,end[0],end[1],0)
        time = (end_date.getTime() - start_date.getTime()) / 1000 / 60 / 60 - 1
        time = Number(time).toFixed(2)
      }
      return time
    },
    get_staff_day_salary(item) {
      if (item.staff_hour_salary == '' || item.total_time == '') {
        return ''
      }
      let salary = Math.floor(item.staff_hour_salary * item.total_time)
      return String(salary)
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
      if ((this.items.start_time || this.items.end_time) && (this.items.start_time.length != 5 || this.items.end_time.length != 5)) {
        return;
      }
      this.dialog = false;
      this.$emit("edit", this.items);
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>