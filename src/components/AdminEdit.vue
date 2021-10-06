<template>
  <v-dialog v-click-outside="close" v-model="dialog" max-width="60%">
    <v-container class="pa-0" fluid>
      <v-card outlined color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title>
            {{ date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="close" class="mx-2">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>

        <v-data-table 
          :headers="headers" 
          :items="items" 
          hide-default-footer 
          disable-pagination
        >
          <template v-slot:item.name="{ item }">
            <label style="font-size: x-large !important;">{{ item.name }}</label>
          </template>
          <template v-slot:item.comment="{ item }">
            <v-textarea 
              class="textarea mx-2 mt-4"
              v-model="item.comment"
              @input="text_input"
              label="メモ"
              auto-grow
              rows=1
            ></v-textarea>
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
    'date', 'items'
  ],
  data: () => ({
    headers: [
      { value:"name", text:"名前", width:"30%", align: 'start', sortable: false},
      { value:"comment", text:"メモ", width:"60%", align: 'center', sortable: false},
      { value:"action", text:"削除", width:"10%", align: 'center', sortable: false},
    ],
    dialog: false,
    input: false,
  }),
  created() {
    this.dialog = true;
  },
  computed: {
  },
  methods: {
    text_input() {
      console.log('input');
      this.input = true;
    },
    text_close() {
      this.input = false;
    },
    close() {
      if (!this.input) {
        this.dialog = false;
        this.$emit("edit_close");
      }
    },
    remove(data) {
      let item = [];
      this.items.map((e) => {
        if (e.name != data.name || e.date != data.date) {
          item.push(e);
        }
      });
      this.items = item;
      if (this.items.length == 0) {
        this.close();
      }
      this.dialog = false;
      this.$emit("remove_one", data);
    },

  },
}
</script>