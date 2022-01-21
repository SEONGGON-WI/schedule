<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2">
            データ削除
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>
        <v-row no-gutters class="mt-4">
          <v-col cols="4">
            <v-menu
            v-model="delete_date"
            :close-on-content-click="false"
            :nudge-right="40"
            transition="scale-transition"
            offset-y
            min-width="auto"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-text-field
                v-model="pick_date"
                label="削除基準月選択"
                prepend-icon="calendar_month"
                readonly
                v-bind="attrs"
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              ref="date"
              v-model="pick_date"
              @input="delete_date = false"
              type="month"
              locale="ja-jp"
              full-width
            ></v-date-picker>
          </v-menu>
          </v-col>
          <v-col cols="8">
            <v-btn outlined class="success ma-2" color="white" @click="remove_check('space')">空きスケジュール削除</v-btn>
            <v-btn outlined class="error ma-2" color="white" @click="remove_check('all')">すべて削除</v-btn>
          </v-col>
        </v-row>
      </v-card>
    </v-container>
    <alert
      @close="alert_show = false"
      :text="alert_text"
      v-if="alert_show"
    ></alert>

    <admin-dialog
      @accept="remove"
      @close="delete_dialog = false"
      v-if="delete_dialog"
      text="削除しますか？"
    ></admin-dialog>
  </v-dialog>
</template>
<script>
import AdminDialog from '@/components/AdminDialog.vue';
import alert from './alert.vue';
import axios from 'axios';
export default {
  name: "deletedialog",
  components: {
    AdminDialog,
    alert,
  },
  props: [
    'date'
  ],
  data: () => ({
    delete_date: '',
    pick_date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 7),
    alert_text: '',
    alert_show: false,
    dialog: false,
    delete_dialog: false,
    delete_condition: false,
    delete_mode: false,
    root_folder: '',
  }),
  created() {
    this.root_folder = this.$store.getters.root_folder
    this.dialog = true;
  },
  computed: {
  },
  methods: {
    remove_check(mode) {
      this.delete_mode = mode
      this.delete_dialog = true
    },  
    remove() {
      if (this.delete_mode === 'space') {
        const url = this.root_folder + "/app/adminRemoveData.php";
        const data = {
          date: (this.pick_date + "-01")
        }
        axios.post(url, data).then(function(response) {
          if (response.data.status == true) {
            this.delete_condition = true
          } else {
            this.alert(response.data.message);
          }
        }.bind(this))
      }

      if (this.delete_mode === 'all') {
        const url = this.root_folder + "/app/adminDeleteData.php";
        const data = {
          date: (this.pick_date + "-01")
        }
        axios.post(url, data).then(function(response) {
          if (response.data.status == true) {
            this.delete_condition = true
          } else {
            this.alert(response.data.message);
          }
        }.bind(this))
      }
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true;
    },
    close() {
      this.dialog = false;
      this.$emit("close", this.delete_condition);
    },
  },
}
</script>