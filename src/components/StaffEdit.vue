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

        <v-data-table 
          :headers="headers" 
          :items="[items]" 
          hide-default-footer 
          disable-pagination
          disable-sort
        >
          <template v-slot:item.start_time>
            <v-text-field
              height="30"
                class="pa-1 mt-4"
                v-model="start"
                label="出勤時間"
                placeholder="0900"
                :rules="rules"
                counter=4
            ></v-text-field>
          </template>
          <template v-slot:item.end_time>
            <v-text-field
              height="30"
                class="pa-1 mt-4"
                v-model="end"
                label="退勤時間"
                placeholder="0600"
                :rules="rules"
                counter=4
            ></v-text-field>
          </template>
          <template v-slot:item.staff_salary>
            <v-text-field
              height="30"
                class="pa-1 mt-4"
                v-model="salary"
                label="給与"
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
      { value:"staff_salary", text:"給与", align: 'center'},
    ],
    date: '',
    start: '',
    end: '',
    salary: '',
    rules: [v => (v || '').length == 4 || '4桁を入力してください。'],
    dialog: false,
  }),
  created() {
    const day = this.items.date.split("-");
    this.date = day[0] +'年 '+ day[1] +'月 '+ day[2]+'日';
    this.start = this.items.start_time;
    this.end = this.items.end_time;
    this.salary = this.items.staff_salary;
    this.dialog = true;
  },
  methods: {
    edit() {
      if( this.start || this.end) {
        if (this.start.length != 4 && this.end.length != 4) {
          return;
        }
      }
      const data = {
        start_time: this.start,
        end_time: this.end,
        staff_salary: this.salary,
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