<template>
  <v-dialog v-model="dialog" @keydown.enter="edit" @keydown.esc="close" max-width="80%" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2 title_text">
            {{ edit_date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="success mx-2 botton_size" @click="edit">
            <v-icon>save</v-icon>保存
          </v-btn>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>

        <v-tabs v-model="tab" grow>
          <v-tab v-for="item in tab_item" :key="item">
            {{ item }}
          </v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab">
          <v-tab-item key="管理者">
            <v-data-table 
              :headers="headers" 
              :items="items" 
              hide-default-footer 
              disable-pagination
              disable-sort
            >
              <template v-slot:item.agenda="{ item }">
                <v-text-field
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.agenda"
                  label="案件"
                  rows=1
                ></v-text-field>
              </template>
              <template v-slot:item.total_time="{ item }">
                <v-text-field
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.total_time"
                ></v-text-field>
              </template>
              <template v-slot:item.admin_hour_salary="{ item }">
                <v-text-field
                  v-if="item.total_time == ''"
                  height="40"
                  class="mx-2 mt-4"
                  :value="item.admin_hour_salary = ''"
                  label="時給"
                  :disabled="item.total_time == ''"
                ></v-text-field>
                <v-text-field
                  v-else
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.admin_hour_salary"
                  label="時給"
                ></v-text-field>
              </template>
              <template v-slot:item.admin_day_salary="{ item }">
                <v-text-field
                  v-if="item.admin_hour_salary"
                  height="40"
                  class="mx-2 mt-4"
                  :value="item.admin_day_salary = get_admin_day_salary(item)"
                  label="日給"
                  :disabled="item.total_time != ''"
                ></v-text-field>
                <v-text-field
                  v-else
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.admin_day_salary"
                  label="日給"
                ></v-text-field>
              </template>
              <template v-slot:item.action="{ item }">
                <v-btn class="error" icon color="white" @click="remove(item)">
                  <v-icon>delete</v-icon>
                </v-btn>
              </template>
            </v-data-table>
          </v-tab-item>

          <v-tab-item key="スタッフ">
            <v-data-table 
              :headers="header" 
              :items="items" 
              hide-default-footer 
              disable-pagination
              disable-sort
            >
              <template v-slot:item.start_time="{ item }">
                <v-text-field
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.start_time"
                  label="出勤時間"
                  placeholder="0900"
                  :rules="rules"
                  counter=4
                  :disabled="item.total_time == ''"
                ></v-text-field>
              </template>
              <template v-slot:item.end_time="{ item }">
                <v-text-field
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.end_time"
                  label="退勤時間"
                  placeholder="1800"
                  :rules="rules"
                  counter=4
                  :disabled="item.total_time == ''"
                ></v-text-field>
              </template>
              <template v-slot:item.total_time="{ item }">
                <v-text-field
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.total_time"
                ></v-text-field>
              </template>
              <template v-slot:item.staff_hour_salary="{ item }">
                <v-text-field
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.staff_hour_salary"
                  label="時給"
                  :disabled="item.total_time == ''"
                ></v-text-field>
              </template>
              <template v-slot:item.staff_day_salary="{ item }">
                <v-text-field
                  v-if="item.staff_hour_salary"
                  height="40"
                  class="mx-2 mt-4"
                  :v-model="item.staff_day_salary = get_staff_day_salary(item)"
                  label="日給"
                  :disabled="item.total_time != ''"
                ></v-text-field>
                <v-text-field
                  v-else
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.staff_day_salary"
                  label="日給"
                ></v-text-field>
              </template>
              <template v-slot:item.staff_expense="{ item }">
                <v-text-field
                  height="40"
                  class="mx-2 mt-4"
                  v-model="item.staff_expense"
                  label="警備"
                ></v-text-field>
              </template>
            </v-data-table>
          </v-tab-item>
        </v-tabs-items>
      </v-card>
    </v-container>
  </v-dialog>
</template>
<script>

export default {
  name: "adminedit",
  components: {
  },
  props: [
    'items', 'date'
  ],
  data: () => ({
    headers: [
      { value:"name", text:"名前", width: "25%", align: 'start'},
      { value:"agenda", text:"案件", width: "25%", align: 'start'},
      { value:"total_time", text:"勤労時間", width: "10%", align: 'center'},
      { value:"admin_hour_salary", text:"時給", width: "10%", align: 'center'},
      { value:"admin_day_salary", text:"日給", width: "10%", align: 'center'},
      { value:"action", text:"削除", width:"10%", align: 'center'}
    ],
    header: [
      { value:"name", text:"名前", width: "20%", align: 'start'},
      { value:"agenda", text:"案件", width: "20%", align: 'start'},
      { value:"start_time", text:"出勤時間", width: "10%", align: 'center'},
      { value:"end_time", text:"退勤時間", width: "10%", align: 'center'},
      { value:"total_time", text:"勤労時間", width: "10%", align: 'center'},
      { value:"staff_hour_salary", text:"時給", width: "10%", align: 'center'},
      { value:"staff_day_salary", text:"日給", width: "10%", align: 'center'},
      { value:"staff_expense", text:"警備", width: "10%", align: 'center'}
    ],
    tab: null,
    tab_item: ['管理者', 'スタッフ'],
    rules: [v => v.length == 4 || v == '' || '4桁入力'],
    edit_date: '',
    dialog: false,
  }),
  created() {
    this.edit_date = this.date.split("-");
    this.edit_date = this.edit_date[0] +"年 "+ this.edit_date[1] +"月 "+ this.edit_date[2] +"日";
    this.dialog = true;
  },
  computed: {
  },
  methods: {
    get_admin_day_salary(item) {
      return String(item.admin_hour_salary * item.total_time)
    },
    get_staff_day_salary(item) {
      return String(item.staff_hour_salary * item.total_time)
    },
    remove(item) {
      const index = this.items.findIndex(obj => obj.name == item.name);
      this.items.splice(index, 1);
    },
    edit() {
      this.dialog = false;
      this.$emit("accept", this.items);
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>