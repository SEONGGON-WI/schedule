<template>
  <v-dialog v-model="dialog" :max-width="$vuetify.breakpoint.mobile ? '100%' : '80%'" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2">
            {{ date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="success mx-2 botton_size" @click="edit" :disabled="!valid">
            <v-icon>cloud_upload</v-icon>登録
          </v-btn>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>
        <v-row no-gutters>
          <v-col cols="3" class="name_agenda pt-3 pl-8">
            <div>{{ items[edit_index].name }}</div>
          </v-col>
          <v-col cols="5" class="name_agenda pt-3 pl-8">
            <v-select
              v-if="items.length !== 0"
              height="30"
              class="pr-3"
              v-model="agenda" 
              :items="items"
              item-text="agenda"
              item-value="agenda"
              label="案件"
              @change="search"
              append-icon="arrow_drop_down"
            >
            </v-select>
          </v-col>
          <v-col cols="4">
            <v-radio-group v-model="salary_change" row class="" @change="change()">  
            <v-radio label="時給" value="hour"></v-radio>
            <v-radio label="日給" value="day" class="pl-2"></v-radio>
          </v-radio-group>  
          </v-col>
        </v-row>
        <v-form ref="form" v-model="valid">
        <v-data-table 
          class="pt-5"
          :headers="header" 
          :items="[items[edit_index]]" 
          hide-default-footer 
          disable-pagination
          disable-sort
        >
          <template v-slot:item.start_time="{ item }">
            <v-text-field
              ref="start"
              v-model="item.start_time"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
              :rules="[rules.required]"
              :dense="salary_change == 'day'"
              :filled="salary_change == 'day'"
              :readonly="salary_change == 'day'"
              @keydown.enter="enter(1)"
              class="my-3"
              label="出勤時間"
              height="60"
              placeholder="0900"
              maxlength="5"
              outlined
              single-line
            ></v-text-field>
          </template>

          <template v-slot:item.end_time="{ item }">
            <v-text-field
              ref="end"
              v-model="item.end_time"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
              :rules="[rules.required]"
              :dense="salary_change == 'day'"
              :filled="salary_change == 'day'"
              :readonly="salary_change == 'day'"
              @keydown.enter="enter(2)"
              class="my-3"
              label="退勤時間"
              height="60"
              placeholder="1800"
              maxlength="5"
              outlined
              single-line
            ></v-text-field>
          </template>

          <template v-slot:item.total_time="{ item }">
            <v-text-field
              ref="total"
              :value="item.total_time = get_total(item)"
              dense
              filled
              readonly
              class="my-3"
              label="勤労時間"
              height="60"
              outlined
              single-line
            ></v-text-field>
          </template>
        </v-data-table>
        <v-data-table 
          :headers="headers"
          :items="[items[edit_index]]" 
          hide-default-footer 
          disable-pagination
          disable-sort
        >
          <template v-slot:item.staff_hour_salary="{ item }">
            <v-text-field
              ref="hour"
              v-model="item.staff_hour_salary"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
              :rules="[rules.positive]"
              :dense="salary_change == 'day'"
              :filled="salary_change == 'day'"
              :readonly="salary_change == 'day'"
              @keydown.enter="enter(3)"
              class="mt-3 mb-10"
              label="時給"
              height="60"
              outlined
              single-line
            ></v-text-field>
          </template>
          <template v-slot:item.staff_day_salary="{ item }">
          <v-text-field
              v-if="salary_change == 'hour'"
              :value="item.staff_day_salary = get_staff_day_salary(item)"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
              dense
              filled
              readonly
              class="mt-3 mb-10"
              label="日給"
              height="60"
              outlined
              single-line
            ></v-text-field>
            <v-text-field
              v-else
              ref="day"
              v-model="item.staff_day_salary"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
              :rules="[rules.positive]"
              @keydown.enter="enter(3)"
              class="mt-3 mb-10"
              label="日給"
              height="60"
              outlined
              single-line
            ></v-text-field>
          </template>
          <template v-slot:item.staff_expense="{ item }">
            <v-text-field
              ref="expense"
              v-model="item.staff_expense"
              :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
              :rules="[rules.positive]"
              @keydown.enter="enter(5)"
              class="mt-3 mb-10"
              label="経費"
              height="60"
              outlined
              single-line
            ></v-text-field>
          </template>
        </v-data-table>
        </v-form>
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
    'items', 'edit_date'
  ],
  data: () => ({
    header: [
      { value:"start_time", text:"出勤時間", width:"33%", align: 'center'},
      { value:"end_time", text:"退勤時間", width:"33%", align: 'center'},
      { value:"total_time", text:"勤務時間", width:"33%", align: 'center'}
    ],
    headers: [
      { value:"staff_hour_salary", text:"時給", width:"33%", align: 'center'},
      { value:"staff_day_salary", text:"日給", width:"33%", align: 'center'},
      { value:"staff_expense", text:"経費", width:"33%", align: 'center'}
    ],
    edit_index: 0,
    name: '',
    date: '',
    agenda: '',
    valid: true,
    rules:{
      required: v => (v.length == 4 || v.length == 5) || v == '' || '時間の様式に合わせてください。',
      positive: v => v >= 0 || v == '' || '正の整数を指定'
    },
    dialog: false,
    salary_change: 'hour',
  }),
  created() {
    const day = this.edit_date.split("-");
    this.date = day[0] +'年 '+ day[1] +'月 '+ day[2]+'日';
    this.agenda = this.items[this.edit_index].agenda
    this.search()
    this.dialog = true;
  },
  methods: {
    search() {
      this.edit_index = this.items.findIndex(element => element.agenda === this.agenda)
      if (this.items[this.edit_index].staff_hour_salary == '' && this.items[this.edit_index].staff_day_salary == '') {
        this.salary_change = 'hour'
      } else if (this.items[this.edit_index].staff_hour_salary != '') {
        this.salary_change = 'hour';
      } else {
        this.salary_change = 'day';
      } 
    },
    get_total(item) {
      let time = ''
      let admin_time = ''
      if (this.salary_change === 'hour' && item.start_time != '' && item.end_time != '' && ((item.start_time.length == 4 || item.start_time.length == 5) && (item.end_time.length == 4 || item.end_time.length == 5))) {
        var start = []
        if (item.start_time.length == 4) {
          start[0] = item.start_time.substring(0, 2)
          start[1] = item.start_time.substring(2, 4)
        } else if (item.start_time.length == 5) {
          start[0] = item.start_time.substring(0, 2)
          start[1] = item.start_time.substring(3, 5)
        }
        var end = []
        if (item.end_time.length == 4) {
          end[0] = item.end_time.substring(0, 2)
          end[1] = item.end_time.substring(2, 4)
        } else if (item.end_time.length == 5) {
          end[0] = item.end_time.substring(0, 2)
          end[1] = item.end_time.substring(3, 5)
        }
        var start_date = new Date (2020,11,31,start[0],start[1],0)
        var end_date = new Date (2020,11,31,end[0],end[1],0)
        time = (end_date.getTime() - start_date.getTime()) / 1000 / 60 / 60 - 1
        time = Number(time).toFixed(2)
        admin_time = (Number(parseFloat(time) + 1).toFixed(2))
        var time_difference = parseFloat(item.total_time) - parseFloat(time)
        if (time_difference <= 2 || time_difference >= -2) {
          item.admin_total_time = Number(item.admin_total_time).toFixed(2)
          return Number(item.total_time).toFixed(2)
        }
      }
      item.admin_total_time = admin_time
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
      var condition = true
      this.items.map(item => {
        if ((item.start_time || item.end_time) && (!(item.start_time.length == 4 || item.start_time.length == 5) && !(item.end_time.length == 4 || item.end_time.length == 5))) {
          condition = false
        }
        item.start_time = item.start_time == '' ? '' : this.make_colon(item.start_time)
        item.end_time = item.end_time == '' ? '' :  this.make_colon(item.end_time)
        if (item.start_time === false) {
          item.start_time = ''
          condition = false
        }
        if (item.end_time === false) {
          item.end_time = ''
          condition = false
        }

      })
      if (condition === false) {
        return
      }
      // if ((this.items[this.edit_index].start_time || this.items[this.edit_index].end_time) && (this.items[this.edit_index].start_time.length != 4 || this.items[this.edit_index].end_time.length != 4)) {
      //   return;
      // }
      // this.items[this.edit_index].start_time = this.items[this.edit_index].start_time == '' ? '' : this.make_colon(this.items[this.edit_index].start_time)
      // this.items[this.edit_index].end_time = this.items[this.edit_index].end_time == '' ? '' :  this.make_colon(this.items[this.edit_index].end_time)
      // if (this.items[this.edit_index].start_time === false) {
      //   this.items[this.edit_index].start_time = ''
      //   return;
      // }
      // if (this.items[this.edit_index].end_time === false) {
      //   this.items[this.edit_index].end_time = ''
      //   return;
      // }
      this.dialog = false;
      this.$emit("edit", this.items);
    },
    make_colon(time) {
      var hours = ''
      var minute = ''
      if (time.length == 4) {
        hours = time.substring(0, 2)
        minute = time.substring(2, 4)  
      } else if (time.length == 5) {
        hours = time.substring(0, 2)
        minute = time.substring(3, 5)  
      }
      if(isFinite(hours + minute) == false) {
        return false
      }
      if (hours > 23 ) {
        return false
      }
      if (minute > 59) {
        return false
      }
      return hours + ":" + minute;
    },
    change(element) {
      if (element == 'day') {
        this.items[this.edit_index].start_time = '';
        this.items[this.edit_index].end_time = '';
        this.items[this.edit_index].total_time = '';
        this.items[this.edit_index].admin_total_time = '';
        this.items[this.edit_index].staff_hour_salary = '';
      } else {
        this.items[this.edit_index].staff_day_salary = '';
      }
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>