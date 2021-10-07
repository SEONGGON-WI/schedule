<template>
  <v-dialog v-model="dialog" max-width="80%" persistent>
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

        <v-data-table 
          :headers="headers" 
          :items="items" 
          hide-default-footer 
          disable-pagination
          disable-sort
        >
          <template v-slot:item.name="{ item }">
            <label class="title_text">{{ item.name }}</label>
          </template>
          <template v-slot:item.comment="{ item }">
            <v-text-field
              height="30"
              class="mx-2 mt-4"
              v-model="item.comment"
              label="案件"
              auto-grow
              rows=1
            ></v-text-field>
          </template>
          <template v-slot:item.hour_salary="{ item }">
            <v-text-field
              height="30"
              class="mx-2 mt-4"
              v-model="item.hour_salary"
              label="時給"
            ></v-text-field>
          </template>
          <template v-slot:item.day_salary="{ item }">
            <v-text-field
              height="30"
              class="mx-2 mt-4"
              v-model="item.day_salary"
              label="日給"
            ></v-text-field>
          </template>
          <template v-slot:item.action="{ item }">
            <v-btn class="error ml-1" icon color="white" @click="remove(item)">
              <v-icon>delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card>
    </v-container>
  </v-dialog>
</template>
<script>

export default {
  name: "editschedule",
  components: {
  },
  props: [
    'items', 'date'
  ],
  data: () => ({
    headers: [
      { value:"name", text:"名前", width:"20%", align: 'start'},
      { value:"comment", text:"案件", width:"25%", align: 'center'},
      { value:"hour_salary", text:"時給", width:"20%", align: 'center'},
      { value:"day_salary", text:"日給", width:"20%", align: 'center'},
      { value:"action", text:"削除", width:"10%", align: 'center'}
    ],
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