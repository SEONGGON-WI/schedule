<template>
  <v-dialog v-model="dialog" max-width="60%" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2">
            {{ date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="success mx-1" @click="edit">
            <v-icon>save</v-icon>閉じる
          </v-btn>
          
          <v-btn class="error mx-1" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>

        <v-main class="pa-0">
          <v-row no-gutters>
            <v-col cols="4">
              <v-text-field
                class="pa-1 mt-4"
                v-model="start"
                label="開始時間"
                placeholder="0900"
                :rules="rules"
                counter=4
              ></v-text-field>
            </v-col>
            <v-col cols="4">
              <v-text-field
                class="pa-1 mt-4"
                v-model="end"
                label="終了時間"
                placeholder="0600"
                :rules="rules"
                counter=4
              ></v-text-field>
            </v-col>
            <v-col cols="4">
              <v-text-field
                class="pa-1 mt-4"
                v-model="price"
                label="給与"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-main>
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
    date: '',
    start: '',
    end: '',
    price: '',
    rules: [v => (v || '').length == 4 || '4桁を入力してください。'],
    dialog: false,
  }),
  created() {
    const day = this.items.date.split("-");
    this.date = day[0] +'年 '+ day[1] +'月 '+ day[2]+'日';
    this.start = this.items.start_time;
    this.end = this.items.end_time;
    this.price = this.items.price;
    this.dialog = true;
  },
  methods: {
    edit() {
      if (this.start === undefined || this.end === undefined) {
        return;
      }
      if (this.start.length != 4 && this.end.length != 4) {
        return;
      }
      const data = {
        start_time: this.start,
        end_time: this.end,
        price: this.price,
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